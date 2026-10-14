<?php 
/**
 * Customer account page template (CabinetPage)
 * 
 * Template Name: Account page
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * 
 * WHAT IT OUTPUTS:
 * 1. Breadcrumbs (via kama_breadcrumbs from the Argon Shop plugin)
 * 3. Three parts: Account and address / Settings / Orders
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template is a wrapper for the three parts of the customer account.
 * The main work with the user data happens inside them.
 * 
 * PLUGIN FUNCTIONS USED IN THE TEMPLATE:
 * 
 * 1. as_kama_breadcrumbs( ' » ' )
 *    File: includes/interface/breadcrumbs.php
 *    Outputs the breadcrumbs: Home » My account
 * 
 * 2. as_get_cabinetPageID()
 *    File: includes/api/getSetting.php
 *    Used by the plugin's redirectUser.php to protect the page
 *    from unauthorized users (see below).
 * 
 * INCLUDED TEMPLATE PARTS (get_template_part):
 * 
 * 1. template-parts/cabinet/account 
 * 2. template-parts/cabinet/setting 
 * 3. template-parts/cabinet/ordering
 * 
 * PROTECTING THE PAGE FROM GUESTS:
 * 
 * The includes/interface/cabinet/redirectUser.php file contains a
 * template_redirect hook that checks:
 * 
 *   if ( ! is_user_logged_in() && is_page( as_get_cabinetPageID() ) ) {
 *       wp_safe_redirect( wp_login_url() );
 *       exit;
 *   }
 * 
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
          
            <?php 
            // Argon Shop plugin function (includes/interface/breadcrumbs.php)
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( ' » ' );
            }
            ?> 

            <h1 class="title"><?php the_title(); ?></h1>
            
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
            <div id="product_page" class="white_block">
                
                <div class="pageControlPanel" id="cabinetControlPanel">
                    
                    <div class="itemControlPanel itemControlPanelActive" data-type="switch" name="cabinetAccount"><?php esc_html_e( 'Account and address', 'argon-shop-theme' ); ?></div>
                    <div class="itemControlPanel" data-type="switch" name="cabinetSetting"><?php esc_html_e( 'Settings', 'argon-shop-theme' ); ?></div>
                    <div class="itemControlPanel" data-type="switch" name="cabinetOrdering"><?php esc_html_e( 'Orders', 'argon-shop-theme' ); ?></div>
                    
                </div>
                
                <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="cabinetAccount"><?php esc_html_e( 'Account and address', 'argon-shop-theme' ); ?></div>
                
                <div class="blockItemPage" id="block_cabinetAccount">
                    
                    <?php get_template_part( 'template-parts/cabinet/account' ); ?>
                    
                </div>
                
                <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="cabinetSetting"><?php esc_html_e( 'Settings', 'argon-shop-theme' ); ?></div>
                
                <div class="blockItemPage" id="block_cabinetSetting">
                    
                    <?php get_template_part( 'template-parts/cabinet/setting' ); ?>
                    
                </div>
                
                <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="cabinetOrdering"><?php esc_html_e( 'Orders', 'argon-shop-theme' ); ?></div>
                
                <div class="blockItemPage" id="block_cabinetOrdering">
                    
                    <?php get_template_part( 'template-parts/cabinet/ordering' ); ?>
                    
                </div>
				
            </div>
            
            <?php endwhile; endif; ?>
           
        </main>
        
    </div>
</section>

<?php get_footer(); ?>