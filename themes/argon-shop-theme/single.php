<?php
/**
 * Redirects to the Moscow or Istra promotion template, or the default one
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov

*/

// Check the categories of the current post
if ( in_category( 36 ) ) { 
    
    // Finds and safely includes single-aktsii-moskva.php from the theme folder
    get_template_part( 'single-aktsii-moskva' );
    
} else if ( in_category( 35 ) ) {
    /**
     * Each city has its own template and its own site theme files (if you make
     * several site versions for different cities without WP multisite).
     * For convenience you can write one switching file and copy it to all theme copies
    */
    get_template_part( 'single-aktsii-istra.php' );
    
} else {
    
    // Includes the default template single-default.php
    get_template_part( 'single-default' );
    
}
