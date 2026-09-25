<?php
/**
 * Подвал сайта
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Два нижних меню (bottom_menu, bottom_menu2)
 * 2. Адрес и время работы
 * 3. Копирайт с текущим годом
 * 4. Дисклеймер о демонстрационном стенде
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * В подвале функции плагина не вызываются напрямую.
 * Но меню в подвале может содержать пункты с меткой "as_homepage",
 * которые плагин (siteLine.php) заменяет на домен текущего сайта
 * через фильтр wp_nav_menu_objects.
 * 
 * См.:
 * - includes/siteLine.php — замена "as_homepage" на домен
 * - header.php — верхнее меню
 * 
 * ============================================================
 */

// Защита от прямого вызова файла
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
                
                <a href="/adres-na-karte-moskva/" class="maps_link" data-modal-ajax="/adres-na-karte-moskva/">Москва, ул. Ленина, дом 1</a>
                
            </div>
                
            <div class="work_time">ПН-ПТ: с 9:00 до 18:00</br>СБ: с 10:00 до 15:00</div>
            
            
        </div>
        
        <div class="block-copyright">
            
            © ArgonShop, 2018-<?php echo esc_html( wp_date( 'Y' ) ); ?>
            
            <p>mail@example.com</p>
            
        </div>
        
        <div class="footerAddInfo">!!!ВНИМАНИЕ: Демонстрационный стенд. Сайт не является действующим интернет-магазином. Данный веб-сайт работает исключительно в целях демонстрации навыков веб-разработки (портфолио) и не предназначен для ведения коммерческой деятельности. Здесь невозможно заказать или приобрести строительные материалы. Любые цены, товары и описания являются вымышленными. Сбор персональных данных реальных пользователей не осуществляется. Доступ к закрытой части предоставляется только для ознакомления с интерфейсом.</div>
        
    </div>
    
    
    
</footer>

 <?php wp_footer(); ?>
</body>

</html>