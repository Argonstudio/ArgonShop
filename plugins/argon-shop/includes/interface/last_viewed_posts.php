<?php

/*
 * ИЗМЕНЕНИЯ ARGON SHOP:
 * 
 * 1. Добавлен отдельный счётчик просмотров (WP-LastViewedPosts1)
 * 2. Добавлен вывод цены и метки «хит» в блоке товара
 * 3. Изменена структура вывода для адаптивной вёрстки
 * 4. Добавлена защита от прямого доступа к файлу (ABSPATH)
 * 5. ИСПРАВЛЕНА КРИТИЧЕСКАЯ ОШИБКА С PHP 8.5:
 *     - Убран модификатор /e в preg_replace() — удалён с PHP 7.0
 *     - Вызвал бы Fatal Error на современном PHP при обращении
 * 6. Заменён небезопасный unserialize() без ограничений на безопасный
 *     вариант с allowed_classes => false + проверкой массива
 * 7. Исправлена опечатка: is_singular( product ) → is_singular( 'product' )
 * 8. Исправлен вызов as_zg_recently_viewed() — передаются параметры по умолчанию
 * 9. Убрана функция extract() — небезопасна и устарела
 * 10. Убран отладочный вывод (echo, print_r) в проде
 * 11. Добавлены проверки isset() для всех $_POST, $_COOKIE, $meta_data
 * 12. Заменены устаревшие функции: attribute_escape → esc_attr
 * 13. Исправлены предупреждения Deprecated в PHP 8.1+:
 *     - setcookie() с 7 аргументами (устарело)
 *     - $value+0 (неявное преобразование)
 *
 * @package Argon_Shop
 * @author  Olaf Baumann (original author)
 * @author  Syed Balkhi (fork author)
 * @author  Ivan Voitkov (customization for ArgonShop)
 * Основано на: Last Viewed Posts 0.7.3 (Olaf Baumann). Версия этой копии = версия плагина.
 */

/* Copyright 2007 Olaf Baumann  (http://zeitgrund.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Использование:
Для вывода используйте виджет сайдбара ИЛИ разместите следующий код в любом месте темы (вне цикла), например в sidebar.php.
Обратите внимание: вывод не появится, если cookie не установлен (cookie отключены или пользователь не просматривал ни одной записи).
-------------------------------------------
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/* Вот некоторые параметры, которые вы можете изменить: */
$zg_cookie_expire = 360; // Через сколько дней истекает cookie? По умолчанию 360.
$zg_number_of_posts = 15; // Сколько записей показывать в списке? По умолчанию 10.
$zg_recognize_pages = true; // Следует ли распознавать и выводить страницы? По умолчанию true.

/* Не редактируйте после этой строки! */

/**
 * ФУНКЦИЯ: хук на каждую выводимую запись
 * 
 * ГДЕ: выполняется через get_header на странице товара
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Увеличивает счётчик просмотров в cookie (WP-LastViewedPosts1)
 * 2. Добавляет ID товара в список просмотренных (WP-LastViewedPosts)
 */
function as_zg_lwp_header() {
    
    // PHP 8.5: is_singular( product ) — ошибка, должно быть 'product' (строка)
    if ( is_singular( 'product' ) ) {
        
        $lastViewedPosts = array();
        
        if ( as_getCookie( 'WP-LastViewedPosts1' ) ) {
            
            $lastViewedPosts = as_getCookie( 'WP-LastViewedPosts1' );
            
            if ( ! is_array( $lastViewedPosts ) ) {
                $lastViewedPosts = array();
            }
            
            $lastViewedPosts['counter'] = isset( $lastViewedPosts['counter'] ) 
                ? intval( $lastViewedPosts['counter'] ) + 1 
                : 1;
                
        } else {
            $lastViewedPosts['counter'] = 1;
        }
        
        $lastViewedPosts = wp_json_encode( $lastViewedPosts );
        
        setcookie(
            'WP-LastViewedPosts1',
            $lastViewedPosts,
            time() + 1209600,
            COOKIEPATH,
            COOKIE_DOMAIN
        );
        
        as_zg_lw_setcookie();
    }
}

/**
 * ФУНКЦИЯ: сохраняет просмотренный товар в cookie
 * 
 * ГДЕ: вызывается автоматически из as_zg_lwp_header()
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Читает текущий список из cookie WP-LastViewedPosts
 * 2. Добавляет новый ID в начало
 * 3. Ограничивает список настроенным количеством
 * 4. Сохраняет обратно в cookie
 * 
 * PHP 8.5: заменён устаревший preg_replace() с /e (удалён в PHP 7.0)
 */
function as_zg_lw_setcookie() {
    
    global $wp_query;
    
    // PHP 8.5: убеждаемся, что объект записи существует
    if ( ! isset( $wp_query->post->ID ) ) {
        return;
    }
    
    $zg_post_ID = absint( $wp_query->post->ID );
    
    if ( $zg_post_ID === 0 ) {
        return;
    }
    
    // Читаем cookie и безопасно десериализуем его
    $zg_cookiearray = array();
    
    if ( isset( $_COOKIE['WP-LastViewedPosts'] ) ) {
        
        $cookie_raw = stripslashes( $_COOKIE['WP-LastViewedPosts'] );
        
        // PHP 8.5: заменён preg_replace(/e) на безопасную десериализацию
        // Ограничение размера (защита от DoS)
        if ( strlen( $cookie_raw ) <= 10000 ) {
            
            $unserialized = @unserialize( $cookie_raw, array( 'allowed_classes' => false ) );
            
            if ( is_array( $unserialized ) ) {
                // Проверка — только целые числа
                $zg_cookiearray = array_map( 'absint', $unserialized );
                $zg_cookiearray = array_filter( $zg_cookiearray );
                $zg_cookiearray = array_values( $zg_cookiearray );
            }
        }
    }
    
    // Если массив пуст или повреждён — создаём новый
    if ( ! is_array( $zg_cookiearray ) || empty( $zg_cookiearray ) ) {
        $zg_cookiearray = array( $zg_post_ID );
    }
    
    // Если товар уже в списке — убираем его
    if ( in_array( $zg_post_ID, $zg_cookiearray, true ) ) {
        
        $zg_key = array_search( $zg_post_ID, $zg_cookiearray, true );
        
        if ( $zg_key !== false ) {
            array_splice( $zg_cookiearray, $zg_key, 1 );
        }
    }
    
    // Добавляем новый ID в начало
    array_unshift( $zg_cookiearray, $zg_post_ID );
    
    // Ограничиваем количество
    global $zg_number_of_posts;
    
    $zg_number_of_posts = absint( $zg_number_of_posts );
    
    if ( $zg_number_of_posts < 1 ) {
        $zg_number_of_posts = 15;
    }
    
    while ( count( $zg_cookiearray ) > $zg_number_of_posts ) {
        array_pop( $zg_cookiearray );
    }
    
    // Определяем домен cookie
    $zg_blog_url_array = parse_url( get_bloginfo( 'url' ) );
    
    if ( ! is_array( $zg_blog_url_array ) ) {
        return;
    }
    
    $zg_blog_url = isset( $zg_blog_url_array['host'] ) ? $zg_blog_url_array['host'] : '';
    $zg_blog_url = str_replace( 'www.', '', $zg_blog_url );
    $zg_blog_url_dot = '.' . $zg_blog_url;
    
    $zg_path_url = isset( $zg_blog_url_array['path'] ) ? $zg_blog_url_array['path'] : '';
    $zg_path_url .= '/';
    
    // Счётчик (для совместимости с модификацией Argon ZBK)
    $lastViewedPosts = array();
    
    if ( as_getCookie( 'WP-LastViewedPosts1' ) ) {
        $lastViewedPosts = as_getCookie( 'WP-LastViewedPosts1' );
        
        if ( ! is_array( $lastViewedPosts ) ) {
            $lastViewedPosts = array();
        }
    }
    
    array_unshift( $lastViewedPosts, $zg_post_ID );
    
    // PHP 8.5: setcookie() с 7 аргументами устарел, используем массив опций
    setcookie(
        'WP-LastViewedPosts',
        serialize( $zg_cookiearray ),
        array(
            'expires'  => time() + ( absint( $zg_cookie_expire ) * 86400 ),
            'path'     => $zg_path_url,
            'domain'   => $zg_blog_url_dot,
            'secure'   => is_ssl(),
            'httponly' => false, // Должно быть false — читается из JS
            'samesite' => 'Lax',
        )
    );
}

/**
 * ФУНКЦИЯ: выводит список просмотренных товаров
 * 
 * ГДЕ: вызывается в шаблоне темы или виджете
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Читает cookie WP-LastViewedPosts
 * 2. Выводит карточки товаров списком
 * 
 * @param int  $rightAmount — максимальное количество выводимых товаров
 * @param bool $sidebarOn   — вывод в сайдбаре (другая сетка)
 */
function as_zg_recently_viewed( $rightAmount = 15, $sidebarOn = true ) {
    
    $rightAmount = absint( $rightAmount );
    
    if ( $rightAmount < 1 ) {
        $rightAmount = 15;
    }
    
    echo "<div class='list row'>";
    
    if ( isset( $_COOKIE['WP-LastViewedPosts'] ) ) {
        
        $cookie_raw = stripslashes( $_COOKIE['WP-LastViewedPosts'] );
        
        $zg_post_IDs = array();
        
        // PHP 8.5: безопасная десериализация вместо preg_replace(/e)
        if ( strlen( $cookie_raw ) <= 10000 ) {
            
            $unserialized = @unserialize( $cookie_raw, array( 'allowed_classes' => false ) );
            
            if ( is_array( $unserialized ) ) {
                $zg_post_IDs = array_map( 'absint', $unserialized );
                $zg_post_IDs = array_filter( $zg_post_IDs );
                $zg_post_IDs = array_values( $zg_post_IDs );
            }
        }
        
        $counter = 0;
        
        foreach ( $zg_post_IDs as $value ) {
            
            if ( $counter >= $rightAmount ) {
                break;
            }
            
            $counter++;
            
            $value = absint( $value );
            
            if ( $value === 0 ) {
                continue;
            }
            
            // Цена товара
            $price = '?';
            
            $price_meta = get_post_meta( $value, '_price', true );
            
            if ( $price_meta ) {
                
                $price = $price_meta;
                
            } else {
                
                $meta_data = get_post_meta( $value, 'product_custom_options', true );
                
                // PHP 8.5: убеждаемся, что это массив и ключ существует
                if ( is_array( $meta_data ) && isset( $meta_data['price'] ) ) {
                    $price = $meta_data['price'];
                }
            }
            
            if ( $sidebarOn ) {
                echo "<div class='column small-12 medium-6 large-12'>";
            } else {
                echo "<div class='column small-12 medium-4 large-4'>";
            }
            
            ?>
            
            <div class='list_row'>
                
                <div class="image">
                    
                    <?php 
                    // PHP 8.5: get_field может вернуть null/false
                    if ( function_exists( 'get_field' ) && get_field( '_hit', $value ) ) { 
                        ?>
                        <span class="goods-label goods-label-hit"><?php esc_html_e( 'hit', 'argon-shop' ); ?></span>
                        <?php 
                    } 
                    ?>
                    
                    <a href="<?php echo esc_url( get_permalink( $value ) ); ?>">
                        <?php 
                        // PHP 8.5: get_the_post_thumbnail возвращает строку или false
                        echo get_the_post_thumbnail( 
                            $value, 
                            'blog_thumb', 
                            array( 'class' => 'img-responsive' ) 
                        ); 
                        ?>
                    </a>
                    
                </div>
                
                <div class="name"><?php echo esc_html( get_the_title( $value ) ); ?></div>
                
                <div class="price">
                    <span class="summ"><?php echo as_price_html( esc_html( $price ) ); ?></span>
                </div>
                
                <div class="link">
                    <a href="<?php echo esc_url( get_permalink( $value ) ); ?>" rel="bookmark"> <?php esc_html_e( 'Details', 'argon-shop' ); ?> </a>
                </div>
                
            </div> 
            
            </div>
            
            <?php
        }
    }
    
    echo "</div>";
}

/**
 * ФУНКЦИЯ: выводит виджет «Просмотренные товары»
 * 
 * ГДЕ: виджет сайдбара
 * 
 * PHP 8.5: extract() убран, добавлены проверки
 */
function as_zg_lwp_widget( $args ) {
    
    // PHP 8.5: extract($args) небезопасен и устарел
    $before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '';
    $after_widget  = isset( $args['after_widget'] )  ? $args['after_widget']  : '';
    $before_title  = isset( $args['before_title'] )  ? $args['before_title']  : '';
    $after_title   = isset( $args['after_title'] )   ? $args['after_title']   : '';
    
    $options = get_option( 'zg_lwp_widget' );
    
    if ( ! is_array( $options ) ) {
        $options = array();
    }
    
    $title = isset( $options['title'] ) ? $options['title'] : 'Last viewed posts';
    $title = esc_html( $title );
    
    if ( isset( $_COOKIE['WP-LastViewedPosts'] ) ) {
        
        echo $before_widget . $before_title . $title . $after_title;
        
        as_zg_recently_viewed( 15, true );
        
        echo $after_widget;
    }
}

/**
 * ФУНКЦИЯ: управление виджетом в админке
 * 
 * ГДЕ: страница виджетов в админке
 */
function as_zg_lwp_widget_control() {
    
    $options = $newoptions = get_option( 'zg_lwp_widget' );
    
    if ( ! is_array( $options ) ) {
        $options = array();
    }
    
    if ( ! is_array( $newoptions ) ) {
        $newoptions = array();
    }
    
    // PHP 8.5: проверяем isset перед обращением к $_POST
    if ( isset( $_POST['lwp-submit'] ) ) {
        
        $title = isset( $_POST['lwp-title'] ) 
            ? sanitize_text_field( wp_unslash( $_POST['lwp-title'] ) ) 
            : '';
        
        $newoptions['title'] = $title;
    }
    
    if ( $options !== $newoptions ) {
        $options = $newoptions;
        update_option( 'zg_lwp_widget', $options );
    }
    
    // PHP 8.5: attribute_escape() удалена в WP 2.8
    $title = isset( $options['title'] ) ? esc_attr( $options['title'] ) : '';
    
    ?>
    <p>
        <label for="lwp-title">
            <?php esc_html_e( 'Title:', 'argon-shop' ); ?>
            <input type="text" style="width:250px" id="lwp-title" name="lwp-title" value="<?php echo $title; ?>" />
        </label>
    </p>
    <input type="hidden" name="lwp-submit" id="lwp-submit" value="1" />
    <?php
}

/**
 * ФУНКЦИЯ: инициализация виджета
 * 
 * ГДЕ: хук widgets_init
 */
function as_zg_lwp_init() {
    
    if ( ! function_exists( 'register_sidebar_widget' ) ) {
        return;
    }
    
    register_sidebar_widget( 'Last Viewed Posts', 'as_zg_lwp_widget' );
    register_widget_control( 'Last Viewed Posts', 'as_zg_lwp_widget_control', 250, 100 );
}

add_action( 'get_header', 'as_zg_lwp_header' );
add_action( 'widgets_init', 'as_zg_lwp_init' );