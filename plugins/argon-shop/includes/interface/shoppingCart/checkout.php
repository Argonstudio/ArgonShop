<?php
/**
 * Оформление заказа — основной обработчик
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 *
 * ГДЕ ТЕСТИРОВАТЬ: Корзина → «Оформить заказ».
 * Автотест: tests/smoke-test.php, разделы 2–5.
 *
 * ПОРЯДОК (каждый шаг — отдельная функция/модуль):
 *  1. nonce, тип формы
 *  2. поля формы и обязательные поля → checkout-fields.php
 *  3. корзина из cookie (цены из БД) → as_sanitize_cart()
 *  4. дополнительные проверки модулей   → filter argon_shop_checkout_errors
 *  5. файлы                            → checkout-files.php
 *  6. регистрация                      → checkout-registration.php
 *  7. заказ в БД                       → as_checkout_create_order()
 *  8. письма                           → checkout-emails.php
 *  9. ответ на фронтенд, очистка корзины
 *
 * ХУКИ (для модулей оплаты, доставки и интеграций):
 * - filter argon_shop_shipping_methods ( array [ code => label ] )
 * - filter argon_shop_payment_methods  ( array [ code => label ] )
 * - filter argon_shop_checkout_errors  ( array $errors, array $order ) — вернуть сообщения об ошибках
 * - filter argon_shop_order_post_data  ( array $post_data, array $order ) — перед wp_insert_post()
 * - action argon_shop_order_created    ( int $order_id, array $order ) — заказ и мета сохранены
 * - filter argon_shop_checkout_response( array $result, array $order ) — ответ на фронтенд (например, редирект на оплату)
 * - filter argon_shop_email            — см. checkout-emails.php
 *
 * $order — массив контекста заказа:
 *   type_form, number, products [ID => amountProduct, price, name, link], amount (количество позиций),
 *   total, fields, email, shipping, shipping_label, payment, payment_label, user_id,
 *   registration_admin, registration_customer, order_id (after creation)
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_ajax_submitCart_shoppingCart', 'as_submitCart_shoppingCart_callback' );
add_action( 'wp_ajax_nopriv_submitCart_shoppingCart', 'as_submitCart_shoppingCart_callback' );

/**
 * ФУНКЦИЯ: способы доставки
 *
 * @return array [ code => name ]
 *
 * @since 1.1.0
 */
function as_get_shipping_methods() {
    return (array) apply_filters( 'argon_shop_shipping_methods', array(
        'selfExport'         => __( 'Pickup', 'argon-shop' ),
        'shippingToAddress'  => __( 'Delivery to address', 'argon-shop' ),
    ) );
}

/**
 * ФУНКЦИЯ: способы оплаты
 *
 * @return array [ code => name ]
 *
 * @since 1.1.0
 */
function as_get_payment_methods() {
    return (array) apply_filters( 'argon_shop_payment_methods', array(
        'paimentUponReceipt' => __( 'Payment on delivery', 'argon-shop' ),
    ) );
}

/**
 * ФУНКЦИЯ: ответ с ошибкой и завершение запроса
 *
 * @param string     $message  — текст ошибки (HTML допустим, пользовательские данные экранируйте заранее)
 * @param string     $typeForm — тип формы
 * @param array|null $files    — файлы для удаления
 *
 * @since 1.1.0
 */
function as_checkout_error( $message, $typeForm, $files = null ) {

    if ( $files ) {
        as_checkout_cleanup_files( $files );
    }

    echo wp_json_encode( array(
        'type'     => 'error',
        'typeForm' => $typeForm,
        'message'  => $message,
    ) );

    wp_die();
}

/**
 * ФУНКЦИЯ: создаёт заказ (post_type = shoporder) и его мета-поля
 *
 * @param array $order — контекст заказа
 *
 * @return int|WP_Error ID заказа
 *
 * @since 1.1.0
 */
function as_checkout_create_order( array $order ) {

    $post_data = array(
        'post_title'  => sprintf(
            /* translators: 1: номер заказа, 2: количество товаров, 3: итог заказа. */
            __( 'Order #%1$s. Items in order: %2$s, total: %3$s', 'argon-shop' ),
            $order['number'],
            $order['amount'],
            $order['total']
        ),
        'post_type'   => 'shoporder',
        'post_status' => 'publish',
        'post_author' => (int) $order['user_id'],
        'tax_input'   => array( 'statusorders' => array( 'neworder' ) ),
    );

    $post_data = apply_filters( 'argon_shop_order_post_data', $post_data, $order );

    $order_id = wp_insert_post( $post_data, true );

    if ( is_wp_error( $order_id ) ) {
        return $order_id;
    }

    add_post_meta( $order_id, '_productsCart', wp_slash( $order['products'] ) );
    add_post_meta( $order_id, '_fieldsCart', wp_slash( $order['fields'] ) );
    add_post_meta( $order_id, '_number', $order['number'] );
    add_post_meta( $order_id, '_productAmountCart', $order['amount'] );
    add_post_meta( $order_id, '_totalPrice', $order['total'] );

    if ( '' !== $order['shipping'] ) {
        add_post_meta( $order_id, '_shipping', wp_slash( $order['shipping'] ) );
    }

    if ( '' !== $order['payment'] ) {
        add_post_meta( $order_id, '_payment', wp_slash( $order['payment'] ) );
    }

    wp_set_object_terms( $order_id, 'neworder', 'statusorders' );

    // Обновляем счётчик сразу, чтобы параллельный заказ не получил тот же номер
    update_option( 'counterOrders', $order['number'] );

    return $order_id;
}

/**
 * ОБРАБОТЧИК: оформление заказа
 *
 * ГДЕ: страница корзины → кнопка «Оформить заказ»
 */
function as_submitCart_shoppingCart_callback() {

    // ============================================
    // 1. БЕЗОПАСНОСТЬ, ТИП ФОРМЫ
    // ============================================

    check_ajax_referer( 'argon_shop_nonce', 'nonce' );

    $typeForm = isset( $_POST['typeForm'] ) ? sanitize_key( wp_unslash( $_POST['typeForm'] ) ) : '';
    // sanitize_key приводит значение к нижнему регистру — восстанавливаем исходное имя типа формы
    $typeForms = array( 'quick' => 'quick', 'person' => 'person', 'legalperson' => 'legalPerson' );

    if ( ! isset( $typeForms[ $typeForm ] ) ) {
        as_checkout_error( __( 'Unknown order form type. Please refresh the page.', 'argon-shop' ), $typeForm );
    }

    $typeForm = $typeForms[ $typeForm ];

    // ============================================
    // 2. ПОЛЯ ФОРМЫ (при ошибке функция сама отвечает и завершает запрос)
    // ============================================

    $fieldsArrays = as_get_valueFieldsCart( $typeForm, $_POST );
    $fieldsCart   = ! empty( $fieldsArrays['fieldsCart'] ) ? $fieldsArrays['fieldsCart'] : array();

    $email = ! empty( $_POST['emailUser'] ) ? sanitize_email( wp_unslash( $_POST['emailUser'] ) ) : '';

    // ============================================
    // 3. КОРЗИНА
    // ============================================

    // Cookie → as_sanitize_cart(): только опубликованные товары, цены из базы данных
    $cart = as_getCookie( 'productsShoppingCart' );
    $cart = is_array( $cart ) ? $cart : array();

    if ( ! $cart ) {
        as_checkout_error( __( 'Your cart is empty. Add products and try again.', 'argon-shop' ), $typeForm );
    }

    // Количество на странице должно совпадать с cookie (защита от устаревшей вкладки)
    $productsFromPage = array();

    if ( ! empty( $_POST['productsCart'] ) ) {
        $decoded = json_decode( wp_unslash( $_POST['productsCart'] ), true );
        $productsFromPage = is_array( $decoded ) ? $decoded : array();
    }

    foreach ( $cart as $productID => $item ) {
        if ( ! isset( $productsFromPage[ $productID ]['amountProduct'] )
            || (float) $productsFromPage[ $productID ]['amountProduct'] !== (float) $item['amountProduct'] ) {
            as_checkout_error( __( 'Error: the number or names of products in the cart and on the page do not match. Try changing the quantity of any product or refresh the page (data entered in the form may be lost)', 'argon-shop' ), $typeForm );
        }
    }

    // Итоги и текущие цены (включая оптовые скидки)
    $actualPrices = as_getActualPriceAllProducts( $cart );
    $totals       = as_getDataCart( $cart );

    foreach ( $cart as $productID => $item ) {
        $cart[ $productID ]['name']  = get_the_title( $productID );
        $cart[ $productID ]['link']  = get_post_permalink( $productID );
        $cart[ $productID ]['price'] = isset( $actualPrices[ $productID ] ) ? $actualPrices[ $productID ] : $item['price'];
    }

    // Доставка и оплата — только из списка известных способов
    $shippingMethods = as_get_shipping_methods();
    $paymentMethods  = as_get_payment_methods();

    $shipping = ! empty( $_POST['shipping'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping'] ) ) : '';
    $payment  = ! empty( $_POST['payment'] ) ? sanitize_text_field( wp_unslash( $_POST['payment'] ) ) : '';
    $shipping = isset( $shippingMethods[ $shipping ] ) ? $shipping : '';
    $payment  = isset( $paymentMethods[ $payment ] ) ? $payment : '';

    $counterOrders = (int) get_option( 'counterOrders', 0 );

    $order = array(
        'type_form'             => $typeForm,
        'number'                => $counterOrders + 1,
        'products'              => $cart,
        'amount'                => (int) $totals['productAmountCart'],
        'total'                 => (float) $totals['totalPrice'],
        'fields'                => $fieldsCart,
        'email'                 => $email,
        'shipping'              => $shipping,
        'shipping_label'        => $shipping ? $shippingMethods[ $shipping ] : __( 'Not specified', 'argon-shop' ),
        'payment'               => $payment,
        'payment_label'         => $payment ? $paymentMethods[ $payment ] : __( 'Not specified', 'argon-shop' ),
        'user_id'               => get_current_user_id(),
        'registration_admin'    => '',
        'registration_customer' => '',
    );

    // ============================================
    // 4. ДОПОЛНИТЕЛЬНЫЕ ПРОВЕРКИ МОДУЛЕЙ
    // ============================================

    $errors = (array) apply_filters( 'argon_shop_checkout_errors', array(), $order );

    if ( $errors ) {
        as_checkout_error( implode( '<br>', array_map( 'esc_html', $errors ) ), $typeForm );
    }

    // ============================================
    // 5. ФАЙЛЫ
    // ============================================

    $files = as_checkout_handle_files();

    if ( is_wp_error( $files ) ) {
        as_checkout_error( esc_html( $files->get_error_message() ), $typeForm );
    }

    // ============================================
    // 6. РЕГИСТРАЦИЯ
    // ============================================

    $message = '';

    if ( ! empty( $_POST['cartReg'] ) ) {

        $login = ! empty( $_POST['loginUser'] ) ? wp_unslash( $_POST['loginUser'] ) : '';
        $registration = as_checkout_register_user( $login, $email, ! empty( $fieldsArrays['accountData'] ) ? $fieldsArrays['accountData'] : array() );

        if ( is_wp_error( $registration ) ) {
            as_checkout_error( __( 'Registration error: ', 'argon-shop' ) . esc_html( wp_strip_all_tags( $registration->get_error_message() ) ), $typeForm, $files );
        }

        $order['user_id'] = $registration['user_id'];

        $order['registration_customer'] = '<p><b style="font-size: 16px;">' . esc_html__( 'You are registered on the site.', 'argon-shop' ) . '</b></p>'
            . '<p><b>' . esc_html__( 'Username:', 'argon-shop' ) . '</b> ' . esc_html( $registration['login'] ) . '</p>'
            . '<p><a href="' . esc_url( $registration['password_url'] ) . '">' . esc_html__( 'Set your password using this link', 'argon-shop' ) . '</a></p>';

        $nameUser = ! empty( $_POST['nameUser'] ) ? sanitize_text_field( wp_unslash( $_POST['nameUser'] ) ) : '';

        $message .= $nameUser ? esc_html( $nameUser ) . ', ' : '';
        $message .= sprintf(
            /* translators: %s: email покупателя. */
            esc_html__( 'You have been registered. A link to set your password has been sent to %s', 'argon-shop' ),
            esc_html( $email )
        );
        $message .= '<p>' . esc_html__( 'You are now logged in.', 'argon-shop' ) . '</p>';

        if ( as_get_cabinetPageURL() ) {
            $message .= '<p>' . sprintf(
                /* translators: %s: ссылка на страницу кабинета покупателя. */
                __( 'You can <a href="%s">go to your account</a> to edit your details and track your orders.', 'argon-shop' ),
                esc_url( as_get_cabinetPageURL() )
            ) . '</p>';
        }
    }

    // ============================================
    // 7. ЗАКАЗ В БАЗЕ ДАННЫХ
    // ============================================

    $order_id = as_checkout_create_order( $order );

    if ( is_wp_error( $order_id ) ) {
        as_checkout_error( __( 'Error saving the order to the database: ', 'argon-shop' ) . esc_html( $order_id->get_error_message() ), $typeForm, $files );
    }

    $order['order_id'] = $order_id;

    do_action( 'argon_shop_order_created', $order_id, $order );

    // ============================================
    // 8. ПИСЬМА
    // ============================================

    as_checkout_send_emails( $order, $files['paths'] );

    as_checkout_cleanup_files( $files );

    // ============================================
    // 9. ОТВЕТ, ОЧИСТКА КОРЗИНЫ
    // ============================================

    $message .= sprintf(
        /* translators: %s: номер заказа. */
        __( 'Thank you for your order! To change or track your order, remember its number: <b>%s</b><p></p> Our manager will contact you shortly', 'argon-shop' ),
        (int) $order['number']
    );

    $result = apply_filters( 'argon_shop_checkout_response', array(
        'type'     => 'success',
        'typeForm' => $typeForm,
        'message'  => $message,
    ), $order );

    setcookie( 'productsShoppingCart', '', array(
        'expires'  => time() - 1209600,
        'path'     => COOKIEPATH ? COOKIEPATH : '/',
        'domain'   => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
        'secure'   => is_ssl(),
        'httponly' => false,
        'samesite' => 'Lax',
    ) );

    echo wp_json_encode( $result );

    wp_die();
}
