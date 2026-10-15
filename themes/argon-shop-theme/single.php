<?php
/**
 * Перенаправляет на шаблон акций Москвы или Истры либо на стандартный
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov

*/

// Проверяем категории текущей записи
if ( in_category( 36 ) ) { 
    
    // Находит и безопасно подключает single-aktsii-moskva.php из папки темы
    get_template_part( 'single-aktsii-moskva' );
    
} else if ( in_category( 35 ) ) {
    /**
     * У каждого города свой шаблон и свои файлы темы сайта (если вы делаете
     * несколько версий сайта для разных городов без WP multisite).
     * Для удобства можно написать один файл-переключатель и скопировать его во все копии темы
    */
    get_template_part( 'single-aktsii-istra.php' );
    
} else {
    
    // Подключает стандартный шаблон single-default.php
    get_template_part( 'single-default' );
    
}
