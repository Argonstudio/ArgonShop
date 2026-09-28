<?php 

$queried_object = get_queried_object();
$current_id = $queried_object->term_id;
$parent_id = $queried_object->parent;

get_header(); ?>
<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
            
            <?php 
            //$taxonomy = get_taxonomy(get_queried_object()->taxonomy);
            //echo $taxonomy->labels->name;
            
            $categoryTerm = get_term_by("slug", get_queried_object()->taxonomy, 'category' );
            ?>
            
            
            <div class="kama_breadcrumbs" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    
                    <a href="http://zbk.ru" itemprop="item">
                        <span itemprop="name">Главная</span>
                    </a>
                    
                </span>
                
                <span class="kb_sep"><span class="b"> / </span></span> 
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    
                    <a href="http://zbk.ru/stati/" itemprop="item">
                        <span itemprop="name">Статьи</span>
                    </a>
                    
                </span>
                
                <span class="kb_sep"><span class="b"> / </span></span> 
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    
                    <a href="<?php echo get_term_link($categoryTerm->term_id,'category'); ?>" itemprop="item">
                        <span itemprop="name"><?php echo $categoryTerm->name; ?></span>
                    </a>
                    
                </span>
                
                <span class="kb_sep"><span class="b"> / </span></span> 
                
                <span class="kb_title"><?php single_term_title(); ?></span>
                
            </div>

            <div id="production_list_page" class="white_block">

					<h1 class="title"><?php single_term_title(); ?></h1>
					
					<div class="detailed-fields-page">
					    
					    <?php 
					    
					    $img_url = wp_get_attachment_url( get_field('image_catalog',get_queried_object()->taxonomy.'_'.get_queried_object()->term_id) ); 
					    
					    if($img_url){
					        
					    ?>
					    
                            <img class="detailed-fields-pageImg" src="<?php echo $img_url; ?>" title="<?php single_term_title(); ?>">
                            
                        <?php } ?>
                        
                        <div class='detailed-fields-pageDescription'><?php echo term_description(); ?></div>
                        
                    </div>   
                    
					    <?php
					    
                            //echo "<div class='detailed-fields-pageDescription'>". term_description(). "</div>";
                        

                        ?>
				
			</div><!-- #production_list_page -->

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>
