<?php
/** Isolated regression checks of real plugin functions using WordPress test doubles. */
if ( PHP_SAPI !== 'cli' || defined( 'ABSPATH' ) ) {
    exit( "Run outside WordPress: php tests/regression-check.php\n" );
}
define( 'ABSPATH', __DIR__ . '/' );
$root = dirname( __DIR__ );
$GLOBALS['as_test_meta'] = array();
$GLOBALS['as_test_terms'] = array( 1 => '1000,50', 2 => '100,50' );
$GLOBALS['as_test_sales'] = true;
$GLOBALS['as_test_fail'] = 0;
$GLOBALS['as_test_pass'] = 0;
class AS_Test_Die extends RuntimeException {}
function as_test_check( $ok, $label ) {
    $GLOBALS[ $ok ? 'as_test_pass' : 'as_test_fail' ]++;
    if ( ! $ok ) echo "FAIL: {$label}\n";
}
function absint( $value ) { return abs( (int) $value ); }
function is_wp_error( $value ) { return false; }
function get_post_meta( $id, $key, $single = false ) { return $GLOBALS['as_test_meta'][$id][$key] ?? ''; }
function get_term( $id, $taxonomy = '' ) { return isset( $GLOBALS['as_test_terms'][$id] ) ? (object) array( 'term_id' => $id, 'name' => $GLOBALS['as_test_terms'][$id] ) : false; }
function get_term_by( $field, $value, $taxonomy ) { return 'id' === $field ? get_term( $value, $taxonomy ) : false; }
function apply_filters( $tag, $value, ...$args ) { return $value; }
function add_filter( ...$args ) {}
function remove_filter( ...$args ) {}
function add_action( ...$args ) {}
function as_check_saleSteps() { return $GLOBALS['as_test_sales']; }
function get_post( $id ) { return isset( $GLOBALS['as_test_meta'][$id] ) ? (object) array( 'post_type' => 'product', 'post_status' => 'publish' ) : false; }
function check_ajax_referer( $action, $field ) {
    $GLOBALS['as_test_nonce_calls']++;
    if ( ! $GLOBALS['as_test_nonce_ok'] ) wp_die( 'Invalid nonce' );
}
function get_current_user_id() { return 7; }
function get_user_by( $field, $id ) { return 7 === (int) $id ? $GLOBALS['as_test_user'] : false; }
function wp_unslash( $value ) { return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( $value ); }
function sanitize_email( $value ) { return filter_var( $value, FILTER_SANITIZE_EMAIL ); }
function __( $text, $domain = '' ) { return $text; }
function wp_check_password( $value, $hash, $id = 0 ) { return password_verify( $value, $hash ); }
function wp_json_encode( $value ) { return json_encode( $value ); }
function wp_die( $message = '' ) { echo $message; throw new AS_Test_Die(); }
function is_email( $value ) { return filter_var( $value, FILTER_VALIDATE_EMAIL ); }
function email_exists( $value ) { return false; }
function wp_update_user( $data ) { $GLOBALS['as_test_user']->user_email = $data['user_email']; return 7; }
function wp_set_password( $value, $id ) {
    $GLOBALS['as_test_stored'] = $value;
    $GLOBALS['as_test_user']->data->user_pass = password_hash( $value, PASSWORD_BCRYPT );
}
function wp_signon( $creds, $secure = false ) {
    return wp_check_password( $creds['user_password'], $GLOBALS['as_test_user']->data->user_pass ) ? $GLOBALS['as_test_user'] : false;
}
function wp_create_nonce( $action ) { return 'test-nonce'; }
require $root . '/plugins/argon-shop/includes/api/getData/getData.php';
require $root . '/plugins/argon-shop/includes/interface/cabinet/myCabinet.php';
require __DIR__ . '/regression-guards.php';
$cases = json_decode( file_get_contents( __DIR__ . '/number-cases.json' ), true, 512, JSON_THROW_ON_ERROR );
foreach ( $cases as $case ) {
    $actual = as_to_float( $case['input'] );
    as_test_check( is_finite( $actual ) && abs( $actual - $case['expected'] ) <= 1e-9 * max( 1, abs( $case['expected'] ) ), 'number ' . json_encode( $case['input'] ) );
}
foreach ( array( NAN, INF, -INF ) as $value ) as_test_check( 0.0 === as_to_float( $value ), 'non-finite number becomes zero' );
$GLOBALS['as_test_meta'][7] = array( '_price' => '1000,50', '_wholesalePrice' => array( 1 => '60,33', 2 => '70,25', 999 => '1' ) );
as_test_check( 1000.5 === as_get_product_price( 7 ), 'base price with comma' );
foreach ( array( array( '100,49', 0.0, 100.49 ), array( '100,50', 100.5, 70.25 ), array( '1000,49', 100.5, 70.25 ), array( '1000,50', 1000.5, 60.33 ) ) as $case ) {
    $cart = array( 7 => array( 'price' => $case[0], 'amountProduct' => 1 ) );
    as_test_check( abs( $case[1] - as_getActualStepDiscont( 7, $cart ) ) < 0.000001, 'step at total ' . $case[0] );
    as_test_check( abs( $case[2] - as_getActualPrice( 'cart', $cart, 7 ) ) < 0.000001, 'actual price at total ' . $case[0] );
}
$cart = array( 7 => array( 'price' => '1000,50', 'amountProduct' => 1 ) );
$GLOBALS['as_test_meta'][7]['_wholesalePrice'] = array( 2 => '70,25', 1 => '60,33' );
as_test_check( 60.33 === as_getActualPrice( 'cart', $cart, 7 ), 'reversing term order preserves the highest step' );
as_test_check( 60.33 === as_getActualPrice( 'productPage', array(), 7, '1000,50', 1 ), 'product page calculation' );
$GLOBALS['as_test_sales'] = false;
as_test_check( 1000.5 === as_getActualPrice( 'cart', $cart, 7 ), 'discounts disabled' );
$GLOBALS['as_test_sales'] = true;
$clean = as_sanitize_cart( array( 7 => array( 'price' => '0,01', 'amountProduct' => 1 ) ) );
as_test_check( 1000.5 === $clean[7]['price'], 'tampered cookie price replaced from database' );
function as_test_password_request( $old, $new, $confirm = null, $id = 7, $nonce = true, $submitted = null ) {
    $GLOBALS['as_test_user'] = (object) array( 'ID' => 7, 'user_login' => 'test', 'user_email' => 'test@example.com', 'data' => (object) array( 'user_pass' => password_hash( $old, PASSWORD_BCRYPT ) ) );
    $GLOBALS['as_test_stored'] = null;
    $GLOBALS['as_test_nonce_calls'] = 0;
    $GLOBALS['as_test_nonce_ok'] = $nonce;
    $_POST = array( 'userID' => $id, 'email' => 'test@example.com', 'oldEmail' => 'test@example.com', 'pass' => is_string( $submitted ?? $old ) ? addslashes( $submitted ?? $old ) : ( $submitted ?? $old ), 'newPass' => is_string( $new ) ? addslashes( $new ) : $new, 'newPassConfirm' => is_string( $confirm ?? $new ) ? addslashes( $confirm ?? $new ) : ( $confirm ?? $new ) );
    ob_start();
    try { as_cabinetEditEmailPassword_callback(); } catch ( AS_Test_Die $e ) {}
    $body = ob_get_clean();
    as_test_check( 1 === $GLOBALS['as_test_nonce_calls'], 'nonce checked before password handling' );
    return json_decode( $body, true );
}
foreach ( array( array( 'Ab%41<x>9  z!{#', 'Zy%42<q>7  k?' ), array( "A\\b'c\"d9", "Z\\y'q\"r8" ), array( '0', 'abcdef' ), array( 'abcdef', str_repeat( "\u{0416}", 6 ) ) ) as $pair ) {
    $result = as_test_password_request( $pair[0], $pair[1] );
    as_test_check( is_array( $result ) && empty( $result['errors'] ) && isset( $result['edited']['newPass'] ) && $GLOBALS['as_test_stored'] === $pair[1], 'exact password preservation' );
}
foreach ( array( '0', 'abcde', str_repeat( "\u{0416}", 5 ) ) as $short ) {
    $result = as_test_password_request( 'abcdef', $short );
    as_test_check( isset( $result['errors']['newPass'] ) && null === $GLOBALS['as_test_stored'], 'short password rejected in characters' );
}
$result = as_test_password_request( 'abcdef', 'ghijkl', null, 7, true, 'wrong password' );
as_test_check( isset( $result['errors']['pass'] ) && null === $GLOBALS['as_test_stored'], 'incorrect current password rejected' );
$result = as_test_password_request( 'abcdef', 'ghijkl', null, 7, true, array( 'not a string' ) );
as_test_check( isset( $result['errors']['pass'] ) && null === $GLOBALS['as_test_stored'], 'array current password rejected' );
$result = as_test_password_request( 'abcdef', 'ghijkl', 'xxxxxx' );
as_test_check( isset( $result['errors']['newPassConfirm'] ) && null === $GLOBALS['as_test_stored'], 'confirmation mismatch rejected' );
$result = as_test_password_request( '0', '' );
as_test_check( is_array( $result ) && empty( $result['errors'] ) && null === $GLOBALS['as_test_stored'], 'current password zero is not empty; blank new password means no change' );
$result = as_test_password_request( 'abcdef', array( 'not a string' ) );
as_test_check( null === $GLOBALS['as_test_stored'], 'array password never saved' );
as_test_password_request( 'abcdef', 'ghijkl', null, 8 );
as_test_check( null === $GLOBALS['as_test_stored'], 'foreign user ID rejected' );
as_test_password_request( 'abcdef', 'ghijkl', null, 7, false );
as_test_check( null === $GLOBALS['as_test_stored'], 'invalid nonce rejected' );
$data_source = file_get_contents( $root . '/plugins/argon-shop/includes/api/getData/getData.php' );
$cabinet_source = file_get_contents( $root . '/plugins/argon-shop/includes/interface/cabinet/myCabinet.php' );
as_test_check( as_test_number_guard( $data_source ), 'number guard accepts current source' );
as_test_check( as_test_password_guard( $cabinet_source ), 'password guard accepts current source' );
foreach ( array( 'floatval ( $stepPrice )', 'floatval /* comment */ ( $stepPrice )' ) as $bad ) {
    $mutant = str_replace( 'as_to_float( $stepPrice )', $bad, $data_source );
    as_test_check( ! as_test_number_guard( $mutant ), 'number guard detects ' . $bad );
}
$mutant = str_replace( "wp_unslash( \$_POST['pass'] )", "sanitize_text_field /* comment */ ( wp_unslash( \$_POST['pass'] ) )", $cabinet_source );
as_test_check( ! as_test_password_guard( $mutant ), 'password guard detects sanitizer with whitespace/comments' );
if ( in_array( '--json', $argv, true ) ) {
    echo json_encode( array_map( function ( $case ) { return as_to_float( $case['input'] ); }, $cases ) ) . "\n";
} else {
    echo ( $GLOBALS['as_test_fail'] ? 'FAILED' : 'OK' ) . ': ' . $GLOBALS['as_test_pass'] . ' passed, ' . $GLOBALS['as_test_fail'] . " failed (WordPress APIs are mocked)\n";
}
exit( $GLOBALS['as_test_fail'] ? 1 : 0 );
