<footer class="footer">
    
    <div class="footer-content">
    
        <div class="block-footerMenus">
           
           <?php 
    			wp_nav_menu( array(
    				'theme_location'    => 'bottom_menu',
    				'menu_id'           => 'bottom_menu'
    			) );
    		?>
    					
    		<?php 
    			wp_nav_menu( array(
    				'theme_location'    => 'bottom_menu2',
    				'menu_id'           => 'bottom_menu2'
    			) );
    		?>			
           
            
        </div>
        
        <div class="block-footerAddress">
            
            <div class="address">
                
                <a data-fancybox data-type="ajax" data-src="/adres-na-karte-moskva/" href="javascript:;" class="maps_link" >Москва, Ленина, 1</a>
                
            </div>
                
            <div class="work_time">ПН-ПТ: с 9:00 до 18:00</br>СБ: с 10:00 до 15:00</div>
            
            <div class="block-social-icons">
                
                <a href="/" class="vkontakte"></a>
                <a href="/" class="facebook"></a>
                <a href="/" class="instagram"></a>
                
            </div>
            
        </div>
        
        <div class="block-copyright">
            
            © Строй поставка, 1998-<?php echo date('Y'); ?>
            
            <p>mail@example.com</p>
            
        </div>
        
        
    </div>
    
    
    
</footer> 

 <?php wp_footer(); ?>
</body>

</html>
