<?php
/**
 * Перенаправляет на шаблон акции Москвы, Истры и по умолчанию
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0 

*/

// Проверяем категории для текущего поста
if ( in_category( 36 ) ) { 
    
    // Ищет и безопасно подключает single-aktsii-moskva.php из папки темы
    get_template_part( 'single-aktsii-moskva' );
    
} else if ( in_category( 35 ) ) {
    /**
     * У каждого города собственный шаблон и собственные файлы темы сайта(если делать
     * несколько версий сайта для разных городов, без WP мультисайт)
     * Для удобства можно написать один файл переключения и скопировать во все копии темы
    */
    get_template_part( 'single-aktsii-istra.php' );
    
} else {
    
    // Подключает дефолтный шаблон single-default.php
    get_template_part( 'single-default' );
    
}
