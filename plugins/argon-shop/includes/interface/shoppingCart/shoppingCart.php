<?php
/**
 * Корзина покупок — AJAX-обработчики
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * 1. Страница товара → кнопка "В корзину" (addProducts)
 * 2. Страница товара → изменение количества → пересчёт цены (wholesalePrice)
 * 3. Корзина → изменение количества → пересчёт цены (amountProducts)
 * 4. Корзина → удаление товара (deleteProducts)
 * 5. Корзина → очистка корзины (deleteAllProducts)
 * 6. Корзина → оформление заказа (submitCart)
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Регистрация AJAX-обработчиков
add_action( 'wp_ajax_wholesalePrice_shoppingCart', 'wholesalePrice_shoppingCart_callback' );
add_action( 'wp_ajax_nopriv_wholesalePrice_shoppingCart', 'wholesalePrice_shoppingCart_callback' );

add_action( 'wp_ajax_addProducts_shoppingCart', 'addProducts_shoppingCart_callback' );
add_action( 'wp_ajax_nopriv_addProducts_shoppingCart', 'addProducts_shoppingCart_callback' );

add_action( 'wp_ajax_amountProducts_shoppingCart', 'amountProducts_shoppingCart_callback' );
add_action( 'wp_ajax_nopriv_amountProducts_shoppingCart', 'amountProducts_shoppingCart_callback' );

add_action( 'wp_ajax_deleteProducts_shoppingCart', 'deleteProducts_shoppingCart_callback' );
add_action( 'wp_ajax_nopriv_deleteProducts_shoppingCart', 'deleteProducts_shoppingCart_callback' );

add_action( 'wp_ajax_deleteAllProducts_shoppingCart', 'deleteAllProducts_shoppingCart_callback' );
add_action( 'wp_ajax_nopriv_deleteAllProducts_shoppingCart', 'deleteAllProducts_shoppingCart_callback' );

add_action( 'wp_ajax_submitCart_shoppingCart', 'submitCart_shoppingCart_callback' );
add_action( 'wp_ajax_nopriv_submitCart_shoppingCart', 'submitCart_shoppingCart_callback' );

/**
 * ОБРАБОТЧИК: Оптовая цена
 * ГДЕ: Страница товара → изменение количества
 */
function wholesalePrice_shoppingCart_callback() {
    
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    $type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
    $productID = isset( $_POST['productID'] ) ? absint( $_POST['productID'] ) : 0;
    $productAmount = isset( $_POST['amountProduct'] ) ? floatval( $_POST['amountProduct'] ) : 0;
    
    if ( 0 === $productID ) {
        wp_die( 'Неверный ID товара' );
    }
    
    $productDiscount = getDiscount( $productID );
    
    if ( $type === 'get' && ! $productDiscount ) {
        echo 'none';
        wp_die();
    }
    
    $productsShoppingCart = getCookie( 'productsShoppingCart' );
    
    $productPrice = get_post_meta( $productID, '_price', true );
    $productPrice = str_replace( ',', '.', $productPrice );
    $productPrice = (float) $productPrice;
    
    $productPrice = getActualPrice( 'productPage', $productsShoppingCart, $productID, $productPrice, $productAmount );
    
    $totalPriceProduct = $productPrice * $productAmount;
    
    echo esc_html( $totalPriceProduct );
    
    wp_die();
}

/**
 * ОБРАБОТЧИК: Изменение количества товара в корзине
 * ГДЕ: Страница корзины → изменение количества
 */
function amountProducts_shoppingCart_callback() {
    
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    $productID = isset( $_POST['productID'] ) ? absint( $_POST['productID'] ) : 0;
    $productAmount = isset( $_POST['productAmount'] ) ? floatval( $_POST['productAmount'] ) : 0;
    
    if ( 0 === $productID ) {
        wp_die( 'Неверный ID товара' );
    }
    
    if ( $productAmount <= 0 ) {
        $productAmount = 1;
    }
    
    $productsShoppingCart = getCookie( 'productsShoppingCart' );
    
    if ( ! is_array( $productsShoppingCart ) ) {
        $productsShoppingCart = array();
    }
    
    $productsShoppingCart[ $productID ]['amountProduct'] = $productAmount;
    
    $productsShoppingCartJson = wp_json_encode( $productsShoppingCart );
    
    setcookie( 'productsShoppingCart', $productsShoppingCartJson, time() + 1209600, COOKIEPATH, COOKIE_DOMAIN );
    
    $weight = get_post_meta( $productID, '_weight', true );
    $weight = str_replace( ',', '.', $weight );
    $weight = (float) $weight;
    
    $actualPriceAllProducts = getActualPriceAllProducts( $productsShoppingCart );
    
    $forFront = array();
    
    if ( $weight ) $forFront['weight'] = $weight;
    if ( $actualPriceAllProducts ) $forFront['actualPrices'] = $actualPriceAllProducts;
    $forFront['productsShoppingCart'] = $productsShoppingCart;
    
    $forFront = wp_json_encode( $forFront );
    
    echo $forFront;
    
    wp_die();
}

/**
 * ОБРАБОТЧИК: Очистка корзины
 * ГДЕ: Страница корзины → кнопка "Очистить корзину"
 */
function deleteAllProducts_shoppingCart_callback() {
    
    setcookie( 'productsShoppingCart', '', time() - 1209600, COOKIEPATH, COOKIE_DOMAIN );
    
    wp_die();
}

/**
 * ОБРАБОТЧИК: Удаление товара из корзины
 * ГДЕ: Страница корзины → кнопка удаления товара
 */
function deleteProducts_shoppingCart_callback() {
    
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    $productID = isset( $_POST['productID'] ) ? absint( $_POST['productID'] ) : 0;
    
    if ( 0 === $productID ) {
        wp_die( 'Неверный ID товара' );
    }
    
    $productsShoppingCart = getCookie( 'productsShoppingCart' );
    
    if ( ! is_array( $productsShoppingCart ) ) {
        $productsShoppingCart = array();
    }
    
    unset( $productsShoppingCart[ $productID ] );
    
    $productsShoppingCartJson = wp_json_encode( $productsShoppingCart );
    
    setcookie( 'productsShoppingCart', $productsShoppingCartJson, time() + 1209600, COOKIEPATH, COOKIE_DOMAIN );
    
    if ( ! $productsShoppingCart ) {
        echo 'false';
        wp_die();
    }
    
    $actualPriceAllProducts = getActualPriceAllProducts( $productsShoppingCart );
    
    $actualPriceAllProducts = wp_json_encode( $actualPriceAllProducts );
    
    echo $actualPriceAllProducts;
    
    wp_die();
}

/**
 * ОБРАБОТЧИК: Добавление товара в корзину
 * ГДЕ: Страница товара → кнопка "В корзину" / "Купить"
 */
function addProducts_shoppingCart_callback() {
    
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    $productID = isset( $_POST['productID'] ) ? absint( $_POST['productID'] ) : 0;
    $amountProduct = isset( $_POST['amountProduct'] ) ? absint( $_POST['amountProduct'] ) : 1;
    
    if ( 0 === $productID ) {
        wp_die( 'Неверный ID товара' );
    }
    
    if ( $amountProduct <= 0 ) {
        $amountProduct = 1;
    }
    
    $productPrice = get_post_meta( $productID, '_price', true );
    $productPrice = str_replace( ',', '.', $productPrice );
    $productPrice = (float) $productPrice;
    
    $data = array(
        'amountProduct' => $amountProduct,
        'price'         => $productPrice,
    );
    
    $productsShoppingCart = array();
    
    if ( getCookie( 'productsShoppingCart' ) ) {
        $productsShoppingCart = getCookie( 'productsShoppingCart' );
        
        if ( ! is_array( $productsShoppingCart ) ) {
            $productsShoppingCart = array();
        }
    }
    
    if ( isset( $productsShoppingCart[ $productID ] ) && $productsShoppingCart[ $productID ] ) {
        $data['amountProduct'] += $productsShoppingCart[ $productID ]['amountProduct'];
    }
    
    $productsShoppingCart[ $productID ] = $data;
    
    $productsShoppingCartJson = wp_json_encode( $productsShoppingCart );
    
    setcookie( 'productsShoppingCart', $productsShoppingCartJson, time() + 1209600, COOKIEPATH, COOKIE_DOMAIN );
    
    $dataCart = getDataCart( $productsShoppingCart );
    
    $forFront = array(
        'amountProduct' => $data['amountProduct'],
    );
    
    if ( $dataCart ) {
        $forFront['productAmountCart'] = $dataCart['productAmountCart'];
        $forFront['totalPrice'] = $dataCart['totalPrice'];
    }
    
    $forFront = wp_json_encode( $forFront );
    
    echo $forFront;
    
    wp_die();
}

/**
 * ОБРАБОТЧИК: Оформление заказа
 * 
 * ГДЕ: Страница корзины → кнопка "Оформить заказ"
 * ЧТО ДЕЛАЕТ:
 * 1. Валидирует данные формы
 * 2. Проверяет соответствие товаров корзины и формы
 * 3. Создаёт заказ в БД (post_type = shoporder)
 * 4. Регистрирует пользователя (если отмечен cartReg)
 * 5. Формирует тексты писем через emailTextsGenerator()
 * 6. Отправляет письма через wp_mail():
 *    - администратору — о новом заказе
 *    - покупателю — с подтверждением и паролем (если регистрация)
 * 
 * ЗАВИСИМОСТИ:
 * - get_valueFieldsCart()    — includes/interface/shoppingCart/shoppingCart.php
 * - getDataCart()            — includes/api/getData/getData.php
 * - getActualPriceAllProducts() — includes/api/getData/getData.php
 * - emailTextsGenerator()    — includes/interface/shoppingCart/shoppingCart.php
 */
function submitCart_shoppingCart_callback() {
    
    // ============================================
    // 1. ПРОВЕРКА БЕЗОПАСНОСТИ
    // ============================================
    
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    global $user_ID;
    
    // ============================================
    // 2. ПОЛУЧЕНИЕ И ВАЛИДАЦИЯ ДАННЫХ ФОРМЫ
    // ============================================
    
    $typeForm = isset( $_POST['typeForm'] ) 
        ? wp_strip_all_tags( wp_unslash( $_POST['typeForm'] ) ) 
        : '';
    
    // Счётчик заказов
    $counterOrders = get_option( 'counterOrders' );
    
    if ( ! $counterOrders ) {
        $counterOrders = 0;
    }
    
    // Получение полей формы через плагин
    $fieldsArrays = get_valueFieldsCart( $typeForm, $_POST );
    
    $fieldsCart = ( ! empty( $fieldsArrays['fieldsCart'] ) ) 
        ? $fieldsArrays['fieldsCart'] 
        : array();
    
    // Email покупателя
    $email = ( ! empty( $_POST['emailUser'] ) ) 
        ? sanitize_email( wp_unslash( $_POST['emailUser'] ) ) 
        : '';
    
    // Товары из формы (JSON)
    $productsCart = array();
    
    if ( ! empty( $_POST['productsCart'] ) ) {
        
        $productsCart_raw = stripslashes( $_POST['productsCart'] );
        $productsCart_decoded = json_decode( $productsCart_raw, true );
        
        if ( is_array( $productsCart_decoded ) ) {
            $productsCart = $productsCart_decoded;
        }
    }
    
    // Товары из куки
    $productsCartCookie = getCookie( 'productsShoppingCart' );
    
    if ( ! is_array( $productsCartCookie ) ) {
        $productsCartCookie = array();
    }
    
    // Доставка и оплата
    $shipping = 'Не указано';
    $payment  = 'Не указано';
    $shippingType = '';
    $paymentType  = '';
    
    if ( ! empty( $_POST['shipping'] ) ) {
        
        $shippingType = sanitize_text_field( wp_unslash( $_POST['shipping'] ) );
        
        switch ( $shippingType ) {
            case 'selfExport':
                $shipping = 'Самовывоз';
                break;
            case 'shippingToAddress':
                $shipping = 'Доставка по адресу';
                break;
        }
    }
    
    if ( ! empty( $_POST['payment'] ) ) {
        
        $paymentType = sanitize_text_field( wp_unslash( $_POST['payment'] ) );
        
        switch ( $paymentType ) {
            case 'paimentUponReceipt':
                $payment = 'Оплата при получении';
                break;
        }
    }
    
    // ============================================
    // 3. ПОДГОТОВКА РЕЗУЛЬТАТА
    // ============================================
    
    $to      = get_option( 'admin_email' );
    $subject = 'Получен заказ';
    $subjectUser = 'Ваш заказ успешно добавлен';
    
    $result = array(
        'type'     => 'error',
        'typeForm' => $typeForm,
    );
    
    // ============================================
    // 4. ПРОВЕРКА СООТВЕТСТВИЯ ТОВАРОВ КУКИ И ФОРМЫ
    // ============================================
    
    $clearCartCookie = array();
    
    foreach ( $productsCartCookie as $key => $value ) {
        
        $key_clean = absint( $key );
        
        if ( ! isset( $productsCart[ $key_clean ] ) 
            || $value['amountProduct'] != $productsCart[ $key_clean ]['amountProduct'] ) {
            
            $result['message'] = 'Ошибка: Число или наименования товаров в корзине и на странице не совпадают, попробуйте изменить число любого из товаров или обновите страницу (возможна потеря данных, введённых в форму)';
            
            echo wp_json_encode( $result );
            wp_die();
        }
        
        $clearCartCookie[ $key_clean ] = array();
        
        foreach ( $value as $nameDataProduct => $valueDataProduct ) {
            $clearCartCookie[ $key_clean ][ sanitize_text_field( $nameDataProduct ) ] = sanitize_text_field( $valueDataProduct );
        }
    }
    
    $productsCart = $clearCartCookie;
    
    // ============================================
    // 5. РАСЧЁТ ЦЕН И ИТОГОВ
    // ============================================
    
    $actualPriceProducts = getActualPriceAllProducts( $productsCart );
    $totalPriseAndAmount = getDataCart( $productsCart );
    
    $totalPrice        = $totalPriseAndAmount['totalPrice'];
    $productAmountCart = $totalPriseAndAmount['productAmountCart'];
    
    // Обогащение данных товаров
    foreach ( $productsCart as $key => $value ) {
        
        $key = absint( $key );
        $post_data = get_post( $key );
        
        if ( ! $post_data ) {
            continue;
        }
        
        $post_name        = $post_data->post_title;
        $post_link        = get_post_permalink( $key );
        $post_actualPrice = isset( $actualPriceProducts[ $key ] ) 
            ? $actualPriceProducts[ $key ] 
            : $value['price'];
        
        $productsCart[ $key ]['name']  = $post_name;
        $productsCart[ $key ]['link']  = $post_link;
        $productsCart[ $key ]['price'] = $post_actualPrice;
    }
    
    // ============================================
    // 6. ОБРАБОТКА ЗАГРУЖЕННЫХ ФАЙЛОВ
    // ============================================
    // 
    // Файлы сохраняются в wp-content/uploads/order-files/ с оригинальными
    // именами. Это нужно, чтобы PHPMailer взял правильное имя вложения,
    // а не имя временного файла PHP (phpUwOBxW.bin).
    // 
    // После отправки писем файлы удаляются.
    
    $allowed_filetypes = array( 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'gif', 'bmp', 'png', 'pdf' );
    
    $attachments         = array();   // Пути к файлам для wp_mail()
    $attachments_to_delete = array(); // Файлы для удаления после отправки
    $filesize            = 0;
    
    if ( ! empty( $_FILES['fileCart'] ) && ! empty( $_FILES['fileCart']['name'] ) ) {
        
        // Папка для временного хранения файлов заказа
        $upload_dir = wp_upload_dir();
        $orders_dir = $upload_dir['basedir'] . '/order-files';
        
        if ( ! file_exists( $orders_dir ) ) {
            wp_mkdir_p( $orders_dir );
        }
        
        for ( $i = 0; $i < count( $_FILES['fileCart']['name'] ); $i++ ) {
            
            if ( ! is_uploaded_file( $_FILES['fileCart']['tmp_name'][ $i ] ) ) {
                continue;
            }
            
            $original_name = sanitize_file_name( $_FILES['fileCart']['name'][ $i ] );
            $filesize += (float) $_FILES['fileCart']['size'][ $i ];
            
            // Проверка расширения
            $ext = strtolower( pathinfo( $original_name, PATHINFO_EXTENSION ) );
            
            if ( ! in_array( $ext, $allowed_filetypes, true ) ) {
                
                $result['message'] = 'Допустимые форматы файлов: ' . implode( ', ', $allowed_filetypes );
                
                echo wp_json_encode( $result );
                wp_die();
            }
            
            // Формируем уникальное имя, сохраняя оригинальное
            $base_name = pathinfo( $original_name, PATHINFO_FILENAME );
            $new_filename = $original_name;
            $counter = 1;
            
            while ( file_exists( $orders_dir . '/' . $new_filename ) ) {
                $new_filename = $base_name . '_' . $counter . '.' . $ext;
                $counter++;
            }
            
            $new_path = $orders_dir . '/' . $new_filename;
            
            // Перемещаем из временной папки PHP в uploads
            if ( move_uploaded_file( $_FILES['fileCart']['tmp_name'][ $i ], $new_path ) ) {
                $attachments[] = $new_path;
                $attachments_to_delete[] = $new_path;
            }
        }
    }
    
    if ( $filesize >= 15000000 ) {
        
        $result['message'] = 'Общий размер файлов не должен превышать 15 МБ.';
        
        // Удаляем уже сохранённые файлы, чтобы не мусорить
        foreach ( $attachments_to_delete as $file ) {
            if ( file_exists( $file ) ) {
                @unlink( $file );
            }
        }
        
        echo wp_json_encode( $result );
        wp_die();
    }
    
    // ============================================
    // 7. РЕГИСТРАЦИЯ ПОЛЬЗОВАТЕЛЯ
    // ============================================
    
    $registrationMessage = '';
    $userSignon          = false;
    
    try {
        
        $cartReg = ( ! empty( $_POST['cartReg'] ) ) 
            ? wp_strip_all_tags( wp_unslash( $_POST['cartReg'] ) ) 
            : 0;
        
        if ( $cartReg ) {
            
            $user_name       = ( ! empty( $_POST['loginUser'] ) ) 
                ? sanitize_user( wp_unslash( $_POST['loginUser'] ) ) 
                : '';
            
            $random_password = wp_generate_password( 12 );
            
            $user_ID = wp_create_user( $user_name, $random_password, $email );
            
            if ( is_wp_error( $user_ID ) ) {
                throw new Exception( $user_ID->get_error_message() );
            }
            
            $registrationMessage = '<p><b style="font-size: 16px;">Вы успешно зарегистрированы:</b></p>';
            $registrationMessage .= '<p><b>Логин:</b> ' . esc_html( $user_name ) . '</p>';
            $registrationMessage .= '<p><b>Пароль:</b> ' . esc_html( $random_password ) . '</p>';
            
            // Приветствие по имени
            $nameUser = ( ! empty( $_POST['nameUser'] ) ) 
                ? sanitize_text_field( wp_unslash( $_POST['nameUser'] ) ) 
                : '';
            
            if ( $nameUser ) {
                $result['message'] = ( ! empty( $result['message'] ) ) 
                    ? $result['message'] . $nameUser . ', ' 
                    : $nameUser . ', ';
            }
            
            // Сообщение о регистрации для фронта
            $reg_message = 'Вы успешно зарегистрированы, пароль отправлен на email: ' . $email;
            
            $result['message'] = ( ! empty( $result['message'] ) ) 
                ? $result['message'] . $reg_message 
                : $reg_message;
            
            // Сохранение данных аккаунта
            if ( ! empty( $fieldsArrays['accountData'] ) ) {
                add_user_meta( $user_ID, 'accountData', $fieldsArrays['accountData'] );
            }
            
            // Автоматический вход
            $creds = array(
                'user_login'    => $user_name,
                'user_password' => $random_password,
                'remember'      => false,
            );
            
            $userSignon = wp_signon( $creds, false );
            
            if ( ! is_wp_error( $userSignon ) ) {
                
                $result['message'] .= '<p>Произведён вход на сайт.</p>';
                
                if ( function_exists( 'get_cabinetPageURL' ) && get_cabinetPageURL() ) {
                    $result['message'] .= '<p>Вы можете <a href="' . esc_url( get_cabinetPageURL() ) . '">перейти в личный кабинет</a> для редактирования личных данных и отслеживания заказов.</p>';
                }
            }
        }
        
    } catch ( Exception $e ) {
        
        if ( empty( $result['message'] ) ) {
            $result['message'] = '';
        }
        
        $result['message'] .= 'Ошибка при регистрации: ' . $e->getMessage();
        
        echo wp_json_encode( $result );
        wp_die();
    }
    
    // ============================================
    // 8. СОЗДАНИЕ ЗАКАЗА В БД
    // ============================================
    
    $post_data = array(
        'post_title'  => 'Заказ №' . ( $counterOrders + 1 ) . ' Товаров в заказе: ' . $productAmountCart . ', на сумму: ' . $totalPrice,
        'post_type'   => 'shoporder',
        'post_status' => 'publish',
        'post_author' => $user_ID,
        'tax_input'   => array( 'statusorders' => array( 'neworder' ) ),
    );
    
    $post_id = wp_insert_post( $post_data );
    
    if ( is_wp_error( $post_id ) ) {
        
        $result['message'] .= 'Ошибка при записи заказа в базу данных: ' . $post_id->get_error_message();
        
        echo wp_json_encode( $result );
        wp_die();
    }
    
    // Мета-поля заказа
    add_post_meta( $post_id, '_productsCart', wp_slash( $productsCart ) );
    add_post_meta( $post_id, '_fieldsCart', wp_slash( $fieldsCart ) );
    add_post_meta( $post_id, '_number', $counterOrders + 1 );
    add_post_meta( $post_id, '_productAmountCart', $productAmountCart );
    add_post_meta( $post_id, '_totalPrice', $totalPrice );
    
    if ( ! empty( $shippingType ) ) {
        add_post_meta( $post_id, '_shipping', wp_slash( $shippingType ) );
    }
    
    if ( ! empty( $paymentType ) ) {
        add_post_meta( $post_id, '_payment', wp_slash( $paymentType ) );
    }
    
    wp_set_object_terms( $post_id, 'neworder', 'statusorders' );
    
    $actuallyCounterOrder = ( ! empty( $counterOrders ) ) ? $counterOrders + 1 : 1;
    
    // ============================================
    // 9. ФОРМИРОВАНИЕ ТЕКСТОВ ПИСЕМ
    // ============================================
    // 
    // emailTextsGenerator() возвращает чистый HTML без MIME.
    // MIME-обёртку, кодировку заголовков и вложения берёт на себя wp_mail().
    
    try {
        
        $message = emailTextsGenerator(
            'messageForAdmin',
            $actuallyCounterOrder,
            $productsCart,
            $productAmountCart,
            $totalPrice,
            $shipping,
            $payment,
            $fieldsCart,
            $registrationMessage
        );
        
        $messageForUser = emailTextsGenerator(
            'messageForUser',
            $actuallyCounterOrder,
            $productsCart,
            $productAmountCart,
            $totalPrice,
            $shipping,
            $payment,
            $fieldsCart,
            $registrationMessage
        );
        
    } catch ( Exception $e ) {
        
        $result['message'] .= 'Ошибка при создании текста письма: ' . $e->getMessage();
        
        echo wp_json_encode( $result );
        wp_die();
    }
    
    // ============================================
    // 10. ОТПРАВКА ПИСЕМ
    // ============================================
    // 
    // Используется wp_mail() вместо mail():
    // - Корректно кодирует заголовки (Subject, From) — Gmail не покажет =?B??=
    // - Работает с вложениями через массив $attachments
    // - На Beget не подменяет From, если он на домене сайта
    // - Возвращает true/false для логирования
    
    $site_domain  = wp_parse_url( home_url(), PHP_URL_HOST );
    $site_domain  = str_replace( 'www.', '', $site_domain );
    $from_noreply = 'noreply@' . $site_domain;
    
    // --- Письмо администратору ---
    
    $headers_admin = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ArgonShop <' . $from_noreply . '>'
        
    );
    
    if ( ! empty( $email ) && is_email( $email ) ) {
        $headers_admin[] = 'Reply-To: ' . $email;
    }
    
    $result_admin = wp_mail(
        $to,
        $subject,
        $message,
        $headers_admin,
        $attachments
    );
    
    // --- Письмо покупателю ---
    
    $result_user = false;
    
    if ( ! empty( $email ) && is_email( $email ) ) {
        
        $headers_user = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ArgonShop <' . $from_noreply . '>',
            'Reply-To: ' . $to,
        );
        
        $result_user = wp_mail(
            $email,
            $subjectUser,
            $messageForUser,
            $headers_user,
            $attachments
        );
    }
    
    // ============================================
    // 11. УДАЛЕНИЕ ВРЕМЕННЫХ ФАЙЛОВ
    // ============================================
    // 
    // Файлы сохранялись в uploads/order-files/ с оригинальными именами,
    // чтобы PHPMailer взял правильное имя вложения (photo.jpg вместо
    // phpUwOBxW.bin). После отправки писем они удаляются.
    
    foreach ( $attachments_to_delete as $file ) {
        if ( file_exists( $file ) ) {
            @unlink( $file );
        }
    }
    
    // ============================================
    // 12. ФИНАЛЬНЫЙ ОТВЕТ
    // ============================================
    
    $result['type'] = 'success';
    
    $success_message = 'Благодарим за ваш заказ! Для изменения и отслеживания заказа запомните его номер: <b>' . $actuallyCounterOrder . '</b><p></p> Наш менеджер свяжется с Вами в ближайшее время';
    
    if ( ! empty( $result['message'] ) ) {
        $result['message'] = $result['message'] . $success_message;
    } else {
        $result['message'] = $success_message;
    }
    
    // Обновление счётчика заказов
    update_option( 'counterOrders', $actuallyCounterOrder );
    
    // ============================================
    // 13. ОЧИСТКА КОРЗИНЫ
    // ============================================

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

/**
 * ФУНКЦИЯ: Создание HTML-текста письма
 * 
 * Возвращает чистый HTML. MIME-обёртку, кодирование заголовков и 
 * вложения берёт на себя wp_mail() + PHPMailer.
 * 
 * @param string $type                 — тип письма: messageForAdmin или messageForUser
 * @param int    $actuallyCounterOrder — номер заказа
 * @param array  $productsCart         — товары в заказе
 * @param int    $productAmountCart    — количество товаров
 * @param float  $cartPrice            — общая сумма
 * @param string $shipping             — способ доставки
 * @param string $payment              — способ оплаты
 * @param array  $fieldsCart           — данные покупателя
 * @param string $registrationMessage  — сообщение о регистрации (логин/пароль)
 * 
 * @return string Чистый HTML-текст письма
 */
function emailTextsGenerator(
    string $type = 'messageForAdmin',
    int $actuallyCounterOrder = 0,
    array $productsCart = array(),
    int $productAmountCart = 0,
    float $cartPrice = 0,
    string $shipping = '',
    string $payment = '',
    array $fieldsCart = array(),
    $registrationMessage = null
) {
    
    ob_start();
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Заказ</title>
    </head>
    <body style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5;">
    
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <h2 style="color: #808080; font-size: 18px; margin: 0 0 20px 0;">Детали заказа:</h2>
    
    <?php if ( $type === 'messageForAdmin' ) { ?>
        <p><b>Номер заказа:</b> <?php echo $actuallyCounterOrder; ?></p>
    <?php } elseif ( $type === 'messageForUser' ) { ?>
        <p><b>Номер вашего заказа:</b> <?php echo $actuallyCounterOrder; ?></p>
    <?php } ?>
    
    <?php if ( ! empty( $productAmountCart ) ) { ?>
        <p><b>Наименований товаров:</b> <?php echo $productAmountCart; ?></p>
    <?php } ?>
    
    <?php if ( ! empty( $cartPrice ) ) { ?>
        <p><b>На сумму:</b> <?php echo $cartPrice; ?></p>
    <?php } ?>
    
    <?php if ( ! empty( $shipping ) ) { ?>
        <p><b>Способ доставки:</b> <?php echo esc_html( $shipping ); ?></p>
    <?php } ?>
    
    <?php if ( ! empty( $payment ) ) { ?>
        <p><b>Способ оплаты:</b> <?php echo esc_html( $payment ); ?></p>
    <?php } ?>
    
    <h3 style="color: #808080; font-size: 16px; margin: 30px 0 15px 0;">Перечень товаров:</h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <?php foreach ( $productsCart as $key => $value ) { 
            
            $key = absint( $key );
            
            $product_link  = isset( $value['link'] ) ? esc_url( $value['link'] ) : '';
            $product_name  = isset( $value['name'] ) ? esc_html( $value['name'] ) : '';
            $amount        = isset( $value['amountProduct'] ) ? floatval( $value['amountProduct'] ) : 0;
            $price         = isset( $value['price'] ) ? floatval( $value['price'] ) : 0;
            
            ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px 0;">
                    <a href="<?php echo $product_link; ?>" style="color: #0066cc;"><?php echo $product_name; ?></a>
                    
                    <?php if ( $type === 'messageForAdmin' ) { 
                        $productArticle = get_post_meta( $key, '_article', true );
                        
                        if ( ! empty( $productArticle ) ) { ?>
                            <br><small style="color: #888;">Артикул: <?php echo esc_html( $productArticle ); ?></small>
                        <?php } ?>
                    <?php } ?>
                </td>
                <td style="padding: 10px 0; text-align: right;">
                    <?php echo $amount; ?> шт × <?php echo $price; ?> ₽
                </td>
                <td style="padding: 10px 0; text-align: right; white-space: nowrap;">
                    <b><?php echo ( $price * $amount ); ?> ₽</b>
                </td>
            </tr>
        <?php } ?>
    </table>
    
    <?php if ( ! empty( $fieldsCart ) && is_array( $fieldsCart ) ) { ?>
    
        <h3 style="color: #808080; font-size: 16px; margin: 30px 0 15px 0;">
            <?php echo ( $type === 'messageForAdmin' ) ? 'Информация от покупателя:' : 'Оставленная Вами информация:'; ?>
        </h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <?php foreach ( $fieldsCart as $key => $value ) { 
                
                if ( empty( $value['value'] ) ) {
                    continue;
                }
                
                $field_name  = isset( $value['name'] ) ? esc_html( $value['name'] ) : '';
                $field_value = esc_html( $value['value'] );
                ?>
                <tr>
                    <td style="padding: 5px 0; vertical-align: top;"><b><?php echo $field_name; ?>:</b></td>
                    <td style="padding: 5px 0;"><?php echo $field_value; ?></td>
                </tr>
            <?php } ?>
        </table>
        
    <?php } ?>
    
    <?php if ( ! empty( $registrationMessage ) && $type === 'messageForUser' ) { ?>
    
        <div style="background: #f5f5f5; padding: 15px; margin: 20px 0; border-left: 4px solid #0066cc;">
            <?php echo $registrationMessage; ?>
        </div>
        
    <?php } ?>
    
    <?php if ( $type === 'messageForUser' ) { ?>
    
        <p style="color: #666; margin-top: 30px;">
            Благодарим за ваш заказ! Наш менеджер свяжется с Вами в ближайшее время.
        </p>
        <p style="color: #666;">
            Для изменения деталей заказа свяжитесь с менеджером магазина по телефону, указанному на сайте.
        </p>
    
    <?php } ?>
    
    </div>
    </body>
    </html>
    <?php
    
    return ob_get_clean();
}

/**
 * ФУНКЦИЯ: Получение и валидация данных формы заказа
 * 
 * ГДЕ ТЕСТИРОВАТЬ: Автоматически при оформлении заказа
 * ЧТО ДЕЛАЕТ:
 * 1. Получает данные из формы заказа
 * 2. Проверяет обязательные поля
 * 3. Валидирует email и телефон
 * 4. Формирует массивы для отправки на почту и сохранения в БД
 * 
 * @param string $typeForm — тип формы: quick, person, legalPerson
 * @param array  $POST     — данные из $_POST
 * 
 * @return array Массив с полями формы и данными аккаунта
 */
function get_valueFieldsCart( $typeForm, $POST ) {
    
    $settingShop = get_option( 'settingShop' );
    
    $cartReg = ( ! empty( $POST['cartReg'] ) ) ? $POST['cartReg'] : 0;
    
    $required = array();
    
    if ( ! empty( $_POST['required'] ) ) {
        $required = stripslashes( $_POST['required'] );
        $required = json_decode( $required, true );
    }
    
    $requredClean = array();
    
    if ( $required && is_array( $required ) ) {
        
        foreach ( $required as $key => $value ) {
            $requredClean[ wp_strip_all_tags( $key ) ] = $value;
        }
    }
    
    $required = $requredClean;
    
    $fieldsCart = array();
    $accountData = array();
    
    /**
     * Внутренние функции для заполнения массивов с данными формы
     * $fieldsCart — отправка на почту
     * $accountData — данные пользователя, записываемые в базу при регистрации
     */
    
    // Передаем $get_valueByType параметры из внешней функции, $fieldsCart ссылкой для возможности его изменения
    $get_valueByType = function( $typeForm ) use ( $settingShop, $POST, &$required, &$accountData, &$fieldsCart ) {
        
        if ( ! isset( $settingShop[ $typeForm ] ) || ! is_array( $settingShop[ $typeForm ] ) ) {
            return;
        }
        
        foreach ( $settingShop[ $typeForm ] as $key => $value ) {
            
            $valueField = isset( $POST[ $key ] ) ? sanitize_text_field( stripslashes( $POST[ $key ] ) ) : '';
            
            if ( ! empty( $POST['cartReg'] ) ) {
                
                $typeData = '';
                
                if ( $typeForm === 'person' ) {
                    $typeData = 'accountDetail';
                } elseif ( $typeForm === 'legalPerson' ) {
                    $typeData = 'accountLegalDetail';
                }
                
                if ( ! empty( $typeData ) && empty( $accountData[ $typeData ][ $key ] ) ) {
                    $accountData[ $typeData ][ $key ] = $valueField;
                }
            }
            
            if ( ! empty( $valueField ) ) {
                
                $fieldsCart[ $key ] = array(
                    'name'  => ( ! empty( $value['name'] ) ) ? $value['name'] : '',
                    'value' => $valueField,
                );
                
            } elseif ( ! empty( $required[ $key ] ) && $required[ $key ] === true ) {
                
                $required[ $key ] = ( ! empty( $value['name'] ) ) ? $value['name'] : '';
            }
        }
    };
    
    $get_valueByFieldname = function( $fieldName, $nameInArray ) use ( $POST, &$required, &$fieldsCart ) {
        
        $fieldsCart[ $fieldName ] = array(
            'name'  => $nameInArray,
            'value' => ( ! empty( $POST[ $fieldName ] ) ) ? sanitize_text_field( stripslashes( $POST[ $fieldName ] ) ) : '',
        );
        
        // Заполняем имя для email на случай вывода ошибки о незаполненности поля
        if ( ! empty( $required[ $fieldName ] ) && $required[ $fieldName ] === true ) {
            $required[ $fieldName ] = $nameInArray;
        }
    };
    
    /**
     * Формируем массив с данными для отправки на почту в зависимости от типа формы
     */
    
    if ( $typeForm !== 'legalPerson' ) {
        
        $get_valueByType( $typeForm );
        
        if ( $typeForm !== 'quick' ) {
            $get_valueByFieldname( 'emailUser', 'Email' );
        }
        
    } else {
        
        $get_valueByType( 'person' );
        $get_valueByFieldname( 'emailUser', 'Email' );
        $get_valueByType( $typeForm );
    }
    
    $get_valueByFieldname( 'messageUser', 'Сообщение' );
    
    /**
     * Перепроверка заполненности полей
     */
    
    // Отдельно обрабатываем логин
    if ( ! empty( $POST['cartReg'] ) ) {
        
        if ( empty( $POST['loginUser'] ) ) {
            $required['loginUser'] = 'Логин';
        } else {
            unset( $required['loginUser'] );
        }
    }
    
    $blankRequired = array_diff_key( $required, $fieldsCart );
    
    if ( $blankRequired ) {
        
        $result = array(
            'type'     => 'error',
            'typeForm' => $typeForm,
            'message'  => 'Заполните поля: ',
        );
        
        foreach ( $blankRequired as $key => $value ) {
            $result['message'] .= $value . ', ';
        }
        
        $result['message'] = rtrim( $result['message'], ', ' );
        
        echo json_encode( $result );
        wp_die();
    }
    
    /**
     * Проверка правильности заполнения
     */
    
    if ( ! empty( $fieldsCart['emailUser']['value'] ) ) {
        
        $user_mail = $fieldsCart['emailUser']['value'];
        $correctEmail = filter_var( $user_mail, FILTER_VALIDATE_EMAIL );
        
        if ( ! $correctEmail ) {
            
            $result = array(
                'type'     => 'error',
                'typeForm' => $typeForm,
                'message'  => 'Email введен не верно',
            );
            
            echo json_encode( $result );
            wp_die();
        }
    }
    
    if ( ! empty( $fieldsCart['telefonUser']['value'] ) ) {
        
        $user_phone = $fieldsCart['telefonUser']['value'];
        
        $result = array(
            'type'     => 'error',
            'typeForm' => $typeForm,
        );
        
        if ( countDigits( $user_phone ) < 11 ) {
            
            $result['message'] = 'Телефон содержит менее 11 символов';
            
            echo json_encode( $result );
            wp_die();
        }
    }
    
    /**
     * Дополнительная обработка, сборка ответа
     */
    
    foreach ( $fieldsCart as $fieldsType => $fieldsTypeValue ) {
        
        foreach ( $fieldsCart[ $fieldsType ] as $key => $value ) {
            
            // Меняем кавычки и другие символы
            $fieldsCart[ $fieldsType ][ $key ] = wptexturize( $value );
        }
    }
    
    $fieldsArrays = array(
        'fieldsCart'  => $fieldsCart,
        'accountData' => array(),
    );
    
    if ( $cartReg ) {
        
        foreach ( $accountData as $fieldsType => $fieldsTypeValue ) {
            
            foreach ( $accountData[ $fieldsType ] as $key => $value ) {
                
                // Меняем кавычки и другие символы
                $accountData[ $fieldsType ][ $key ] = wptexturize( $value );
            }
        }
        
        $fieldsArrays['accountData'] = $accountData;
    }
    
    return $fieldsArrays;
}