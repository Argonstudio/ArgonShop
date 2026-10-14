<?php
/**
 * Site footer
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * 
 * WHAT IT OUTPUTS:
 * 1. Two bottom menus (bottom_menu, bottom_menu2)
 * 2. Address and opening hours
 * 3. Copyright with the current year
 * 4. Demo stand disclaimer
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * The footer does not call plugin functions directly.
 * But the footer menu may contain items with the "as_homepage" marker,
 * which the plugin (siteLine.php) replaces with the current site domain
 * via the wp_nav_menu_objects filter.
 * 
 * See:
 * - includes/siteLine.php — replacing "as_homepage" with the domain
 * - header.php — top menu
 * 
 * ============================================================
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<footer class="footer">
    
    <div class="footer-content">
    
        <div class="block-footerMenus">
           
           <?php 
            wp_nav_menu( array(
                'theme_location' => 'bottom_menu',
                'menu_id'        => 'bottom_menu',
            ) );
            ?>
                    
            <?php 
            wp_nav_menu( array(
                'theme_location' => 'bottom_menu2',
                'menu_id'        => 'bottom_menu2',
            ) );
            ?>          
           
            
        </div>
        
        <div class="block-footerAddress">
            
            <div class="address">
                
                <a href="/adres-na-karte-moskva/" class="maps_link" data-modal-ajax="/adres-na-karte-moskva/"><?php esc_html_e( 'City Hall, New York, NY', 'argon-shop-theme' ); ?></a>
                
            </div>
                
            <div class="work_time"><?php esc_html_e( 'Mon–Fri: 9:00 to 18:00', 'argon-shop-theme' ); ?></br><?php esc_html_e( 'Sat: 10:00 to 15:00', 'argon-shop-theme' ); ?></div>
            
            
        </div>
        
        <div class="block-copyright">
            
            © ArgonShop, 2018-<?php echo esc_html( wp_date( 'Y' ) ); ?>
            
            <p>mail@example.com</p>
            
        </div>
        
        <div class="footerAddInfo"><?php esc_html_e( '!!!ATTENTION: Demo stand. This site is not an operating online store. It exists solely to demonstrate web development skills (portfolio) and is not intended for commercial activity. Building materials cannot be ordered or purchased here. All prices, products and descriptions are fictional. No personal data of real users is collected. Access to the private area is provided only to explore the interface.', 'argon-shop-theme' ); ?></div>
        
    </div>
    
    
    
</footer>

 <?php wp_footer(); ?>
</body>

</html>