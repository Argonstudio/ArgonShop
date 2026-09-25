<?php

/*
 * CHANGES FROM ARGON SHOP:
 * 
 * 1. Added a separate view counter (WP-LastViewedPosts1)
 * 2. Added price and "hit" label output in the product block
 * 3. Changed output structure for responsive layout
 * 4. Added direct file access protection (ABSPATH)
 * 5. FIXED CRITICAL ERROR WITH PHP 8.5:
 *     - Removed /e modifier in preg_replace() — removed since PHP 7.0
 *     - Will cause Fatal Error on modern PHP when accessed
 * 6. Replaced unsafe unserialize() without restrictions with safe
 *     version using allowed_classes => false + array validation
 * 7. Fixed typo: is_singular( product ) → is_singular( 'product' )
 * 8. Fixed call to zg_recently_viewed() — default parameters passed
 * 9. Removed extract() function — unsafe and outdated
 * 10. Removed debug output (echo, print_r) in production
 * 11. Added isset() checks for all $_POST, $_COOKIE, $meta_data
 * 12. Replaced outdated functions: attribute_escape → esc_attr
 * 13. Fixed Deprecated warnings in PHP 8.1+:
 *     - setcookie() with 7 arguments (outdated)
 *     - $value+0 (implicit conversion)
 *
 * @package Argon_Shop
 * @author  Olaf Baumann (original author)
 * @author  Syed Balkhi (fork author)
 * @author  Ivan Voitkov (customization for ArgonShop)
 * @version 0.7.3-argon
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

Use:
For the ouput use the sidebar widget OR place following code just anywhere (outside the loop) into your theme (e.g. sidebar.php).
Note that the output will not appear if there's no cookie set (because cookies are disabled or the user didn't view any single post).
-------------------------------------------
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/* Here are some parameters you may want to change: */
$zg_cookie_expire = 360; // After how many days should the cookie expire? Default is 360.
$zg_number_of_posts = 15; // How many posts should be displayed in the list? Default is 10.
$zg_recognize_pages = true; // Should pages to be recognized and listed? Default is true.

/* Do not edit after this line! */

/**
 * ФУНКЦИЯ: Хук на каждом сгенерированном посте
 * 
 * ГДЕ: Запускается через get_header на странице товара
 * ЧТО ДЕЛАЕТ:
 * 1. Увеличивает счётчик просмотров в куках (WP-LastViewedPosts1)
 * 2. Добавляет ID товара в список просмотренных (WP-LastViewedPosts)
 */
function zg_lwp_header() {
    
    // PHP 8.5: is_singular( product ) — ошибка, должно быть 'product' (строкой)
    if ( is_singular( 'product' ) ) {
        
        $lastViewedPosts = array();
        
        if ( getCookie( 'WP-LastViewedPosts1' ) ) {
            
            $lastViewedPosts = getCookie( 'WP-LastViewedPosts1' );
            
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
        
        zg_lw_setcookie();
    }
}

/**
 * ФУНКЦИЯ: Сохранение просмотренного товара в куки
 * 
 * ГДЕ: Автоматически вызывается из zg_lwp_header()
 * ЧТО ДЕЛАЕТ:
 * 1. Читает текущий список из куки WP-LastViewedPosts
 * 2. Добавляет новый ID в начало
 * 3. Ограничивает список заданным количеством
 * 4. Сохраняет обратно в куки
 * 
 * PHP 8.5: заменён устаревший preg_replace() с /e (удалён с PHP 7.0)
 */
function zg_lw_setcookie() {
    
    global $wp_query;
    
    // PHP 8.5: проверяем, что объект поста существует
    if ( ! isset( $wp_query->post->ID ) ) {
        return;
    }
    
    $zg_post_ID = absint( $wp_query->post->ID );
    
    if ( $zg_post_ID === 0 ) {
        return;
    }
    
    // Читаем куки и десериализуем безопасно
    $zg_cookiearray = array();
    
    if ( isset( $_COOKIE['WP-LastViewedPosts'] ) ) {
        
        $cookie_raw = stripslashes( $_COOKIE['WP-LastViewedPosts'] );
        
        // PHP 8.5: заменён preg_replace(/e) на безопасную десериализацию
        // Ограничение размера для защиты от DoS
        if ( strlen( $cookie_raw ) <= 10000 ) {
            
            $unserialized = @unserialize( $cookie_raw, array( 'allowed_classes' => false ) );
            
            if ( is_array( $unserialized ) ) {
                // Валидация — только целые числа
                $zg_cookiearray = array_map( 'absint', $unserialized );
                $zg_cookiearray = array_filter( $zg_cookiearray );
                $zg_cookiearray = array_values( $zg_cookiearray );
            }
        }
    }
    
    // Если массив пустой или повреждён — создаём новый
    if ( ! is_array( $zg_cookiearray ) || empty( $zg_cookiearray ) ) {
        $zg_cookiearray = array( $zg_post_ID );
    }
    
    // Если товар уже в списке — удаляем его
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
    
    // Определяем домен для куки
    $zg_blog_url_array = parse_url( get_bloginfo( 'url' ) );
    
    if ( ! is_array( $zg_blog_url_array ) ) {
        return;
    }
    
    $zg_blog_url = isset( $zg_blog_url_array['host'] ) ? $zg_blog_url_array['host'] : '';
    $zg_blog_url = str_replace( 'www.', '', $zg_blog_url );
    $zg_blog_url_dot = '.' . $zg_blog_url;
    
    $zg_path_url = isset( $zg_blog_url_array['path'] ) ? $zg_blog_url_array['path'] : '';
    $zg_path_url .= '/';
    
    // Счётчик (для совместимости с Argon-доработкой ZBK)
    $lastViewedPosts = array();
    
    if ( getCookie( 'WP-LastViewedPosts1' ) ) {
        $lastViewedPosts = getCookie( 'WP-LastViewedPosts1' );
        
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
 * ФУНКЦИЯ: Вывод списка просмотренных товаров
 * 
 * ГДЕ: Вызывается в шаблоне темы или виджете
 * ЧТО ДЕЛАЕТ:
 * 1. Читает куку WP-LastViewedPosts
 * 2. Выводит карточки товаров в виде списка
 * 
 * @param int  $rightAmount — максимальное количество товаров для вывода
 * @param bool $sidebarOn   — вывод в сайдбаре (другая сетка)
 */
function zg_recently_viewed( $rightAmount = 15, $sidebarOn = true ) {
    
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
                
                // PHP 8.5: проверяем, что это массив и ключ существует
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
                        <span class="goods-label goods-label-hit">хит</span>
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
                    <span class="summ"><?php echo esc_html( $price ); ?> руб</span>
                </div>
                
                <div class="link">
                    <a href="<?php echo esc_url( get_permalink( $value ) ); ?>" rel="bookmark"> Подробнее </a>
                </div>
                
            </div> 
            
            </div>
            
            <?php
        }
    }
    
    echo "</div>";
}

/**
 * ФУНКЦИЯ: Вывод виджета "Просмотренные товары"
 * 
 * ГДЕ: Виджет в сайдбаре
 * 
 * PHP 8.5: убран extract(), добавлены проверки
 */
function zg_lwp_widget( $args ) {
    
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
        
        zg_recently_viewed( 15, true );
        
        echo $after_widget;
    }
}

/**
 * ФУНКЦИЯ: Управление виджетом в админке
 * 
 * ГДЕ: Страница виджетов в админке
 */
function zg_lwp_widget_control() {
    
    $options = $newoptions = get_option( 'zg_lwp_widget' );
    
    if ( ! is_array( $options ) ) {
        $options = array();
    }
    
    if ( ! is_array( $newoptions ) ) {
        $newoptions = array();
    }
    
    // PHP 8.5: проверяем isset перед доступом к $_POST
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
    
    // PHP 8.5: attribute_escape() удалён с WP 2.8
    $title = isset( $options['title'] ) ? esc_attr( $options['title'] ) : '';
    
    ?>
    <p>
        <label for="lwp-title">
            <?php esc_html_e( 'Title:' ); ?>
            <input type="text" style="width:250px" id="lwp-title" name="lwp-title" value="<?php echo $title; ?>" />
        </label>
    </p>
    <input type="hidden" name="lwp-submit" id="lwp-submit" value="1" />
    <?php
}

/**
 * ФУНКЦИЯ: Инициализация виджета
 * 
 * ГДЕ: Хук widgets_init
 */
function zg_lwp_init() {
    
    if ( ! function_exists( 'register_sidebar_widget' ) ) {
        return;
    }
    
    register_sidebar_widget( 'Last Viewed Posts', 'zg_lwp_widget' );
    register_widget_control( 'Last Viewed Posts', 'zg_lwp_widget_control', 250, 100 );
}

add_action( 'get_header', 'zg_lwp_header' );
add_action( 'widgets_init', 'zg_lwp_init' );