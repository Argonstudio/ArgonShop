<?php 
	get_header();
	
	/*
	1  
	
	
	*/
	
?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
          
            <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs('<b> / </b>'); ?> 

            
            <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
            <div id="product_page" class="white_block">
                
                <h1 class="title"> <?php the_title(); ?></h1>
                <div class="idPost"><?php echo get_the_id(); ?></div>
            </div>
            <?php endwhile; ?>    
           
        </main>
        
    </div>
</section>

<?php get_footer(); ?>