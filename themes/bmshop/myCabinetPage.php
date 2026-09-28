<?php 
	get_header();
	
	/*
	
	Template name: Мой кабинет
	
	ПОЛУЧАЕМ ОПТОВЫЕ ЦЕНЫ $wholesalePrice = get_post_meta(get_the_ID(), '_wholesalePrice',true);
	 
	
	
	*/
	
?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
          
            <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs(' » '); ?> 

            <h1 class="title"><?php the_title(); ?></h1>
            
            <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
            <div id="product_page" class="white_block">
                
                <div class="pageControlPanel" id="cabinetControlPanel">
                    
                    <div class="itemControlPanel itemControlPanelActive" data-type="switch" name="cabinetAccount">Аккаунт и адрес</div>
                    <div class="itemControlPanel" data-type="switch" name="cabinetSetting">Настройки</div>
                    <div class="itemControlPanel" data-type="switch" name="cabinetOrdering">Заказы</div>
                    
                </div>
                
                <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="cabinetAccount">Аккаунт и адрес</div>
                
                <div class="blockItemPage" id="block_cabinetAccount">
                    
                    <?php get_template_part( 'template-parts/cabinet/account' ); ?>
                    
                </div>
                
                <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="cabinetSetting">Настройки</div>
                
                <div class="blockItemPage" id="block_cabinetSetting">
                    
                    <?php get_template_part( 'template-parts/cabinet/setting' ); ?>
                    
                </div>
                
                <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="cabinetOrdering">Заказы</div>
                
                <div class="blockItemPage" id="block_cabinetOrdering">
                    
                    <?php get_template_part( 'template-parts/cabinet/ordering' ); ?>
                    
                </div>
                
                
                
                
				
            </div>
            <?php endwhile; ?>
           
        </main>
        
    </div>
</section>

<?php get_footer(); ?>