<?php 
	get_header();
	
	
?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
       
       <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs('<span class="b"> » </span>'); 
       
       echo "<div class='block-pageSearch'>";
       
       $searchParameters = Array(
        			            
        	//"post_type"   => Array('post', 'product'),
        	"post_type"   => Array('product'),
        	'taxonomy'    => Array(
        			                
        		"catalog"  => "all",
        		//"category" => Array(36,37) 
        			                
        	),
        			                
        	"classForm"   => "pageSearchForm",
        	"classInput"  => "pageSearchInput",
        	"classSubmit" => "pageSearchSubmit",
        	"valueSubmit" => "",
        			            
        	"ajax"        => Array(
        			                
        		"classBlockResult" => "pageSearchBlockResult",
        		"positionResult"   => "bottom",
        		'productResult'    => true,
        		"catalogResult"    => true,
        		"postResult"       => Array(
        			                                  
                        Array(
                                            			                      
                            "name"         => "Акции и статьи",
                            "class"        => "resultEntry",
                            "category"     => Array(36,37) 
                            			                                      
                        )
                        			                                  
                    )
    		)     
        			            
        );    
        			            
        			    
        as_search($searchParameters); 
       
        echo "</div>";
       
       if(get_search_query()){
           
           $productsShoppingCart = getCookie("productsShoppingCart");
           
       ?>
       
           <div class="block-pageSearchResult catalogProductList">
               
                <?php if ( have_posts() ) while ( have_posts() ) : the_post(); 
                
                    generate_product_card($post, $productsShoppingCart);
                    
                endwhile; ?>
                <?php kama_pagenavi(); ?>	
            </div>
       
       <?php
       
       }else{
           echo "Введите поисковой запрос";
       }
       
       ?>
        </main>
        
    </div>
</section>

<?php get_footer(); ?>