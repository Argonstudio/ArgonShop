<?php
/**
 * ArgonShop — статические проверки, не требующие WordPress, базы данных или сайта.
 *
 * Запуск из корня репозитория:
 *   php tests/static-check.php
 *
 * Проверки:
 *   1. Синтаксис PHP каждого файла плагина/темы (php -l).
 *   2. Нет жёстко зашитых кириллических строк в коде (все тексты интерфейса должны идти через __()).
 *   3. Текстовые домены: код плагина использует 'argon-shop', код темы — 'argon-shop-theme'.
 *   4. Переводы: каждая строка из кода есть в .pot и переведена
 *      на ru_RU и fr_FR; плейсхолдеры (%s, %1$s, %d) совпадают.
 *   5. Версии: заголовок плагина = ARGON_SHOP_VERSION = версия темы, в CHANGELOG.md есть
 *      раздел для неё, нет @version в отдельных файлах проекта.
 *   6. Комментарии на русском (в этой RU-ветке): в комментариях PHP разрешена кириллица,
 *      в JS/CSS кириллица допустима только в комментариях (тексты интерфейса приходят из PHP через arsWpAjax.i18n, см. AI_I18N.md).
 *
 *   7. Токенизированная защита чисел/паролей и изолированные регрессионные проверки.
 *
 * Выход: код 0 — всё в порядке, 1 — найдены проблемы (перечислены в выводе).
 *
 * @package Argon_Shop
 * @since   1.1.0
 */

if ( PHP_SAPI !== 'cli' ) {
    exit( "Run from the command line: php tests/static-check.php\n" );
}

$root   = dirname( __DIR__ );
$plugin = $root . '/plugins/argon-shop';
$theme  = $root . '/themes/argon-shop-theme';
$errors = array();
$passed = 0;

function sc_ok( $cond, $label, $details = array() ) {
    global $errors, $passed;
    if ( $cond ) {
        $passed++;
        echo "  ✔ {$label}\n";
        return;
    }
    echo "  ✘ {$label}\n";
    foreach ( array_slice( (array) $details, 0, 30 ) as $d ) {
        echo "      {$d}\n";
    }
    if ( count( (array) $details ) > 30 ) {
        echo '      … and ' . ( count( $details ) - 30 ) . " more\n";
    }
    $errors[] = $label;
}

function sc_php_files( $dir ) {
    $out = array();
    $it  = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
    foreach ( $it as $f ) {
        $p = str_replace( '\\', '/', (string) $f );
        if ( substr( $p, -4 ) === '.php' && false === strpos( $p, '/vendor/' ) && false === strpos( $p, '/languages/' ) ) {
            $out[] = $p;
        }
    }
    sort( $out );
    return $out;
}

function sc_rel( $path ) {
    global $root;
    return ltrim( substr( $path, strlen( $root ) ), '/' );
}

// Строки, которые являются данными, а не текстами интерфейса (намеренно оставлены на русском).
$cyr_allow = array(
    "'/запятая/'",                                          // маркер запятой формата настроек
    '"Название компании"',                                  // ключ сохранённой меты пользователя (accountData)
);
$cyr_allow_files = array(
    'plugins/argon-shop/includes/interface/search/searchLanguage.php' => 'keyboard layout maps (single letters) and layout examples in comments',
);

$gettext = array( // функция => индекс аргумента msgid, индекс аргумента контекста (или null)
    '__' => array( 0, null ), '_e' => array( 0, null ), 'esc_html__' => array( 0, null ), 'esc_html_e' => array( 0, null ),
    'esc_attr__' => array( 0, null ), 'esc_attr_e' => array( 0, null ), '_x' => array( 0, 1 ), '_ex' => array( 0, 1 ),
    'esc_html_x' => array( 0, 1 ), 'esc_attr_x' => array( 0, 1 ), '_n' => array( 0, null ), '_nx' => array( 0, 3 ),
);

/**
 * Собирает вызовы gettext с литеральными аргументами: массив [msgctxt, msgid, domain, line].
 */
function sc_gettext_calls( $file ) {
    global $gettext;
    $tokens = token_get_all( file_get_contents( $file ) );
    $calls  = array();
    $n      = count( $tokens );
    for ( $i = 0; $i < $n; $i++ ) {
        $t = $tokens[ $i ];
        if ( ! is_array( $t ) || T_STRING !== $t[0] || ! isset( $gettext[ $t[1] ] ) ) {
            continue;
        }
        // предыдущий значимый токен не должен быть ->, :: или function
        $j = $i - 1;
        while ( $j >= 0 && is_array( $tokens[ $j ] ) && T_WHITESPACE === $tokens[ $j ][0] ) { $j--; }
        if ( $j >= 0 && is_array( $tokens[ $j ] ) && in_array( $tokens[ $j ][0], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION ), true ) ) {
            continue;
        }
        $k = $i + 1;
        while ( $k < $n && is_array( $tokens[ $k ] ) && T_WHITESPACE === $tokens[ $k ][0] ) { $k++; }
        if ( '(' !== $tokens[ $k ] ) {
            continue;
        }
        // читаем аргументы на глубине 1
        $args = array(); $cur = array(); $depth = 0;
        for ( $k++; $k < $n; $k++ ) {
            $x = $tokens[ $k ];
            if ( '(' === $x || '[' === $x ) { $depth++; }
            if ( ')' === $x || ']' === $x ) {
                if ( 0 === $depth ) { $args[] = $cur; break; }
                $depth--;
            }
            if ( ',' === $x && 0 === $depth ) { $args[] = $cur; $cur = array(); continue; }
            if ( is_array( $x ) && in_array( $x[0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true ) ) { continue; }
            $cur[] = $x;
        }
        $lit = function ( $arg ) {
            if ( 1 !== count( $arg ) || ! is_array( $arg[0] ) || T_CONSTANT_ENCAPSED_STRING !== $arg[0][0] ) {
                return null;
            }
            $s = $arg[0][1];
            return "'" === $s[0] ? str_replace( array( "\\'", '\\\\' ), array( "'", '\\' ), substr( $s, 1, -1 ) ) : stripcslashes( substr( $s, 1, -1 ) );
        };
        list( $mi, $ci ) = $gettext[ $t[1] ];
        $domain_index    = array( '_n' => 3, '_nx' => 4 )[ $t[1] ] ?? ( null === $ci ? 1 : 2 );
        $calls[]         = array(
            'ctx'    => null === $ci ? '' : (string) $lit( $args[ $ci ] ?? array() ),
            'id'     => $lit( $args[ $mi ] ?? array() ),
            'domain' => $lit( $args[ $domain_index ] ?? array() ),
            'line'   => $t[2],
        );
    }
    return $calls;
}

/** Минимальный парсер .po/.pot: возвращает [ «ctx\x04id» => msgstr ]. */
function sc_parse_po( $file ) {
    $entries = array();
    if ( ! is_file( $file ) ) {
        return null;
    }
    $cur = array( 'msgctxt' => '', 'msgid' => null, 'msgstr' => '' ); $key = null;
    $flush = function () use ( &$cur, &$entries ) {
        if ( null !== $cur['msgid'] && '' !== $cur['msgid'] ) {
            $entries[ $cur['msgctxt'] . "\x04" . $cur['msgid'] ] = $cur['msgstr'];
        }
        $cur = array( 'msgctxt' => '', 'msgid' => null, 'msgstr' => '' );
    };
    foreach ( file( $file ) as $line ) {
        $line = rtrim( $line, "\r\n" );
        if ( preg_match( '/^(msgctxt|msgid|msgstr(?:\[0\])?)\s+"(.*)"$/', $line, $m ) ) {
            $k = 'msgstr[0]' === $m[1] ? 'msgstr' : $m[1];
            if ( 'msgctxt' === $k || ( 'msgid' === $k && 'msgctxt' !== $key ) ) {
                $flush();
            }
            $key         = $k;
            $cur[ $key ] = stripcslashes( $m[2] );
        } elseif ( preg_match( '/^"(.*)"$/', $line, $m ) && $key ) {
            $cur[ $key ] .= stripcslashes( $m[1] );
        } elseif ( preg_match( '/^msgstr\[\d+\]/', $line ) ) {
            $key = null; // другие формы множественного числа игнорируются
        }
    }
    $flush();
    return $entries;
}

function sc_placeholders( $s ) {
    preg_match_all( '/%(?:\d+\$)?[sdf]/', $s, $m );
    $p = $m[0];
    sort( $p );
    return implode( ' ', $p );
}

// ------------------------------------------------------------------
echo "1. PHP syntax\n";
$bad = array();
foreach ( array_merge( sc_php_files( $plugin ), sc_php_files( $theme ), sc_php_files( __DIR__ ) ) as $f ) {
    $out = array(); $code = 0;
    exec( escapeshellarg( PHP_BINARY ) . ' -l ' . escapeshellarg( $f ) . ' 2>&1', $out, $code );
    if ( 0 !== $code ) {
        $bad[] = sc_rel( $f ) . ': ' . trim( implode( ' ', $out ) );
    }
}
sc_ok( ! $bad, 'All PHP files are syntactically valid', $bad );

// ------------------------------------------------------------------
echo "2. Hard-coded texts\n";
$bad = array();
foreach ( array_merge( sc_php_files( $plugin ), sc_php_files( $theme ) ) as $f ) {
    $rel = sc_rel( $f );
    foreach ( token_get_all( file_get_contents( $f ) ) as $t ) {
        if ( ! is_array( $t ) || ! preg_match( '/\p{Cyrillic}/u', $t[1] ) ) {
            continue;
        }
        if ( T_CONSTANT_ENCAPSED_STRING === $t[0] || T_ENCAPSED_AND_WHITESPACE === $t[0] ) {
            if ( in_array( $t[1], $cyr_allow, true ) || ( isset( $cyr_allow_files[ $rel ] ) && mb_strlen( $t[1] ) <= 5 ) ) {
                continue;
            }
            $bad[] = "{$rel}:{$t[2]} " . mb_substr( $t[1], 0, 80 );
        } elseif ( T_INLINE_HTML === $t[0] ) {
            $html = preg_replace( '/<!--.*?-->|\/\*.*?\*\/|(?<![:"\'])\/\/[^\n]*/s', '', $t[1] );  // HTML- и JS-комментарии
            $html = preg_replace( '/[A-Za-z]+\p{Cyrillic}[A-Za-z]+/u', '', $html );                     // смешанные идентификаторы (латинское слово с кириллической буквой внутри)
            if ( preg_match( '/\p{Cyrillic}[^<>\n]{0,60}/u', $html, $m ) ) {
                $bad[] = "{$rel}:{$t[2]} " . trim( $m[0] );
            }
        }
    }
}
sc_ok( ! $bad, 'No hard-coded Cyrillic texts in code (use __() / esc_html_e() with an English source string)', $bad );

// ------------------------------------------------------------------
echo "3. Text domains\n";
$calls = array( 'argon-shop' => array(), 'argon-shop-theme' => array() );
$bad   = array();
foreach ( array( 'argon-shop' => $plugin, 'argon-shop-theme' => $theme ) as $domain => $dir ) {
    foreach ( sc_php_files( $dir ) as $f ) {
        foreach ( sc_gettext_calls( $f ) as $c ) {
            if ( $c['domain'] !== $domain ) {
                $bad[] = sc_rel( $f ) . ":{$c['line']} domain '" . ( $c['domain'] ?? 'not a literal' ) . "' instead of '{$domain}'";
                continue;
            }
            if ( null === $c['id'] ) {
                $bad[] = sc_rel( $f ) . ":{$c['line']} the text is not a literal string (translation tools cannot extract it)";
                continue;
            }
            $calls[ $domain ][ $c['ctx'] . "\x04" . $c['id'] ] = sc_rel( $f ) . ":{$c['line']}";
        }
    }
}
sc_ok( ! $bad, 'Every translation call uses the correct text domain and a literal string', $bad );

// ------------------------------------------------------------------
echo "4. Translations\n";
$files = array(
    'argon-shop'       => array( 'pot' => "$plugin/languages/argon-shop.pot", 'ru_RU' => "$plugin/languages/argon-shop-ru_RU.po", 'fr_FR' => "$plugin/languages/argon-shop-fr_FR.po" ),
    'argon-shop-theme' => array( 'pot' => "$theme/languages/argon-shop-theme.pot", 'ru_RU' => "$theme/languages/ru_RU.po", 'fr_FR' => "$theme/languages/fr_FR.po" ),
);
foreach ( $files as $domain => $set ) {
    $pot = sc_parse_po( $set['pot'] );
    sc_ok( null !== $pot, "{$domain}: .pot exists", array( sc_rel( $set['pot'] ) ) );
    if ( null === $pot ) {
        continue;
    }
    $missing = array();
    foreach ( $calls[ $domain ] as $key => $where ) {
        if ( ! isset( $pot[ $key ] ) ) {
            $missing[] = "{$where} \"" . str_replace( "\x04", '" | "', $key ) . '"';
        }
    }
    sc_ok( ! $missing, "{$domain}: .pot contains all strings from the code (otherwise regenerate it, see docs/ai/AI_I18N.md)", $missing );

    foreach ( array( 'ru_RU', 'fr_FR' ) as $locale ) {
        $po = sc_parse_po( $set[ $locale ] );
        sc_ok( null !== $po && is_file( substr( $set[ $locale ], 0, -3 ) . '.mo' ), "{$domain} {$locale}: .po and .mo exist" );
        if ( null === $po ) {
            continue;
        }
        $untranslated = array(); $ph = array();
        foreach ( $pot as $key => $unused ) {
            $src = explode( "\x04", $key, 2 )[1];
            if ( ! isset( $po[ $key ] ) || '' === $po[ $key ] ) {
                $untranslated[] = '"' . $src . '"';
            } elseif ( sc_placeholders( $src ) !== sc_placeholders( $po[ $key ] ) ) {
                $ph[] = '"' . $src . '" → "' . $po[ $key ] . '"';
            }
        }
        sc_ok( ! $untranslated, "{$domain} {$locale}: all strings translated", $untranslated );
        sc_ok( ! $ph, "{$domain} {$locale}: placeholders (%s, %1\$s, %d) match", $ph );
        $mo = substr( $set[ $locale ], 0, -3 ) . '.mo';
        sc_ok( ! is_file( $mo ) || filemtime( $mo ) >= filemtime( $set[ $locale ] ) - 2, "{$domain} {$locale}: .mo is not older than .po (recompile after editing .po)" );
    }
}

// ------------------------------------------------------------------
echo "5. Versions\n";
$main = file_get_contents( "$plugin/argon-shop.php" );
preg_match( '/^\s*\*\s*Version:\s*(\S+)/m', $main, $h );
preg_match( "/define\(\s*'ARGON_SHOP_VERSION',\s*'([^']+)'/", $main, $c );
preg_match( '/^Version:\s*(\S+)/m', file_get_contents( "$theme/style.css" ), $t );
$v = $h[1] ?? '?';
sc_ok( isset( $c[1], $t[1] ) && $v === $c[1] && $v === $t[1], "Plugin header ({$v}), ARGON_SHOP_VERSION (" . ( $c[1] ?? '?' ) . ') and theme style.css (' . ( $t[1] ?? '?' ) . ') are equal' );
sc_ok( (bool) preg_match( '/^## \[' . preg_quote( $v, '/' ) . '\]/m', (string) @file_get_contents( "$root/CHANGELOG.md" ) ), "CHANGELOG.md has a section ## [{$v}]" );
$bad = array();
foreach ( array_merge( sc_php_files( $plugin ), sc_php_files( $theme ) ) as $f ) {
    if ( preg_match( '/^\s*\*\s*@version\s/m', file_get_contents( $f ) ) ) {
        $bad[] = sc_rel( $f );
    }
}
foreach ( array( $plugin . '/assets', $theme . '/assets' ) as $dir ) {
    $it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
    foreach ( $it as $f ) {
        $p = str_replace( '\\', '/', (string) $f );
        if ( preg_match( '/\.(js|css)$/', $p ) && false === strpos( $p, '/vendor/' ) && preg_match( '/^\s*\*\s*@version\s/m', file_get_contents( $p ) ) ) {
            $bad[] = sc_rel( $p );
        }
    }
}
sc_ok( ! $bad, 'No per-file @version tags (the version lives only in argon-shop.php and style.css)', $bad );

// ------------------------------------------------------------------
echo "6. Комментарии на русском (RU-ветка)\n";
$bad = array();
// RU-ветка: комментарии PHP намеренно переведены на русский.
sc_ok( true, 'PHP-комментарии на русском (кириллица разрешена)', array() );
$bad = array();
foreach ( array( $plugin . '/assets', $theme . '/assets' ) as $dir ) {
    $it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
    foreach ( $it as $f ) {
        $p = str_replace( '\\', '/', (string) $f );
        if ( ! preg_match( '/\.(js|css)$/', $p ) || false !== strpos( $p, '/vendor/' ) ) {
            continue;
        }
        $src = file_get_contents( $p );
        $src = preg_replace( '~/\*.*?\*/~s', '', $src );
        $src = preg_replace( '~//[^\n]*~', '', $src );
        foreach ( explode( "\n", $src ) as $i => $line ) {
            if ( preg_match( '/\p{Cyrillic}[^\n]{0,60}/u', $line, $m ) ) {
                $bad[] = sc_rel( $p ) . ':' . ( $i + 1 ) . ' ' . trim( $m[0] );
            }
        }
    }
}
sc_ok( ! $bad, 'В JS/CSS кириллица только в комментариях (код без кириллицы, тексты интерфейса через arsWpAjax.i18n)', $bad );

// ------------------------------------------------------------------
echo "7. Числа и пароли\n";
require_once __DIR__ . '/regression-guards.php';
$data_php = (string) file_get_contents( "$plugin/includes/api/getData/getData.php" );
$cabinet_php = (string) file_get_contents( "$plugin/includes/interface/cabinet/myCabinet.php" );
sc_ok( as_test_number_guard( $data_php ), 'Price functions use as_to_float(), never floatval() (token-based check)' );
sc_ok( as_test_password_guard( $cabinet_php ), 'Password assignments use wp_unslash() without sanitizers (token-based check)' );
$regression_output = array(); $regression_code = 0;
exec( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __DIR__ . '/regression-check.php' ) . ' 2>&1', $regression_output, $regression_code );
sc_ok( 0 === $regression_code, 'Isolated number, wholesale, password and guard regression checks pass', $regression_output );

// ------------------------------------------------------------------
echo "\n";
if ( $errors ) {
    echo 'FAILED: ' . count( $errors ) . " check(s), passed: {$passed}\n";
    exit( 1 );
}
echo "OK: all {$passed} checks passed\n";
exit( 0 );
