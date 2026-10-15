<?php
/**
 * Языковые правила поиска: исправление раскладки и нормализация слов.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 * @since   1.1.0
 *
 * ГДЕ ТЕСТИРОВАТЬ:
 * 1. Поле поиска в шапке (AJAX-поиск)
 * 2. Страница поиска /?s=
 * 3. Админка → Заказы → редактирование заказа → поле «Добавить товар»
 *
 * Правила зависят от языка сайта (Настройки → Общие → Язык сайта):
 *
 * ru — кириллический магазин (поведение версий до 1.1.0, без изменений):
 *      - текст, набранный в английской раскладке QWERTY, преобразуется в ЙЦУКЕН
 *        («ghjlern» → «продукт»);
 *      - слова разбиваются только по пробелам.
 *
 * en — латинский магазин:
 *      - текст, по ошибке набранный в русской раскладке, преобразуется в QWERTY
 *        («ыскуц» → «screw»);
 *      - типографские кавычки и апострофы нормализуются, знаки препинания вокруг слов удаляются;
 *      - стоп-слова (the, of, for, and…) игнорируются;
 *      - притяжательное 's и окончания множественного числа удаляются (screws → screw, boxes → box, batteries → battery).
 *
 * fr — латинский магазин с клавиатурой AZERTY:
 *      - текст, набранный в русской раскладке на клавиатуре AZERTY, преобразуется
 *        в AZERTY («срйшыу» → «chaise»);
 *      - текст, набранный в QWERTY на клавиатуре AZERTY (или наоборот), исправляется
 *        заменой a↔q, z↔w и ;→m («chqise» → «chaise»);
 *      - элизии удаляются (l'huile → huile, d'eau → eau), стоп-слова
 *        (le, la, les, de, des, du, et, pour…) игнорируются;
 *      - окончания множественного числа -s / -x удаляются (chaises → chaise);
 *      - œ / æ соответствуют oe / ae, а апострофы ' / ’ соответствуют друг другу.
 *
 * Другие локали используют правила «en». Акценты (é = e) сравниваются
 * сортировкой базы данных: стандартная collation WordPress utf8mb4_unicode(_520)_ci их игнорирует.
 *
 * ХУКИ:
 * - filter argon_shop_search_language ( string $lang, string $locale ) — 'ru' | 'en' | 'fr'
 * - filter argon_shop_search_stop_words ( array $words, string $lang )
 * - filter argon_shop_search_layout_variants ( array $variants, string $searchStr, string $lang )
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: язык поиска магазина
 *
 * Определяется локалью сайта (языком контента), а не языком администратора.
 *
 * @return string 'ru', 'en' или 'fr'
 */
function as_search_language() {

    $locale = function_exists( 'get_locale' ) ? (string) get_locale() : 'en_US';
    $lang   = strtolower( substr( $locale, 0, 2 ) );

    if ( ! in_array( $lang, array( 'ru', 'en', 'fr' ), true ) ) {
        $lang = 'en';
    }

    $lang = apply_filters( 'argon_shop_search_language', $lang, $locale );

    return in_array( $lang, array( 'ru', 'en', 'fr' ), true ) ? $lang : 'en';
}

/**
 * ФУНКЦИЯ: карты раскладок клавиатуры
 *
 * Каждая карта — это «набранный символ → ожидаемый символ» для одной физической клавиши.
 *
 * @param string $name — 'qwerty_to_ru', 'ru_to_qwerty', 'ru_to_azerty', 'qwerty_azerty'
 *
 * @return array
 */
function as_search_layout_map( $name ) {

    // Физические клавиши в порядке US QWERTY и буквы ЙЦУКЕН на тех же клавишах.
    $qwerty = array( 'q','w','e','r','t','y','u','i','o','p','[',']','a','s','d','f','g','h','j','k','l',';',"'",'z','x','c','v','b','n','m',',','.','`' );
    $ru     = array( 'й','ц','у','к','е','н','г','ш','щ','з','х','ъ','ф','ы','в','а','п','р','о','л','д','ж','э','я','ч','с','м','и','т','ь','б','ю','ё' );
    // Символы AZERTY на тех же физических клавишах ('' — мёртвая клавиша, отбрасывается).
    $azerty = array( 'a','z','e','r','t','y','u','i','o','p','', '$','q','s','d','f','g','h','j','k','l','m','ù','w','x','c','v','b','n',',',';',':','²' );

    switch ( $name ) {

        case 'qwerty_to_ru':
            // Ровно та карта, что была в версиях до 1.1.0 (включая особенности знаков препинания).
            return array(
                '&' => '?', 'q' => 'й', 'w' => 'ц', 'e' => 'у', 'r' => 'к', 't' => 'е', 'y' => 'н', 'u' => 'г', 'i' => 'ш', 'o' => 'щ', 'p' => 'з', '[' => 'х', ']' => 'ъ',
                'a' => 'ф', 's' => 'ы', 'd' => 'в', 'f' => 'а', 'g' => 'п', 'h' => 'р', 'j' => 'о', 'k' => 'л', 'l' => 'д', ';' => 'ж', "'" => 'э',
                'z' => 'я', 'x' => 'ч', 'c' => 'с', 'v' => 'м', 'b' => 'и', 'n' => 'т', 'm' => 'ь', ',' => 'б', '.' => 'ю', '/' => '.',
                'Q' => 'Й', 'W' => 'Ц', 'E' => 'У', 'R' => 'К', 'T' => 'Е', 'Y' => 'Н', 'U' => 'Г', 'I' => 'Ш', 'O' => 'Щ', 'P' => 'З',
                'A' => 'Ф', 'S' => 'Ы', 'D' => 'В', 'F' => 'А', 'G' => 'П', 'H' => 'Р', 'J' => 'О', 'K' => 'Л', 'L' => 'Д',
                'Z' => 'Я', 'X' => 'Ч', 'C' => 'С', 'V' => 'М', 'B' => 'И', 'N' => 'Т', 'M' => 'Ь',
            );

        case 'ru_to_qwerty':
            $map = array_combine( $ru, $qwerty );
            foreach ( $ru as $i => $letter ) {
                $map[ mb_strtoupper( $letter, 'UTF-8' ) ] = strtoupper( $qwerty[ $i ] );
            }
            return $map;

        case 'ru_to_azerty':
            $map = array_combine( $ru, $azerty );
            foreach ( $ru as $i => $letter ) {
                $map[ mb_strtoupper( $letter, 'UTF-8' ) ] = mb_strtoupper( $azerty[ $i ], 'UTF-8' );
            }
            return $map;

        case 'qwerty_azerty':
            return array( 'a' => 'q', 'q' => 'a', 'z' => 'w', 'w' => 'z', 'A' => 'Q', 'Q' => 'A', 'Z' => 'W', 'W' => 'Z', ';' => 'm' );
    }

    return array();
}

/**
 * ФУНКЦИЯ: альтернативные написания запроса, набранного не в той раскладке
 *
 * Вызывающий код пробует варианты по порядку, когда исходный запрос ничего не нашёл.
 *
 * @param string $searchStr — текст поиска
 *
 * @return string[] Варианты, отличающиеся от исходного (может быть пустым)
 */
function as_search_layout_variants( $searchStr ) {

    $searchStr = (string) $searchStr;
    $lang      = as_search_language();
    $variants  = array();
    $hasCyr    = (bool) preg_match( '/\p{Cyrillic}/u', $searchStr );

    if ( 'ru' === $lang ) {
        $variants[] = strtr( $searchStr, as_search_layout_map( 'qwerty_to_ru' ) );
    } elseif ( 'fr' === $lang ) {
        if ( $hasCyr ) {
            $variants[] = strtr( $searchStr, as_search_layout_map( 'ru_to_azerty' ) );
        } else {
            $variants[] = strtr( $searchStr, as_search_layout_map( 'qwerty_azerty' ) );
        }
    } elseif ( $hasCyr ) {
        $variants[] = strtr( $searchStr, as_search_layout_map( 'ru_to_qwerty' ) );
    }

    $variants = apply_filters( 'argon_shop_search_layout_variants', $variants, $searchStr, $lang );
    $clean    = array();

    foreach ( (array) $variants as $variant ) {
        $variant = trim( (string) $variant );
        if ( '' !== $variant && $variant !== $searchStr && ! in_array( $variant, $clean, true ) ) {
            $clean[] = $variant;
        }
    }

    return $clean;
}

/**
 * ФУНКЦИЯ: стоп-слова, игнорируемые при поиске на латинице
 *
 * @param string $lang — 'en' или 'fr'
 *
 * @return string[]
 */
function as_search_stop_words( $lang ) {

    $words = array();

    if ( 'en' === $lang ) {
        $words = array( 'a', 'an', 'the', 'of', 'for', 'and', 'or', 'with', 'to', 'in', 'on', 'by', 'at', 'from' );
    } elseif ( 'fr' === $lang ) {
        $words = array( 'le', 'la', 'les', 'l', 'un', 'une', 'des', 'de', 'du', 'd', 'et', 'ou', 'pour', 'avec', 'en', 'au', 'aux', 'à', 'a', 'sur', 'par' );
    }

    return (array) apply_filters( 'argon_shop_search_stop_words', $words, $lang );
}

/**
 * ФУНКЦИЯ: упрощённая форма единственного числа латинского слова
 *
 * Поиск использует LIKE '%word%', поэтому более короткая основа лишь расширяет совпадение:
 * «screws» находит «screw» и «screws».
 *
 * @param string $word — слово в нижнем регистре
 * @param string $lang — 'en' или 'fr'
 *
 * @return string
 */
function as_search_singular( $word, $lang ) {

    $len = mb_strlen( $word, 'UTF-8' );

    if ( $len < 4 || preg_match( '/\d/', $word ) ) {
        return $word;
    }

    if ( 'en' === $lang ) {
        if ( $len > 4 && preg_match( '/ies$/u', $word ) ) {
            return mb_substr( $word, 0, -3, 'UTF-8' ) . 'y';
        }
        if ( $len > 4 && preg_match( '/(s|x|z|ch|sh)es$/u', $word ) ) {
            return mb_substr( $word, 0, -2, 'UTF-8' );
        }
        if ( preg_match( '/[^su]s$/u', $word ) && ! preg_match( '/is$/u', $word ) ) {
            return mb_substr( $word, 0, -1, 'UTF-8' );
        }
        return $word;
    }

    if ( 'fr' === $lang && preg_match( '/[^s][sx]$/u', $word ) ) {
        return mb_substr( $word, 0, -1, 'UTF-8' );
    }

    return $word;
}

/**
 * ФУНКЦИЯ: разбивает текст поиска на слова
 *
 * Для 'ru' — разбиение только по пробелам, как в версиях до 1.1.0.
 * Для 'en' / 'fr' — нормализация, описанная в шапке файла.
 * Используется не более 10 слов.
 *
 * @param string $s — текст поиска
 *
 * @return string[] Слова для условий LIKE
 */
function as_search_get_words( $s ) {

    $s = trim( (string) $s );

    if ( $s === '' ) {
        return array();
    }

    $lang = as_search_language();

    if ( 'ru' === $lang ) {
        $words = preg_split( '/\s+/', $s );
        $words = array_filter( $words );
        $words = array_slice( $words, 0, 10 );
        return array_values( $words );
    }

    // Типографские апострофы → ', кавычки → пробелы
    $s = str_replace( array( '’', '‘', 'ʼ', '´', '`' ), "'", $s );
    $s = str_replace( array( '«', '»', '“', '”', '„', '"' ), ' ', $s );

    $raw   = preg_split( '/\s+/u', $s, -1, PREG_SPLIT_NO_EMPTY );
    $stop  = as_search_stop_words( $lang );
    $words = array();
    $kept  = array();

    foreach ( (array) $raw as $word ) {

        // Знаки препинания вокруг слова: «screw,» «(set)» «chaise!»
        $word = preg_replace( '/^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$/u', '', $word );
        $word = mb_strtolower( (string) $word, 'UTF-8' );

        if ( 'fr' === $lang ) {
            $word = preg_replace( "/^(?:l|d|j|m|n|s|t|c|qu|jusqu|lorsqu|puisqu)'/u", '', $word );
        } else {
            $word = preg_replace( "/'s$/u", '', $word );
        }

        if ( '' === $word ) {
            continue;
        }

        $kept[] = $word;

        if ( in_array( $word, $stop, true ) ) {
            continue;
        }

        $words[] = as_search_singular( $word, $lang );
    }

    // Запрос состоит только из стоп-слов («the», «de») — ищем их как есть
    if ( empty( $words ) ) {
        $words = $kept;
    }

    $words = array_values( array_unique( $words ) );

    return array_slice( $words, 0, 10 );
}

/**
 * ФУНКЦИЯ: варианты написания одного слова (может совпасть любой из них)
 *
 * @param string $word — слово из as_search_get_words()
 *
 * @return string[] Сначала само слово, затем варианты
 */
function as_search_word_variants( $word ) {

    $variants = array( $word );

    if ( 'ru' !== as_search_language() ) {

        if ( false !== strpos( $word, "'" ) ) {
            $variants[] = str_replace( "'", '’', $word );
        }

        $folded = str_replace( array( 'œ', 'æ' ), array( 'oe', 'ae' ), $word );
        if ( $folded !== $word ) {
            $variants[] = $folded;
        } elseif ( false !== strpos( $word, 'oe' ) || false !== strpos( $word, 'ae' ) ) {
            $variants[] = str_replace( array( 'oe', 'ae' ), array( 'œ', 'æ' ), $word );
        }
    }

    return array_values( array_unique( $variants ) );
}

/**
 * ФУНКЦИЯ: SQL-условие многословного поиска через AND
 *
 * Общая для страницы /?s= (searchGetFilters.php) и AJAX-поиска (searchAjax.php).
 * Каждое слово должно найтись в заголовке, анонсе, контенте или артикуле (_article).
 *
 * @param string $s — текст поиска
 *
 * @return string ' AND (...) ' или '' если слов нет
 */
function as_search_where_sql( $s ) {

    global $wpdb;

    $where_parts = array();

    foreach ( as_search_get_words( $s ) as $word ) {

        $or_parts = array();

        foreach ( as_search_word_variants( $word ) as $variant ) {
            $like       = '%' . $wpdb->esc_like( $variant ) . '%';
            // Значения подставляются через $wpdb->prepare() — без ручного экранирования
            $or_parts[] = $wpdb->prepare(
                "{$wpdb->posts}.post_title LIKE %s
              OR {$wpdb->posts}.post_excerpt LIKE %s
              OR {$wpdb->posts}.post_content LIKE %s
              OR as_article_meta.meta_value LIKE %s",
                $like, $like, $like, $like
            );
        }

        $where_parts[] = '( ' . implode( ' OR ', $or_parts ) . ' )';
    }

    if ( empty( $where_parts ) ) {
        return '';
    }

    return ' AND (' . implode( ' AND ', $where_parts ) . ') ';
}
