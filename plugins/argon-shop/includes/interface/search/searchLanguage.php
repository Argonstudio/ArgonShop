<?php
/**
 * Search language rules: keyboard layout fixes and word normalization.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 * @since   1.1.0
 *
 * WHERE TO TEST:
 * 1. Search field in the header (AJAX search)
 * 2. The /?s= search page
 * 3. Admin → Orders → edit order → "Add product" field
 *
 * The rules depend on the site language (Settings → General → Site Language):
 *
 * ru — Cyrillic store (behaviour of versions up to 1.1.0, unchanged):
 *      - text typed in the English QWERTY layout is converted to ЙЦУКЕН
 *        ("ghjlern" → "продукт");
 *      - words are split by spaces only.
 *
 * en — Latin store:
 *      - text typed in the Russian layout by mistake is converted to QWERTY
 *        ("ыскуц" → "screw");
 *      - typographic quotes and apostrophes are normalized, punctuation around words is removed;
 *      - stop words (the, of, for, and…) are ignored;
 *      - possessive 's and plural endings are removed (screws → screw, boxes → box, batteries → battery).
 *
 * fr — Latin store with the AZERTY keyboard:
 *      - text typed in the Russian layout on an AZERTY keyboard is converted
 *        to AZERTY ("срйшыу" → "chaise");
 *      - text typed in QWERTY on an AZERTY keyboard (or vice versa) is fixed
 *        by swapping a↔q, z↔w and ;→m ("chqise" → "chaise");
 *      - elisions are removed (l'huile → huile, d'eau → eau), stop words
 *        (le, la, les, de, des, du, et, pour…) are ignored;
 *      - plural -s / -x is removed (chaises → chaise);
 *      - œ / æ match oe / ae and the ' / ’ apostrophes match each other.
 *
 * Other locales use the "en" rules. Accents (é = e) are compared by the
 * database collation: the WordPress default utf8mb4_unicode(_520)_ci ignores them.
 *
 * HOOKS:
 * - filter argon_shop_search_language ( string $lang, string $locale ) — 'ru' | 'en' | 'fr'
 * - filter argon_shop_search_stop_words ( array $words, string $lang )
 * - filter argon_shop_search_layout_variants ( array $variants, string $searchStr, string $lang )
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Search language of the store
 *
 * Determined by the site locale (content language), not by the language of the admin user.
 *
 * @return string 'ru', 'en' or 'fr'
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
 * FUNCTION: Keyboard layout maps
 *
 * Each map is "character typed → character intended" for the same physical key.
 *
 * @param string $name — 'qwerty_to_ru', 'ru_to_qwerty', 'ru_to_azerty', 'qwerty_azerty'
 *
 * @return array
 */
function as_search_layout_map( $name ) {

    // Physical keys in the US QWERTY order and the ЙЦУКЕН letters on the same keys.
    $qwerty = array( 'q','w','e','r','t','y','u','i','o','p','[',']','a','s','d','f','g','h','j','k','l',';',"'",'z','x','c','v','b','n','m',',','.','`' );
    $ru     = array( 'й','ц','у','к','е','н','г','ш','щ','з','х','ъ','ф','ы','в','а','п','р','о','л','д','ж','э','я','ч','с','м','и','т','ь','б','ю','ё' );
    // The AZERTY characters on the same physical keys ('' — dead key, dropped).
    $azerty = array( 'a','z','e','r','t','y','u','i','o','p','', '$','q','s','d','f','g','h','j','k','l','m','ù','w','x','c','v','b','n',',',';',':','²' );

    switch ( $name ) {

        case 'qwerty_to_ru':
            // Exactly the map of versions up to 1.1.0 (including the punctuation quirks).
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
 * FUNCTION: Alternative spellings of a query typed in the wrong keyboard layout
 *
 * Callers try the variants in order when the original query found nothing.
 *
 * @param string $searchStr — search text
 *
 * @return string[] Variants that differ from the original (may be empty)
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
 * FUNCTION: Stop words that are ignored in Latin-language search
 *
 * @param string $lang — 'en' or 'fr'
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
 * FUNCTION: Light singular form of a Latin-language word
 *
 * Search uses LIKE '%word%', so a shorter stem only widens the match:
 * "screws" finds "screw" and "screws".
 *
 * @param string $word — lowercase word
 * @param string $lang — 'en' or 'fr'
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
 * FUNCTION: Split the search text into words
 *
 * For 'ru' — splitting by spaces only, exactly as in versions up to 1.1.0.
 * For 'en' / 'fr' — the normalization described in the file header.
 * At most 10 words are used.
 *
 * @param string $s — search text
 *
 * @return string[] Words for the LIKE conditions
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

    // Typographic apostrophes → ', quotes → spaces
    $s = str_replace( array( '’', '‘', 'ʼ', '´', '`' ), "'", $s );
    $s = str_replace( array( '«', '»', '“', '”', '„', '"' ), ' ', $s );

    $raw   = preg_split( '/\s+/u', $s, -1, PREG_SPLIT_NO_EMPTY );
    $stop  = as_search_stop_words( $lang );
    $words = array();
    $kept  = array();

    foreach ( (array) $raw as $word ) {

        // Punctuation around the word: "screw," "(set)" "chaise!"
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

    // The query consists only of stop words ("the", "de") — search them as they are
    if ( empty( $words ) ) {
        $words = $kept;
    }

    $words = array_values( array_unique( $words ) );

    return array_slice( $words, 0, 10 );
}

/**
 * FUNCTION: Spelling variants of one word (any of them may match)
 *
 * @param string $word — word from as_search_get_words()
 *
 * @return string[] The word itself first, then the variants
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
 * FUNCTION: SQL condition of the multi-word AND search
 *
 * Shared by the /?s= page (searchGetFilters.php) and the AJAX search (searchAjax.php).
 * Every word must be found in the title, excerpt, content or SKU (_article).
 *
 * @param string $s — search text
 *
 * @return string ' AND (...) ' or '' if there are no words
 */
function as_search_where_sql( $s ) {

    global $wpdb;

    $where_parts = array();

    foreach ( as_search_get_words( $s ) as $word ) {

        $or_parts = array();

        foreach ( as_search_word_variants( $word ) as $variant ) {
            $like       = '%' . $wpdb->esc_like( $variant ) . '%';
            // Values are inserted via $wpdb->prepare() — no manual escaping
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
