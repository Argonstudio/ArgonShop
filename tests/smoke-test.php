<?php
/**
 * ArgonShop — smoke test of the main store scenarios.
 *
 * RUN (on a test/local site only!):
 *   wp eval-file tests/smoke-test.php
 *
 * What it does:
 *   1. Creates test data (cart/account pages, form settings, products).
 *   2. Installs a temporary mu-plugin that intercepts emails (no mail is sent).
 *   3. Goes through the cart, checkout, account and search scenarios over HTTP
 *      (like a browser), opens the main site and admin pages.
 *   4. If WP_DEBUG_LOG is enabled, checks that no new PHP errors or warnings
 *      appeared in debug.log.
 *   5. Removes the test data and the mu-plugin.
 *
 * Exit: code 0 — all checks passed, 1 — there are failures.
 *
 * The test does NOT run if WP_ENVIRONMENT_TYPE = production
 * (override: ARGON_SHOP_SMOKE_FORCE=1).
 *
 * @package Argon_Shop
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit( "Run it via WP-CLI: wp eval-file tests/smoke-test.php\n" );
}

if ( 'production' === wp_get_environment_type() && ! getenv( 'ARGON_SHOP_SMOKE_FORCE' ) ) {
    WP_CLI::error( 'The site is marked as production. The test creates and deletes data — run it on a test site.' );
}

// ============================================================
// INFRASTRUCTURE
// ============================================================

$GLOBALS['as_smoke'] = array( 'pass' => 0, 'fail' => 0, 'cleanup' => array() );

function as_smoke_check( $condition, $label, $details = '' ) {
    if ( $condition ) {
        $GLOBALS['as_smoke']['pass']++;
        WP_CLI::log( "  ✔ {$label}" );
    } else {
        $GLOBALS['as_smoke']['fail']++;
        WP_CLI::warning( "✘ {$label}" . ( $details ? "\n      " . substr( wp_strip_all_tags( $details ), 0, 300 ) : '' ) );
    }
}

/** A simple HTTP client that keeps cookies, like a browser. */
class AS_Smoke_Browser {

    public $cookies = array();

    public function request( $url, $args = array() ) {
        $args = wp_parse_args( $args, array( 'method' => 'GET', 'timeout' => 30, 'redirection' => 0, 'headers' => array() ) );
        if ( $this->cookies ) {
            $pairs = array();
            foreach ( $this->cookies as $name => $value ) {
                $pairs[] = $name . '=' . rawurlencode( $value );
            }
            $args['headers']['Cookie'] = implode( '; ', $pairs );
        }
        $response = wp_remote_request( $url, $args );
        if ( is_wp_error( $response ) ) {
            WP_CLI::error( 'HTTP: ' . $response->get_error_message() );
        }
        foreach ( wp_remote_retrieve_cookies( $response ) as $cookie ) {
            if ( $cookie->expires && $cookie->expires < time() ) {
                unset( $this->cookies[ $cookie->name ] );
            } else {
                $this->cookies[ $cookie->name ] = $cookie->value;
            }
        }
        return array( 'code' => wp_remote_retrieve_response_code( $response ), 'body' => wp_remote_retrieve_body( $response ) );
    }

    public function ajax( $action, $fields = array(), $files = array() ) {
        $fields['action'] = $action;
        $url = admin_url( 'admin-ajax.php' );
        if ( ! $files ) {
            return $this->request( $url, array( 'method' => 'POST', 'body' => $fields ) );
        }
        // multipart/form-data for file uploads
        $boundary = 'as' . wp_generate_password( 16, false );
        $body = '';
        foreach ( $fields as $name => $value ) {
            $body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"{$name}\"\r\n\r\n{$value}\r\n";
        }
        foreach ( $files as $file ) {
            $body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"fileCart[]\"; filename=\"{$file['name']}\"\r\n"
                . "Content-Type: {$file['type']}\r\n\r\n{$file['content']}\r\n";
        }
        $body .= "--{$boundary}--\r\n";
        return $this->request( $url, array(
            'method'  => 'POST',
            'body'    => $body,
            'headers' => array( 'Content-Type' => 'multipart/form-data; boundary=' . $boundary ),
        ) );
    }

    public function cart() {
        return isset( $this->cookies['productsShoppingCart'] ) ? json_decode( stripslashes( $this->cookies['productsShoppingCart'] ), true ) : array();
    }
}

function as_smoke_nonce( AS_Smoke_Browser $browser, $url ) {
    $page = $browser->request( $url );
    if ( preg_match( '/"nonce":"([a-f0-9]+)"/', $page['body'], $m ) ) {
        return $m[1];
    }
    WP_CLI::error( "Nonce (window.arsWpAjax) not found on the page {$url}. HTTP {$page['code']}" );
}

/** WordPress critical error page or a PHP fatal error in the response (language-independent). */
function as_smoke_crashed( $body ) {
    return false !== strpos( $body, 'wp-die-message' ) || false !== stripos( $body, 'Fatal error' );
}

function as_smoke_mails() {
    wp_cache_delete( 'as_smoke_mail_log', 'options' );
    wp_cache_delete( 'notoptions', 'options' );
    wp_cache_delete( 'alloptions', 'options' );
    $log = get_option( 'as_smoke_mail_log', array() );
    return is_array( $log ) ? $log : array();
}

// ============================================================
// SETUP
// ============================================================

WP_CLI::log( 'Preparing test data…' );

$mu_dir  = WPMU_PLUGIN_DIR;
$mu_file = $mu_dir . '/argon-shop-smoke-mail-capture.php';
wp_mkdir_p( $mu_dir );
file_put_contents( $mu_file, '<?php
// Temporary ArgonShop smoke test file: intercepts emails. Removed automatically.
add_filter( "pre_wp_mail", function( $null, $atts ) {
    $log = get_option( "as_smoke_mail_log", array() );
    $atts["attachment_names"] = array_map( "basename", (array) $atts["attachments"] );
    $atts["attachments_existed"] = array_map( "file_exists", (array) $atts["attachments"] );
    $log[] = $atts;
    update_option( "as_smoke_mail_log", $log, false );
    return true;
}, 10, 2 );
' );
delete_option( 'as_smoke_mail_log' );

$old_settings = get_option( 'settingShop' );
$created      = array();
$as_smoke_wholesale_terms = array();

$cart_page = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Smoke cart', 'page_template' => 'shoppingCartPage.php' ) );
$cab_page  = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Smoke account', 'page_template' => 'myCabinetPage.php' ) );
$created[] = $cart_page;
$created[] = $cab_page;

$settings_fn = function_exists( 'as_sanitize_settings' ) ? 'as_sanitize_settings' : 'sanitize_callback';
update_option( 'settingShop', call_user_func( $settings_fn, array(
    'shoppingCartPage' => $cart_page,
    'cabinetPage'      => $cab_page,
    'typeSale'         => 'noSale',
    'quickLine'        => "telefonUser[Phone,+1,50]\naddressUser[Address,City,50]",
    'personLine'       => "nameUser[Name,John,50]\ntelefonUser[Phone,+1,50]",
    'legalPersonLine'  => "companyNameUser[Company,LLC,50]\ninnUser[TIN,,50]\nkppUser[KPP,,50]\nlegalAddressUser[Legal address,,100]",
) ) );

$product_a = wp_insert_post( array( 'post_type' => 'product', 'post_status' => 'publish', 'post_title' => 'Smoke product A' ) );
$product_b = wp_insert_post( array( 'post_type' => 'product', 'post_status' => 'publish', 'post_title' => 'Smoke product B' ) );
$draft     = wp_insert_post( array( 'post_type' => 'product', 'post_status' => 'draft', 'post_title' => 'Smoke draft' ) );
update_post_meta( $product_a, '_price', '1500,50' );
update_post_meta( $product_b, '_price', '200' );
update_post_meta( $draft, '_price', '10' );
array_push( $created, $product_a, $product_b, $draft );

// Search language of the site (ru / en / fr) — see includes/interface/search/searchLanguage.php.
$search_lang = function_exists( 'as_search_language' ) ? as_search_language() : 'en';

// Russian site: a product with a Cyrillic title, built from the QWERTY spelling
// "ghjlern" with the plugin's own layout map (keeps this file free of Cyrillic).
$product_ru = 0;
if ( 'ru' === $search_lang && function_exists( 'as_search_layout_map' ) ) {
    $product_ru = wp_insert_post( array( 'post_type' => 'product', 'post_status' => 'publish', 'post_title' => 'Smoke ' . strtr( 'ghjlern', as_search_layout_map( 'qwerty_to_ru' ) ) ) );
    update_post_meta( $product_ru, '_price', '100' );
    $created[] = $product_ru;
}

$smoke_term = wp_insert_term( 'Smoke category ' . wp_generate_password( 4, false, false ), 'catalog' );
$smoke_term_id = is_wp_error( $smoke_term ) ? 0 : (int) $smoke_term['term_id'];
if ( $smoke_term_id ) {
    wp_set_object_terms( $product_a, array( $smoke_term_id ), 'catalog' );
    wp_set_object_terms( $product_b, array( $smoke_term_id ), 'catalog' );
}

// Temporary administrator for checking the admin pages
$smoke_admin_id = wp_insert_user( array(
    'user_login' => 'smoke_admin_' . strtolower( wp_generate_password( 6, false, false ) ),
    'user_pass'  => wp_generate_password( 32 ),
    'user_email' => 'smoke-admin-' . strtolower( wp_generate_password( 6, false, false ) ) . '@example.com',
    'role'       => 'administrator',
) );

// PHP errors logged during the test (if WP_DEBUG_LOG is enabled)
$debug_log      = ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) ? ( is_string( WP_DEBUG_LOG ) ? WP_DEBUG_LOG : WP_CONTENT_DIR . '/debug.log' ) : '';
$debug_log_size = ( $debug_log && file_exists( $debug_log ) ) ? filesize( $debug_log ) : 0;

$orders_before = get_posts( array( 'post_type' => 'shoporder', 'numberposts' => -1, 'cache_results' => false, 'fields' => 'ids', 'post_status' => 'any' ) );
$test_login    = 'smoke_' . wp_generate_password( 6, false, false );
$test_login    = strtolower( $test_login );

$browser = new AS_Smoke_Browser();
$nonce   = as_smoke_nonce( $browser, get_permalink( $cart_page ) );

// ============================================================
// SCENARIOS
// ============================================================

try {

    WP_CLI::log( '1. Cart' );

    $r = $browser->ajax( 'addProducts_shoppingCart', array( 'nonce' => $nonce, 'productID' => $product_a, 'amountProduct' => 2 ) );
    $data = json_decode( $r['body'], true );
    as_smoke_check( isset( $data['totalPrice'] ) && abs( $data['totalPrice'] - 3001 ) < 0.01, 'Adding 2 × 1500.50 → total 3001', $r['body'] );

    $browser->ajax( 'addProducts_shoppingCart', array( 'nonce' => $nonce, 'productID' => $product_b, 'amountProduct' => 1 ) );
    as_smoke_check( count( $browser->cart() ) === 2, 'The cart contains two products' );

    $r = $browser->ajax( 'addProducts_shoppingCart', array( 'nonce' => $nonce, 'productID' => $draft, 'amountProduct' => 1 ) );
    $r = $browser->ajax( 'amountProducts_shoppingCart', array( 'nonce' => $nonce, 'productID' => $product_b, 'productAmount' => 1 ) );
    $data = json_decode( $r['body'], true );
    as_smoke_check( ! isset( $data['productsShoppingCart'][ $draft ] ), 'A draft does not get into the cart', $r['body'] );

    // Price tampering in the cookie
    $cart = $browser->cart();
    unset( $cart[ $draft ] );
    $cart[ $product_a ]['price'] = 1;
    $browser->cookies['productsShoppingCart'] = wp_json_encode( $cart );
    $r = $browser->ajax( 'amountProducts_shoppingCart', array( 'nonce' => $nonce, 'productID' => $product_a, 'productAmount' => 2 ) );
    $data = json_decode( $r['body'], true );
    as_smoke_check( isset( $data['actualPrices'][ $product_a ] ) && abs( $data['actualPrices'][ $product_a ] - 1500.5 ) < 0.01, 'The price from the cookie is ignored, the database price is used', $r['body'] );

    $r = $browser->ajax( 'deleteAllProducts_shoppingCart' );
    as_smoke_check( count( $browser->cart() ) === 2, 'Clearing the cart without a nonce is rejected', 'HTTP ' . $r['code'] . ' ' . $r['body'] );

    WP_CLI::log( '2. Required fields' );

    $products_json = wp_json_encode( array( $product_a => array( 'amountProduct' => 2 ), $product_b => array( 'amountProduct' => 1 ) ) );

    $r = $browser->ajax( 'submitCart_shoppingCart', array( 'nonce' => $nonce, 'typeForm' => 'quick', 'productsCart' => $products_json, 'required' => '{}' ) );
    $data = json_decode( $r['body'], true );
    as_smoke_check( isset( $data['type'] ) && 'error' === $data['type'] && false !== strpos( $data['message'], 'Phone' ), 'An empty quick order is rejected even if the browser did not send required', $r['body'] );

    $r = $browser->ajax( 'submitCart_shoppingCart', array( 'nonce' => $nonce, 'typeForm' => 'legalPerson', 'productsCart' => $products_json, 'nameUser' => 'John', 'telefonUser' => '+1 555 000-0000' ) );
    $data = json_decode( $r['body'], true );
    as_smoke_check( isset( $data['type'] ) && 'error' === $data['type'] && false !== strpos( $data['message'], 'TIN' ), 'A legal entity without company details is rejected', $r['body'] );

    WP_CLI::log( '3. Order files' );

    $php_payload = array( 'name' => 'shell.jpg', 'type' => 'image/jpeg', 'content' => '<?php echo "hacked"; ?>' );
    $r = $browser->ajax( 'submitCart_shoppingCart', array( 'nonce' => $nonce, 'typeForm' => 'quick', 'productsCart' => $products_json, 'telefonUser' => '+1 555 000-0000', 'addressUser' => 'New York' ), array( $php_payload ) );
    $data = json_decode( $r['body'], true );
    as_smoke_check( isset( $data['type'] ) && 'error' === $data['type'], 'PHP code disguised as .jpg is rejected', $r['body'] );

    $big = array( 'name' => 'big.pdf', 'type' => 'application/pdf', 'content' => "%PDF-1.4\n" . str_repeat( 'A', 5 * 1024 * 1024 + 10 ) );
    $r = $browser->ajax( 'submitCart_shoppingCart', array( 'nonce' => $nonce, 'typeForm' => 'quick', 'productsCart' => $products_json, 'telefonUser' => '+1 555 000-0000', 'addressUser' => 'New York' ), array( $big ) );
    $data = json_decode( $r['body'], true );
    as_smoke_check( isset( $data['type'] ) && 'error' === $data['type'], 'A file larger than 5 MB is rejected', $r['body'] );

    as_smoke_check( 0 === count( as_smoke_mails() ), 'Rejected orders do not send emails' );

    WP_CLI::log( '4. Quick order with a file' );

    // Minimal valid 1×1 PNG
    $png = array( 'name' => 'Order scheme.png', 'type' => 'image/png', 'content' => base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==' ) );
    $r = $browser->ajax( 'submitCart_shoppingCart', array( 'nonce' => $nonce, 'typeForm' => 'quick', 'productsCart' => $products_json, 'telefonUser' => '+1 555 000-0000', 'addressUser' => 'New York, Test street' ), array( $png ) );
    $data = json_decode( $r['body'], true );
    as_smoke_check( isset( $data['type'] ) && 'success' === $data['type'], 'The order is placed', $r['body'] );

    $orders = get_posts( array( 'post_type' => 'shoporder', 'numberposts' => -1, 'cache_results' => false, 'post_status' => 'any', 'exclude' => $orders_before ) );
    as_smoke_check( 1 === count( $orders ), 'Exactly one order is created', count( $orders ) . ' orders' );

    if ( $orders ) {
        $order = $orders[0];
        $GLOBALS['as_smoke']['cleanup'][] = $order->ID;
        $total = (float) get_post_meta( $order->ID, '_totalPrice', true );
        as_smoke_check( abs( $total - 3201 ) < 0.01, 'Order total 3201 (from the database prices)', 'Total: ' . $total );
        $products = get_post_meta( $order->ID, '_productsCart', true );
        as_smoke_check( isset( $products[ $product_a ]['price'] ) && abs( $products[ $product_a ]['price'] - 1500.5 ) < 0.01, 'Product price in the order is 1500.50' );
    }

    $mails = as_smoke_mails();
    as_smoke_check( 1 === count( $mails ), 'One email is sent (to the admin; a quick order has no email)', count( $mails ) . ' emails' );
    if ( $mails ) {
        as_smoke_check( in_array( 'Order scheme.png', $mails[0]['attachment_names'], true ) || in_array( sanitize_file_name( 'Order scheme.png' ), $mails[0]['attachment_names'], true ), 'The attachment kept its original name', implode( ', ', $mails[0]['attachment_names'] ) );
        as_smoke_check( ! in_array( false, $mails[0]['attachments_existed'], true ), 'The file existed at the moment of sending' );
        foreach ( (array) $mails[0]['attachments'] as $path ) {
            as_smoke_check( ! file_exists( $path ), 'The temporary file is deleted after sending' );
            $uploads = wp_upload_dir();
            $public_dir = trailingslashit( $uploads['basedir'] ) . 'order-files';
            as_smoke_check( 0 !== strpos( $path, $public_dir ), 'The file was not stored in the public uploads/order-files folder', $path );
        }
    }
    as_smoke_check( 0 === count( $browser->cart() ), 'The cart is cleared after the order' );

    WP_CLI::log( '5. Order with registration' );

    delete_option( 'as_smoke_mail_log' );
    $browser->ajax( 'addProducts_shoppingCart', array( 'nonce' => $nonce, 'productID' => $product_b, 'amountProduct' => 3 ) );
    $products_json = wp_json_encode( array( $product_b => array( 'amountProduct' => 3 ) ) );
    $r = $browser->ajax( 'submitCart_shoppingCart', array(
        'nonce' => $nonce, 'typeForm' => 'person', 'productsCart' => $products_json,
        'nameUser' => 'John <b>Test</b>', 'telefonUser' => '+1 555 000-0000',
        'emailUser' => $test_login . '@example.com', 'cartReg' => 1, 'loginUser' => $test_login,
    ) );
    $data = json_decode( $r['body'], true );
    as_smoke_check( isset( $data['type'] ) && 'success' === $data['type'], 'The order with registration is placed', $r['body'] );
    as_smoke_check( isset( $data['message'] ) && false === strpos( $data['message'], '<b>Test</b>' ), 'The customer name is escaped in the response' );

    $user = get_user_by( 'login', $test_login );
    as_smoke_check( (bool) $user, 'The user is created' );
    if ( $user ) {
        $GLOBALS['as_smoke']['cleanup_user'] = $user->ID;
    }

    $mails = as_smoke_mails();
    as_smoke_check( 2 === count( $mails ), 'Two emails: to the admin and to the customer', count( $mails ) . ' emails' );
    $admin_mail = null;
    $user_mail  = null;
    foreach ( $mails as $mail ) {
        $to = is_array( $mail['to'] ) ? implode( ',', $mail['to'] ) : $mail['to'];
        if ( false !== strpos( $to, $test_login ) ) {
            $user_mail = $mail;
        } else {
            $admin_mail = $mail;
        }
    }
    as_smoke_check( $user_mail && false === stripos( $user_mail['message'], 'Password:' ) && false === stripos( $user_mail['message'], __( 'Password:', 'argon-shop' ) ), 'The customer email does not contain a plain-text password' );
    as_smoke_check( $user_mail && false !== strpos( $user_mail['message'], 'action=rp' ), 'The customer email contains a password setup link' );
    as_smoke_check( $admin_mail && false === strpos( $admin_mail['message'], 'action=rp' ) && false === stripos( $admin_mail['message'], 'Password:' ), 'The administrator does not receive the customer password or link' );

    WP_CLI::log( '6. Customer account' );

    $r = $browser->ajax( 'ordersFiltersStatus', array( 'userID' => $user ? $user->ID : 0, 'status' => '' ) );
    as_smoke_check( false === strpos( $r['body'], '<table' ), 'The order filter without a nonce is rejected', 'HTTP ' . $r['code'] );

    WP_CLI::log( '7. Search' );

    $home = $browser->request( home_url( '/' ) );
    $data_ajax = preg_match( "/data-ajax='([^']+)'/", $home['body'], $m ) ? html_entity_decode( $m[1], ENT_QUOTES ) : '';
    as_smoke_check( '' !== $data_ajax, 'The home page has an AJAX search form' );
    // After registration the customer is logged in — the nonce is different now
    $nonce = preg_match( '/"nonce":"([a-f0-9]+)"/', $home['body'], $m ) ? $m[1] : $nonce;

    $r = $browser->ajax( 'getAjaxSearchResult', array( 'nonce' => $nonce, 'searchStr' => 'Smoke product', 'dataAjax' => $data_ajax ) );
    as_smoke_check( false !== strpos( $r['body'], 'Smoke product A' ), 'AJAX search finds the product', $r['body'] );

    $r = $browser->ajax( 'getAjaxSearchResult', array( 'nonce' => $nonce, 'searchStr' => "Smoke' % _ \\ \"", 'dataAjax' => $data_ajax ) );
    as_smoke_check( 200 === $r['code'] && ! as_smoke_crashed( $r['body'] ), 'Special characters in AJAX search do not break the query', 'HTTP ' . $r['code'] );

    $r = $browser->request( add_query_arg( 's', rawurlencode( 'Smoke product' ), home_url( '/' ) ) );
    as_smoke_check( 200 === $r['code'] && false !== strpos( $r['body'], 'Smoke product A' ), 'The search results page finds the product', 'HTTP ' . $r['code'] );

    $r = $browser->request( add_query_arg( 's', rawurlencode( "Smoke' product %" ), home_url( '/' ) ) );
    as_smoke_check( 200 === $r['code'] && ! as_smoke_crashed( $r['body'] ), 'Special characters on the search page do not break the query', 'HTTP ' . $r['code'] );

    WP_CLI::log( "8. Search language ({$search_lang})" );

    // A query typed in the wrong keyboard layout must still find the product.
    if ( 'ru' === $search_lang ) {
        $wrong_layout = 'ghjlern';
        $expected     = $product_ru ? get_the_title( $product_ru ) : 'Smoke product A';
    } else {
        // "smoke" typed with the Russian layout on a QWERTY (en) or AZERTY (fr) keyboard.
        $map          = array_flip( array_filter( as_search_layout_map( 'fr' === $search_lang ? 'ru_to_azerty' : 'ru_to_qwerty' ), 'strlen' ) );
        $wrong_layout = strtr( 'smoke', $map );
        $expected     = 'Smoke product A';
    }
    $r = $browser->ajax( 'getAjaxSearchResult', array( 'nonce' => $nonce, 'searchStr' => $wrong_layout, 'dataAjax' => $data_ajax ) );
    as_smoke_check( false !== strpos( $r['body'], $expected ), "AJAX search fixes the keyboard layout: {$wrong_layout}", $r['body'] );

    $r = $browser->request( add_query_arg( 's', rawurlencode( $wrong_layout ), home_url( '/' ) ) );
    as_smoke_check( 200 === $r['code'] && false !== strpos( $r['body'], $expected ), 'The search page fixes the keyboard layout', 'HTTP ' . $r['code'] );

    if ( 'en' === $search_lang ) {
        $words = as_search_get_words( 'The smoke products' );
        as_smoke_check( array( 'smoke', 'product' ) === $words, 'English: stop words and plural endings are removed', implode( ', ', $words ) );
        $r = $browser->ajax( 'getAjaxSearchResult', array( 'nonce' => $nonce, 'searchStr' => 'the smoke products', 'dataAjax' => $data_ajax ) );
        as_smoke_check( false !== strpos( $r['body'], 'Smoke product A' ), 'English: "the smoke products" finds the product', $r['body'] );
    } elseif ( 'fr' === $search_lang ) {
        $words = as_search_get_words( "les chaises de l'huile" );
        as_smoke_check( array( 'chaise', 'huile' ) === $words, 'French: stop words, elisions and plurals are handled', implode( ', ', $words ) );
        as_smoke_check( in_array( 'chaise', as_search_layout_variants( 'chqise' ), true ), 'French: QWERTY/AZERTY swap (chqise → chaise)' );
        $r = $browser->ajax( 'getAjaxSearchResult', array( 'nonce' => $nonce, 'searchStr' => 'des smokes', 'dataAjax' => $data_ajax ) );
        as_smoke_check( false !== strpos( $r['body'], 'Smoke product A' ), 'French: "des smokes" finds the product', $r['body'] );
    }

    WP_CLI::log( '9. Pages open without errors' );

    $is_ok = function( $r ) {
        return 200 === $r['code'] && ! as_smoke_crashed( $r['body'] );
    };

    $front_pages = array(
        'Home'             => home_url( '/' ),
        'Product'          => get_permalink( $product_a ),
        'Category'         => $smoke_term_id ? get_term_link( $smoke_term_id, 'catalog' ) : '',
        'Cart'             => get_permalink( $cart_page ),
        'My account'       => get_permalink( $cab_page ),
    );

    $browser->ajax( 'addProducts_shoppingCart', array( 'nonce' => $nonce, 'productID' => $product_a, 'amountProduct' => 1 ) );

    foreach ( $front_pages as $label => $url ) {
        if ( ! $url || is_wp_error( $url ) ) {
            continue;
        }
        $r = $browser->request( $url );
        $has_product = in_array( $label, array( 'Product', 'Category', 'Cart' ), true ) ? false !== strpos( $r['body'], 'Smoke product A' ) : true;
        as_smoke_check( $is_ok( $r ) && $has_product, "Site: {$label}", 'HTTP ' . $r['code'] . ' ' . $url );
    }

    if ( ! is_wp_error( $smoke_admin_id ) ) {

        $admin = new AS_Smoke_Browser();
        $expire = time() + HOUR_IN_SECONDS;
        $admin->cookies[ AUTH_COOKIE ]      = wp_generate_auth_cookie( $smoke_admin_id, $expire, 'auth' );
        $admin->cookies[ LOGGED_IN_COOKIE ] = wp_generate_auth_cookie( $smoke_admin_id, $expire, 'logged_in' );

        $admin_pages = array(
            'Product list'       => admin_url( 'edit.php?post_type=product' ),
            'New product'        => admin_url( 'post-new.php?post_type=product' ),
            'Product editing'    => admin_url( 'post.php?action=edit&post=' . $product_a ),
            'Order list'         => admin_url( 'edit.php?post_type=shoporder' ),
            'Catalog categories' => admin_url( 'edit-tags.php?taxonomy=catalog&post_type=product' ),
            'Store settings'     => admin_url( 'options-general.php?page=settings-shop' ),
        );

        if ( ! empty( $order ) ) {
            $admin_pages['Order editing'] = admin_url( 'post.php?action=edit&post=' . $order->ID );
        }
        if ( $smoke_term_id ) {
            $admin_pages['Category editing'] = admin_url( 'term.php?taxonomy=catalog&post_type=product&tag_ID=' . $smoke_term_id );
        }

        foreach ( $admin_pages as $label => $url ) {
            $r = $admin->request( $url );
            as_smoke_check( $is_ok( $r ) && false !== strpos( $r['body'], 'wpbody' ), "Admin: {$label}", 'HTTP ' . $r['code'] . ' ' . $url );
        }

        // Order recount AJAX in the admin (the nonce is taken from the order page)
        if ( ! empty( $order ) ) {
            $r = $admin->request( admin_url( 'post.php?action=edit&post=' . $order->ID ) );
            if ( preg_match( '/argonShopOrderNonce\W+([a-f0-9]{10})/', $r['body'], $m ) ) {
                $r = $admin->ajax( 'as_admin_recount_order', array( 'nonce' => $m[1], 'postID' => $order->ID, 'orderProducts[' . $product_a . ']' => 2 ) );
                $data = json_decode( $r['body'], true );
                as_smoke_check( isset( $data['totalPrice'] ) && abs( $data['totalPrice'] - 3001 ) < 0.01, 'Admin: order recount (AJAX)', $r['body'] );
            } else {
                as_smoke_check( false, 'Admin: the order page has argonShopOrderNonce' );
            }
        }
    }

    WP_CLI::log( '10. Currency' );

    // Test existing settings without a currency key; the default must still work.
    as_smoke_check( 'USD' === as_get_currency(), 'The default currency is USD' );
    $r = $browser->request( get_permalink( $product_a ) );
    as_smoke_check( false !== strpos( $r['body'], 'class="currencySymbol">$</span>' ), 'Product page: $ before the price', 'HTTP ' . $r['code'] );

    $currency_settings = get_option( 'settingShop' );
    $currency_settings['currency'] = 'XYZ';
    $sanitized = as_sanitize_settings( $currency_settings );
    as_smoke_check( 'USD' === $sanitized['currency'], 'An unknown currency code is saved as USD' );

    foreach ( array( 'EUR' => '€', 'RUB' => as_get_currency_symbol( 'RUB' ) ) as $code => $symbol ) {
        $currency_settings['currency'] = $code;
        update_option( 'settingShop', as_sanitize_settings( $currency_settings ) );
        $r = $browser->request( get_permalink( $cart_page ) );
        as_smoke_check( $code === as_get_currency() && false !== strpos( $r['body'], '</span> ' . $symbol ), "{$code}: the cart shows the amount followed by {$symbol}", 'HTTP ' . $r['code'] );
        $r = $browser->request( get_permalink( $product_a ) );
        as_smoke_check( false !== strpos( $r['body'], 'class="unitMeasure">' . esc_html( $symbol ) . '/' ), "{$code}: product page shows {$symbol}/unit", 'HTTP ' . $r['code'] );
    }
    unset( $currency_settings['currency'] );
    update_option( 'settingShop', $currency_settings );

    WP_CLI::log( '11. Translations' );
    $locale = determine_locale();
    // For every language the plugin has a translation for, the strings must be translated.
    if ( file_exists( ARGON_SHOP_PATH . 'languages/argon-shop-' . $locale . '.mo' ) ) {
        as_smoke_check( 'Add to cart' !== __( 'Add to cart', 'argon-shop' ), "The plugin is translated into {$locale}" );
        as_smoke_check( 'Your cart is empty' !== __( 'Your cart is empty', 'argon-shop-theme' ), "The theme is translated into {$locale}" );
    } elseif ( 0 === strpos( $locale, 'en_' ) ) {
        as_smoke_check( 'Add to cart' === __( 'Add to cart', 'argon-shop' ), 'English: the source strings are output as is' );
    } else {
        WP_CLI::log( "  (no translation for {$locale} — the strings are output in English)" );
    }

    // Frontend JS strings come from PHP (arsWpAjax.i18n) in the site language.
    $r = $browser->request( get_permalink( $cart_page ) );
    $files_uploaded = __( 'Files uploaded: %d', 'argon-shop' );
    as_smoke_check( false !== strpos( $r['body'], '"filesUploaded":' . wp_json_encode( $files_uploaded, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES ) ), 'Cart page: JS strings are passed in the site language', $files_uploaded );
    if ( file_exists( ARGON_SHOP_PATH . 'languages/argon-shop-' . $locale . '.mo' ) ) {
        as_smoke_check( 'Files uploaded: %d' !== $files_uploaded, "JS strings are translated into {$locale}" );
    }
    if ( 'ru' !== $search_lang ) {
        // No Cyrillic text may leak into the cart page on a non-Russian site.
        $cart_text = preg_replace( '#<(script|style)\b[^>]*>.*?</\1>#is', '', $r['body'] );
        as_smoke_check( ! preg_match( '/\p{Cyrillic}/u', $cart_text ), 'Cart page: no Cyrillic text on a non-Russian site' );
    }

    WP_CLI::log( '12. Decimal numbers, wholesale steps and passwords' );

    $number_cases = json_decode( file_get_contents( __DIR__ . '/number-cases.json' ), true, 512, JSON_THROW_ON_ERROR );
    foreach ( $number_cases as $number_case ) {
        $actual_number = as_to_float( $number_case['input'] );
        as_smoke_check( is_finite( $actual_number ) && abs( $actual_number - $number_case['expected'] ) <= 1e-9 * max( 1, abs( $number_case['expected'] ) ), 'Number parser: ' . wp_json_encode( $number_case['input'] ) );
    }

    // Real WordPress terms, metadata and cart sanitization; temporary data only.
    $wholesale_product = wp_insert_post( array( 'post_type' => 'product', 'post_status' => 'publish', 'post_title' => 'Smoke wholesale decimals' ) );
    $created[] = $wholesale_product;
    $wholesale_ids = array();
    foreach ( array( '1000,50', '100,50' ) as $step_name ) {
        $inserted = wp_insert_term( $step_name, 'wholesalePrice' );
        if ( is_wp_error( $inserted ) && 'term_exists' === $inserted->get_error_code() ) {
            $wholesale_ids[] = (int) $inserted->get_error_data();
        } elseif ( ! is_wp_error( $inserted ) ) {
            $wholesale_ids[] = (int) $inserted['term_id'];
            $as_smoke_wholesale_terms[] = (int) $inserted['term_id'];
        } else {
            as_smoke_check( false, 'Wholesale test term created', $inserted->get_error_message() );
        }
    }
    if ( 2 === count( $wholesale_ids ) ) {
        $before_wholesale_settings = get_option( 'settingShop' );
        $wholesale_settings = $before_wholesale_settings;
        $wholesale_settings['typeSale'] = 'wholesalePriceSteps';
        update_option( 'settingShop', $wholesale_settings );
        try {
            update_post_meta( $wholesale_product, '_wholesalePrice', array( $wholesale_ids[0] => '60,33', $wholesale_ids[1] => '70,25' ) );
            foreach ( array( array( '100,49', 0.0, 100.49 ), array( '100,50', 100.5, 70.25 ), array( '1000,49', 100.5, 70.25 ), array( '1000,50', 1000.5, 60.33 ) ) as $wholesale_case ) {
                update_post_meta( $wholesale_product, '_price', $wholesale_case[0] );
                $wholesale_cart = as_sanitize_cart( array( $wholesale_product => array( 'price' => '0,01', 'amountProduct' => 1 ) ) );
                as_smoke_check( abs( $wholesale_cart[$wholesale_product]['price'] - as_to_float( $wholesale_case[0] ) ) < 0.000001, 'Wholesale cart price comes from the database, not cookie' );
                as_smoke_check( abs( $wholesale_case[1] - as_getActualStepDiscont( $wholesale_product, $wholesale_cart ) ) < 0.000001, 'Wholesale step at total ' . $wholesale_case[0] );
                as_smoke_check( abs( $wholesale_case[2] - as_getActualPrice( 'cart', $wholesale_cart, $wholesale_product ) ) < 0.000001, 'Wholesale actual price at total ' . $wholesale_case[0] );
            }
            update_post_meta( $wholesale_product, '_wholesalePrice', array( $wholesale_ids[1] => '70,25', $wholesale_ids[0] => '60,33' ) );
            as_smoke_check( 60.33 === as_getActualPrice( 'cart', $wholesale_cart, $wholesale_product ), 'Reversing step order preserves the highest reached step' );
            as_smoke_check( 60.33 === as_getActualPrice( 'productPage', array(), $wholesale_product, '1000,50', 1 ), 'Product page applies a fractional wholesale price' );
            $wholesale_browser = new AS_Smoke_Browser();
            $wholesale_nonce = as_smoke_nonce( $wholesale_browser, get_permalink( $cart_page ) );
            $wholesale_response = $wholesale_browser->ajax( 'addProducts_shoppingCart', array( 'nonce' => $wholesale_nonce, 'productID' => $wholesale_product, 'amountProduct' => 1 ) );
            $wholesale_data = json_decode( $wholesale_response['body'], true );
            as_smoke_check( isset( $wholesale_data['totalPrice'] ) && abs( $wholesale_data['totalPrice'] - 60.33 ) < 0.000001, 'Wholesale AJAX total is 60.33, not 60', $wholesale_response['body'] );
            $wholesale_settings['typeSale'] = 'noSale';
            update_option( 'settingShop', $wholesale_settings );
            as_smoke_check( 1000.5 === as_getActualPrice( 'cart', $wholesale_cart, $wholesale_product ), 'Disabled wholesale discounts preserve base price' );
        } finally {
            update_option( 'settingShop', $before_wholesale_settings );
        }
    }

    // Test actual WordPress hashing and the AJAX endpoint, not just source patterns.
    if ( $user ) {
        $password_cases = array(
            array( 'Ab%41<x>9  z!{#', 'Zy%42<q>7  k?', null, true, null ),
            array( "A\\b'c\"d9", "Z\\y'q\"r8", null, true, null ),
            array( '0', 'abcdef', null, true, null ),
            array( 'abcdef', str_repeat( "\u{0416}", 6 ), null, true, null ),
            array( 'abcdef', str_repeat( "\u{0416}", 5 ), null, false, 'newPass' ),
            array( 'abcdef', '0', null, false, 'newPass' ),
            array( 'abcdef', 'ghijkl', 'xxxxxx', false, 'newPassConfirm' ),
            array( '0', '', null, true, null ),
        );
        foreach ( $password_cases as $password_index => $password_case ) {
            list( $old_password, $new_password, $confirm_password, $should_succeed, $error_key ) = $password_case;
            wp_set_password( $old_password, $user->ID );
            clean_user_cache( $user->ID );
            $customer = new AS_Smoke_Browser();
            $expire = time() + HOUR_IN_SECONDS;
            $token = WP_Session_Tokens::get_instance( $user->ID )->create( $expire );
            $customer->cookies[ AUTH_COOKIE ] = wp_generate_auth_cookie( $user->ID, $expire, 'auth', $token );
            $customer->cookies[ SECURE_AUTH_COOKIE ] = wp_generate_auth_cookie( $user->ID, $expire, 'secure_auth', $token );
            $customer->cookies[ LOGGED_IN_COOKIE ] = wp_generate_auth_cookie( $user->ID, $expire, 'logged_in', $token );
            $customer_nonce = as_smoke_nonce( $customer, get_permalink( $cart_page ) );
            $password_response = $customer->ajax( 'cabinetEditEmailPassword', array(
                'nonce' => $customer_nonce, 'userID' => $user->ID, 'email' => $user->user_email, 'oldEmail' => $user->user_email,
                'pass' => $old_password, 'newPass' => $new_password, 'newPassConfirm' => $confirm_password ?? $new_password,
            ) );
            $password_data = json_decode( $password_response['body'], true );
            $success = is_array( $password_data ) && empty( $password_data['errors'] );
            if ( $should_succeed ) {
                as_smoke_check( $success && ( '' === $new_password || isset( $password_data['edited']['newPass'] ) ), 'Password AJAX case ' . $password_index . ' succeeds', $password_response['body'] );
            } else {
                as_smoke_check( is_array( $password_data ) && isset( $password_data['errors'][$error_key] ), 'Password AJAX case ' . $password_index . ' is rejected with the expected error', $password_response['body'] );
            }
            clean_user_cache( $user->ID );
            $fresh_user = get_user_by( 'id', $user->ID );
            $expected_password = $should_succeed && '' !== $new_password ? $new_password : $old_password;
            as_smoke_check( $fresh_user && wp_check_password( $expected_password, $fresh_user->user_pass, $user->ID ), 'Password AJAX case ' . $password_index . ' stores exactly the expected password' );
            WP_Session_Tokens::get_instance( $user->ID )->destroy( $token );
        }
    }

    if ( $debug_log ) {
        clearstatcache();
        $new_errors = array();
        if ( file_exists( $debug_log ) && filesize( $debug_log ) > $debug_log_size ) {
            $fh = fopen( $debug_log, 'r' );
            fseek( $fh, $debug_log_size );
            while ( false !== ( $line = fgets( $fh ) ) ) {
                if ( preg_match( '/PHP (Fatal|Parse|Warning|Notice|Error)/i', $line ) ) {
                    $new_errors[] = trim( $line );
                }
            }
            fclose( $fh );
        }
        as_smoke_check( ! $new_errors, 'No new PHP errors or warnings in debug.log', implode( "\n      ", array_slice( array_unique( $new_errors ), 0, 5 ) ) );
    } else {
        WP_CLI::log( '  (debug.log check skipped: enable WP_DEBUG and WP_DEBUG_LOG)' );
    }

} finally {

    // ============================================================
    // CLEANUP
    // ============================================================

    WP_CLI::log( 'Removing test data…' );

    foreach ( array_merge( $created, $GLOBALS['as_smoke']['cleanup'] ) as $post_id ) {
        wp_delete_post( $post_id, true );
    }
    require_once ABSPATH . 'wp-admin/includes/user.php';
    if ( ! empty( $GLOBALS['as_smoke']['cleanup_user'] ) ) {
        wp_delete_user( $GLOBALS['as_smoke']['cleanup_user'] );
    }
    if ( ! is_wp_error( $smoke_admin_id ) ) {
        wp_delete_user( $smoke_admin_id );
    }
    foreach ( $as_smoke_wholesale_terms as $as_smoke_term_id ) {
        wp_delete_term( $as_smoke_term_id, 'wholesalePrice' );
    }
    if ( $smoke_term_id ) {
        wp_delete_term( $smoke_term_id, 'catalog' );
    }
    if ( false === $old_settings ) {
        delete_option( 'settingShop' );
    } else {
        update_option( 'settingShop', $old_settings );
    }
    delete_option( 'as_smoke_mail_log' );
    if ( file_exists( $mu_file ) ) {
        unlink( $mu_file );
    }
}

$s = $GLOBALS['as_smoke'];
WP_CLI::log( '' );
if ( $s['fail'] ) {
    WP_CLI::error( "Failed checks: {$s['fail']}, passed: {$s['pass']}" );
}
WP_CLI::success( "All checks passed: {$s['pass']}" );
