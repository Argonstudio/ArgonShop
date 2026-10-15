<?php
/** Регрессионные проверки по токенам; пробелы и комментарии игнорируются. */
function as_test_function_code( $source, $wanted ) {
    $tokens = token_get_all( $source );
    $count = count( $tokens );
    for ( $i = 0; $i < $count; $i++ ) {
        if ( ! is_array( $tokens[$i] ) || T_FUNCTION !== $tokens[$i][0] ) continue;
        $j = $i + 1;
        while ( $j < $count && ( ! is_array( $tokens[$j] ) || T_STRING !== $tokens[$j][0] ) && '{' !== $tokens[$j] ) $j++;
        if ( $j >= $count || ! is_array( $tokens[$j] ) || $wanted !== $tokens[$j][1] ) continue;
        while ( $j < $count && '{' !== $tokens[$j] ) $j++;
        $body = ''; $depth = 0;
        for ( ; $j < $count; $j++ ) {
            $t = $tokens[$j];
            if ( '{' === $t ) $depth++;
            if ( '}' === $t ) $depth--;
            if ( is_array( $t ) ) {
                if ( in_array( $t[0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true ) ) continue;
                $body .= $t[1];
            } else {
                $body .= $t;
            }
            if ( 0 === $depth ) break;
        }
        return $body;
    }
    return null;
}
function as_test_number_guard( $source ) {
    foreach ( array( 'as_getActualPrice', 'as_getActualStepDiscont', 'as_get_product_price' ) as $name ) {
        $body = as_test_function_code( $source, $name );
        if ( null === $body || preg_match( '/\bfloatval\(/i', $body ) || false === strpos( $body, 'as_to_float(' ) ) return false;
    }
    return true;
}
function as_test_password_guard( $source ) {
    $body = as_test_function_code( $source, 'as_cabinetEditEmailPassword_callback' );
    if ( null === $body ) return false;
    foreach ( array( 'pass', 'newPass', 'newPassConfirm' ) as $name ) {
        if ( ! preg_match_all( '/\$' . $name . '=(?!=)([^;]+);/', $body, $matches ) ) return false;
        foreach ( $matches[1] as $expression ) {
            if ( preg_match( '/\b(?:sanitize_[a-z_]+|trim|ltrim|rtrim|strip_tags|htmlspecialchars|esc_[a-z_]+)\(/i', $expression ) ) return false;
        }
        if ( ! preg_match( '/\$' . $name . '=(?!=)[^;]*wp_unslash\(/', $body ) ) return false;
    }
    return true;
}
