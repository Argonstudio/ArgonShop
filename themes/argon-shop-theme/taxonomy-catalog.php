<?php
/**
 * "Catalog" taxonomy template
 * 
 * Template for displaying a product catalog category
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 *
 * 
 * WHAT IT OUTPUTS:
 * 1. Breadcrumbs (via kama_breadcrumbs)
 * 2. Category title (or a custom one via ACF second_title)
 * 3. Category description (term_description)
 * 4. Child categories as cards
 * 5. Popular queries (cards)
 * 6. Second description (ACF second_desc)
 * 7. Product filter (if enabled)
 * 8. Product sorting
 * 9. List of category products
 * 10. Bottom description (ACF bottom_desc)
 * 11. Pagination
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template uses the following plugin functions:
 * 
 * - as_kama_breadcrumbs()     → breadcrumbs (breadcrumbs.php)
 * - as_kama_pagenavi()        → pagination (pagenavi.php)
 * - as_getCookie()            → reading the sort cookie (getData.php)
 * - as_sort()              → sort output (sort.php)
 * - as_get_sort_products()    → product sorting (catalog.php)
 * - as_view_products_list()   → product list output (productsLists.php)
 * 
 * EXTERNAL DEPENDENCIES:
 * - ACF (Advanced Custom Fields) — second_title, second_desc, bottom_desc, image_catalog, catalog_type fields
 * - wp_product_filter() — filter function (if installed)
 * 
 * ============================================================
 *
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================
// GET DATA ABOUT THE CURRENT CATEGORY
// ============================================
// 

$queried_object = get_queried_object();

$current_id = 0;
$parent_id  = 0;

if ( $queried_object && is_object( $queried_object ) && isset( $queried_object->term_id ) ) {
    $current_id = absint( $queried_object->term_id );
    $parent_id  = isset( $queried_object->parent ) ? absint( $queried_object->parent ) : 0;
}

get_header(); ?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
            
            <?php 
            // ============================================
            // BREADCRUMBS
            // ============================================
            // Argon Shop plugin function (includes/interface/breadcrumbs.php)
            
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( '<span class="b"> » </span>' );
            }
            ?>

            <div id="production_list_page" class="white_block">

					<h1 class="title"> 
						<?php 
						// ============================================
						// CATEGORY TITLE
						// ============================================
						// If the category has the ACF second_title field, output it.
						// Otherwise — the standard term title.
						
						$second_title = '';
						
						if ( function_exists( 'get_field' ) ) {
						    $second_title = get_field( 'second_title', 'catalog_' . $current_id );
						}
						
						if ( $second_title ) {
						    echo esc_html( $second_title );
						} else {
						    single_term_title();
						}
						?>
					</h1>

					<?php
					    
					$termDescription = term_description();

                    // PHP 8.5: $_GET is always an array, but check just in case
                    $get_is_empty = ! is_array( $_GET ) || empty( $_GET );
                    
                    if ( $termDescription && $get_is_empty ) { ?>
                        
                        <div class="catalogPage_openMobile mobileOpen-topDescr"><?php esc_html_e( 'Category description', 'argon-shop-theme' ); ?></div>
                            
                        <div class="catalogPage_mobileExtensible topDescr"><?php echo $termDescription; ?></div>
                     
                    <?php } ?>
					
					<?php 
					
					// ============================================
					// CHILD CATEGORIES AND POPULAR QUERIES
					// ============================================
					// 
					
					$terms = get_terms( array(
					    'taxonomy'   => 'catalog',
					    'hide_empty' => false,
					    'parent'     => $current_id,
					) );
					
					$termsRequest = array();
						
					if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
						
						$count = count( $terms ); 
						$i = 0;  
						?>
							
						<div class="catalogPage_openMobile mobileOpen-cardsCategory"><?php esc_html_e( 'Subcategories', 'argon-shop-theme' ); ?></div>
							
						<div class="catalogPage_mobileExtensible block-cards-category">
							    
    						<?php foreach ( $terms as $term ) { $i++; ?>
    							
    							<?php 
    							
    							// PHP 8.5: get_field may return false/null/an array
    							$image = function_exists( 'get_field' ) ? get_field( 'image_catalog', $term ) : false;
    							$type  = function_exists( 'get_field' ) ? get_field( 'catalog_type', $term ) : false;
    							
    							// Extract the URL from the ACF array
    							$image_url = '';
    							
    							if ( is_array( $image ) && isset( $image['url'] ) ) {
    							    $image_url = $image['url'];
    							} elseif ( is_string( $image ) ) {
    							    $image_url = $image;
    							}
    							
    							if ( $type === 'base' ) {
    								
    								// Term link
    								$term_link = get_term_link( $term );
    								
    								if ( is_wp_error( $term_link ) ) {
    								    $term_link = '#';
    								}
    								
    								$term_name = isset( $term->name ) ? $term->name : '';
    							?>
    							
    							<span class="block-card-category">
    							    
    							    <a href="<?php echo esc_url( $term_link ); ?>" 
    							       class="card-category<?php echo $image_url ? '' : ' card-category-notImg'; ?>" 
    							       style="background-image:url(<?php echo esc_url( $image_url ); ?>)">
    								    
        								<span><?php echo esc_html( $term_name ); ?></span>
        									
        							</a>
    							    
    							</span>
    							
    								
    							<?php 
    								    
    							} else {
    								
    								array_push( $termsRequest, $term );
    								
    							}
    							
    							?>
    								
    						<?php } ?>
    							
						 </div><!-- .block-cards-category -->
						
						<?php } ?>
						
						
						<?php if ( ! empty( $termsRequest ) ) { ?>
						
						<div class="block-request">
						        
            				<div class="catalogPage_openMobile titleCardsRequest"><?php esc_html_e( 'Popular searches', 'argon-shop-theme' ); ?></div>
            				
            				<div class="catalogPage_mobileExtensible">
            							
                        		<div class="block-cards-request">
                        						    
                        		<?php foreach ( $termsRequest as $term ) { 
                        		    
                        		    $term_link = get_term_link( $term );
                        		    
                        		    if ( is_wp_error( $term_link ) ) {
                        		        $term_link = '#';
                        		    }
                        		    
                        		    $term_name = isset( $term->name ) ? $term->name : '';
                        		?>
                        						        
                        			<a href="<?php echo esc_url( $term_link ); ?>" class="card-request">
                            								    
                            			<span><?php echo esc_html( $term_name ); ?></span>
                            									
                            		</a>
                        						      
                        		<?php } ?>
                        						    
                        		</div><!-- .block-cards-request -->
                		
                		        <div class="view-allCards-request"><?php esc_html_e( 'view all', 'argon-shop-theme' ); ?></div>
                		        
                		    </div>
                		</div><!-- .block-request -->				    
                		
						    
						<?php } ?>
						
						
						<?php 
						// ============================================
						// SECOND DESCRIPTION (ACF second_desc)
						// ============================================
						
						$second_desc = '';
						
						if ( function_exists( 'get_field' ) ) {
						    $second_desc = get_field( 'second_desc', 'catalog_' . $current_id );
						}
						
						if ( $second_desc ) { ?>
							<div id="topTwoDescr"><?php echo $second_desc; ?></div>
						<?php } ?>
					
					
					<?php 
					// ============================================
					// PRODUCT FILTER (if enabled)
					// ============================================
					
					if ( function_exists( 'wp_product_filter' ) ) { ?>
							<div id="productFilter">
								<?php wp_product_filter(); ?>
							</div>
					<?php } ?>
					
					
					
					<?php 
					// ============================================
					// POSTS OUTPUT
					// ============================================
					
					if ( have_posts() ) { 
					    
					    $pageNum = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
					    $catID   = absint( get_queried_object_id() );
					    
					    // Argon Shop plugin function (includes/api/getData/getData.php)
					    $typeSort = function_exists( 'as_getCookie' ) ? as_getCookie( 'AS_CatalogSorting' ) : false;
					    
					    $settingSort = array(
					        
					        'baseSort' => array(
					            'typeSort' => 'date',
					            'howSort'  => 'DESC',
					        ),
					            
					        'typesSort' => array(
					            'price' => array( 'name' => _x( 'Price', 'sort by', 'argon-shop-theme' ) ),
					            'name'  => array( 'name' => __( 'Name', 'argon-shop-theme' ) ),
					            'date'  => array( 'name' => __( 'Date added', 'argon-shop-theme' ) ),
					        ),
					    );
					    
					    ?>
					    
					    <div class="block-catalogSort">
					        
					        <div class="catalogPage_openMobile titleCatalogSort"><?php esc_html_e( 'Sort by:', 'argon-shop-theme' ); ?></div>
					        
					        <span class="catalogPage_mobileExtensible">
					        
					            <?php 
					            // Argon Shop plugin function (includes/interface/catalog/sort.php)
					            if ( function_exists( 'as_sort' ) ) {
					                as_sort( $settingSort, $catID, $pageNum );
					            }
					            ?>
					        
					        </span>
					        
					        <div class="as-error-sort"></div>
					        
					    </div>
					    
					    <div class="catalogProductList"> 
					    
					        <?php 
					        
					        // Argon Shop plugin function (includes/api/getData/product/productLists/catalog.php)
					        $productsList = false;
					        
					        if ( function_exists( 'as_get_sort_products' ) ) {
					            
    					        if ( $typeSort && isset( $typeSort['typeSort'], $typeSort['howSort'] ) ) {
    					            
    					            $productsList = as_get_sort_products( $typeSort['typeSort'], $typeSort['howSort'], $catID, $pageNum );
    					            
    					        } else {
    					            
    					            $productsList = as_get_sort_products( 'date', 'DESC', $catID, $pageNum );
    					        }
					        }
					        
					        // Argon Shop plugin function (includes/api/view/product/productsLists/productsLists.php)
					        if ( $productsList && function_exists( 'as_view_products_list' ) ) {
					            as_view_products_list( $productsList );
					        }
					        ?> 
    					
    					 </div> 
    					
					<?php } else {
					    
					    echo __( '<p>The category is empty.</p>', 'argon-shop-theme' );
					    
					} ?>
					    
					    <div class="descr">
							<?php 
							// ============================================
							// BOTTOM DESCRIPTION (ACF bottom_desc)
							// ============================================
							
							$bottom_desc = '';
							
							if ( function_exists( 'get_field' ) ) {
							    $bottom_desc = get_field( 'bottom_desc', 'catalog_' . $current_id );
							}
							
							if ( $bottom_desc ) {
							    echo wpautop( $bottom_desc );
							}
							?>
						</div><!-- .descr -->
					
					     
			<?php 
			// Argon Shop plugin function (includes/interface/pagenavi.php)
			if ( function_exists( 'as_kama_pagenavi' ) ) {
			    as_kama_pagenavi();
			}
			?>	
			</div><!-- #production_list_page -->

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>