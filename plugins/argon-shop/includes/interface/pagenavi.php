<?php
/**
 * Пагинация для WordPress (аналог wp_pagenavi)
 *
 * Оригинальный автор: Тимур Камаев (Kama)
 * Источник: http://wp-kama.ru/?p=8
 * Оригинальная версия: 2.5.1
 *
 * @package Argon_Shop
 * @author  Тимур Камаев (Kama) — оригинальный автор
 * @author  Иван Войтков (Ivan Voitkov) — правки безопасности и совместимости с PHP 8.5
 * @version 2.5.2-argon
 * 
 * ИЗМЕНЕНИЯ ОТ ARGON SHOP:
 * 1. Добавлена защита от прямого вызова файла (ABSPATH)
 * 2. Заменены нестрогие сравнения (== / !=) на строгие (=== / !==)
 *     в критичных местах проверки номера страницы
 * 3. Добавлены явные проверки типов и значений:
 *     - $wp_query является объектом WP_Query
 *     - $max_page > 0
 *     - $pages_to_show > 0 (защита от деления на 0)
 * 4. Устранены Deprecated-предупреждения PHP 8.1+:
 *     - неявное преобразование float в int
 *     - обращение к свойствам null
 * 5. Совместимость с PHP 8.5:
 *     - явные приведения типов
 *     - безопасные математические операции
 *
 * ПАГИНАЦИЯ
 * 
 * Альтернатива wp_pagenavi. Создает ссылки пагинации на страницах архивов.
 *
 * @param string   $before   — текст до навигации
 * @param string   $after    — текст после навигации
 * @param bool     $echo     — возвращать или выводить результат
 * @param array    $args     — аргументы функции
 * @param WP_Query $wp_query — объект WP_Query на основе которого строится пагинация.
 *                             По умолчанию глобальная переменная $wp_query
 * 
 * @return string|void HTML-код пагинации или ничего (если $echo = true)
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function kama_pagenavi( $before = '', $after = '', $echo = true, $args = array(), $wp_query = null ) {
    
    // Получаем глобальный WP_Query
    if ( ! $wp_query ) {
        wp_reset_query();
        global $wp_query;
    }
    
    // PHP 8.5: проверяем, что $wp_query — объект WP_Query
    if ( ! $wp_query instanceof WP_Query ) {
        return false;
    }

    // Параметры по умолчанию
    $default_args = array(
        'text_num_page'   => '',
        'num_pages'       => 3,
        'step_link'       => 0,
        'dotright_text'   => '…',
        'dotright_text2'  => '…',
        'back_text'       => '« ',
        'next_text'       => ' »',
        'first_page_text' => 0,
        'last_page_text'  => 0,
    );

    $default_args = apply_filters( 'kama_pagenavi_args', $default_args );

    $args = array_merge( $default_args, $args );

    // PHP 8.5: заменяем extract() на явное извлечение для безопасности
    $text_num_page   = $args['text_num_page'];
    $num_pages       = $args['num_pages'];
    $step_link       = $args['step_link'];
    $dotright_text   = $args['dotright_text'];
    $dotright_text2  = $args['dotright_text2'];
    $back_text       = $args['back_text'];
    $next_text       = $args['next_text'];
    $first_page_text = $args['first_page_text'];
    $last_page_text  = $args['last_page_text'];

    // PHP 8.5: явное приведение типов
    $paged    = (int) $wp_query->get( 'paged' );
    $max_page = (int) $wp_query->max_num_pages;

    // Проверка на надобность в навигации
    if ( $max_page <= 1 ) {
        return false;
    }

    if ( $paged === 0 ) {
        $paged = 1;
    }

    $pages_to_show = intval( $num_pages );
    
    // PHP 8.5: защита от некорректного значения
    if ( $pages_to_show < 1 ) {
        $pages_to_show = 3;
    }
    
    $pages_to_show_minus_1 = $pages_to_show - 1;

    // PHP 8.5: явные приведения к int
    $half_page_start = (int) floor( $pages_to_show_minus_1 / 2 );
    $half_page_end   = (int) ceil( $pages_to_show_minus_1 / 2 );

    $start_page = $paged - $half_page_start;
    $end_page   = $paged + $half_page_end;

    if ( $start_page <= 0 ) {
        $start_page = 1;
    }
    
    if ( ( $end_page - $start_page ) !== $pages_to_show_minus_1 ) {
        $end_page = $start_page + $pages_to_show_minus_1;
    }
    
    if ( $end_page > $max_page ) {
        $start_page = $max_page - $pages_to_show_minus_1;
        $end_page   = $max_page;
    }

    if ( $start_page <= 0 ) {
        $start_page = 1;
    }

    // Выводим навигацию
    $out = '';

    // Создаём базу, чтобы вызвать get_pagenum_link один раз
    $link_base = str_replace( 99999999, '___', get_pagenum_link( 99999999 ) );
    $first_url = get_pagenum_link( 1 );
    
    if ( false === strpos( $first_url, '?' ) ) {
        $first_url = user_trailingslashit( $first_url );
    }

    $out .= $before . "<div class='wp-pagenavi'>\n";

        // Текст перед пагинацией
        if ( $text_num_page ) {
            $text_num_page = preg_replace( '!{current}|{last}!', '%s', $text_num_page );
            $out .= sprintf( "<span class='pages'>{$text_num_page}</span> ", $paged, $max_page );
        }
        
        // Назад
        if ( $back_text && $paged !== 1 ) {
            $prev_link = ( ( $paged - 1 ) === 1 ) ? $first_url : str_replace( '___', ( $paged - 1 ), $link_base );
            $out .= '<a class="prev" href="' . $prev_link . '">' . $back_text . '</a> ';
        }
        
        // В начало
        if ( $start_page >= 2 && $pages_to_show < $max_page ) {
            $first_text = $first_page_text ? $first_page_text : 1;
            $out .= '<a class="first" href="' . $first_url . '">' . $first_text . '</a> ';
            
            if ( $dotright_text && $start_page !== 2 ) {
                $out .= '<span class="extend">' . $dotright_text . '</span> ';
            }
        }
        
        // Пагинация
        for ( $i = $start_page; $i <= $end_page; $i++ ) {
            if ( $i === $paged ) {
                $out .= '<span class="current">' . $i . '</span> ';
            } elseif ( $i === 1 ) {
                $out .= '<a href="' . $first_url . '">1</a> ';
            } else {
                $out .= '<a href="' . str_replace( '___', $i, $link_base ) . '">' . $i . '</a> ';
            }
        }

        // Ссылки с шагом
        $dd = 0;
        if ( $step_link && $end_page < $max_page ) {
            for ( $i = $end_page + 1; $i <= $max_page; $i++ ) {
                if ( $i % $step_link === 0 && $i !== $num_pages ) {
                    if ( ++$dd === 1 ) {
                        $out .= '<span class="extend">' . $dotright_text2 . '</span> ';
                    }
                    $out .= '<a href="' . str_replace( '___', $i, $link_base ) . '">' . $i . '</a> ';
                }
            }
        }
        
        // В конец
        if ( $end_page < $max_page ) {
            if ( $dotright_text && $end_page !== ( $max_page - 1 ) ) {
                $out .= '<span class="extend">' . $dotright_text2 . '</span> ';
            }
            $last_text = $last_page_text ? $last_page_text : $max_page;
            $out .= '<a class="last" href="' . str_replace( '___', $max_page, $link_base ) . '">' . $last_text . '</a> ';
        }
        
        // Вперёд
        if ( $next_text && $paged !== $end_page ) {
            $out .= '<a class="next" href="' . str_replace( '___', ( $paged + 1 ), $link_base ) . '">' . $next_text . '</a> ';
        }

    $out .= "</div>" . $after . "\n";

    $out = apply_filters( 'kama_pagenavi', $out );

    if ( $echo ) {
        echo $out;
        return;
    }

    return $out;
}

/**
 * История изменений:
 * 2.5 - 2.5.1 - автоматический сброс основного запроса.
 * 
 * Argon Shop (2.5.2-argon):
 * - Добавлена защита ABSPATH
 * - Убрана зависимость от extract()
 * - Строгие сравнения (=== / !==) вместо нестрогих
 * - Проверки типов и защита от деления на ноль
 * - Совместимость с PHP 8.5
 */