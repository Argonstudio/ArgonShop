<?php
/**
 * Шаблон таксономии "Каталог"
 * 
 * Шаблон для вывода категории каталога товаров
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0 * 
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (через kama_breadcrumbs)
 * 2. Заголовок категории (или кастомный через ACF second_title)
 * 3. Описание категории (term_description)
 * 4. Дочерние категории карточками
 * 5. Популярные запросы (карточки)
 * 6. Второе описание (ACF second_desc)
 * 7. Фильтр товаров (если подключен)
 * 8. Сортировку товаров
 * 9. Список товаров категории
 * 10. Нижнее описание (ACF bottom_desc)
 * 11. Пагинацию
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * В этом шаблоне используются функции плагина:
 * 
 * - kama_breadcrumbs()     → хлебные крошки (breadcrumbs.php)
 * - kama_pagenavi()        → пагинация (pagenavi.php)
 * - getCookie()            → чтение куки сортировки (getData.php)
 * - as_sort()              → вывод сортировки (sort.php)
 * - get_sort_products()    → сортировка товаров (catalog.php)
 * - view_products_list()   → вывод списка товаров (productsLists.php)
 * 
 * ВНЕШНИЕ ЗАВИСИМОСТИ:
 * - ACF (Advanced Custom Fields) — поля second_title, second_desc, bottom_desc, image_catalog, catalog_type
 * - wp_product_filter() — функция фильтрации (если установлена)
 * 
 * ============================================================
 *
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================
// ПОЛУЧЕНИЕ ДАННЫХ О ТЕКУЩЕЙ КАТЕГОРИИ
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
            // ХЛЕБНЫЕ КРОШКИ
            // ============================================
            // Функция плагина Argon Shop (includes/interface/breadcrumbs.php)
            
            if ( function_exists( 'kama_breadcrumbs' ) ) {
                kama_breadcrumbs( '<span class="b"> » </span>' );
            }
            ?>

            <div id="production_list_page" class="white_block">

					<h1 class="title"> 
						<?php 
						// ============================================
						// ЗАГОЛОВОК КАТЕГОРИИ
						// ============================================
						// Если у категории задано ACF-поле second_title — выводим его.
						// Иначе — стандартный заголовок термина.
						
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

                    // PHP 8.5: $_GET всегда массив, но проверим на всякий случай
                    $get_is_empty = ! is_array( $_GET ) || empty( $_GET );
                    
                    if ( $termDescription && $get_is_empty ) { ?>
                        
                        <div class="catalogPage_openMobile mobileOpen-topDescr">Описание категории</div>
                            
                        <div class="catalogPage_mobileExtensible topDescr"><?php echo $termDescription; ?></div>
                     
                    <?php } ?>
					
					<?php 
					
					// ============================================
					// ДОЧЕРНИЕ КАТЕГОРИИ И ПОПУЛЯРНЫЕ ЗАПРОСЫ
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
							
						<div class="catalogPage_openMobile mobileOpen-cardsCategory">Дочерние категории</div>
							
						<div class="catalogPage_mobileExtensible block-cards-category">
							    
    						<?php foreach ( $terms as $term ) { $i++; ?>
    							
    							<?php 
    							
    							// PHP 8.5: get_field может вернуть false/null/массив
    							$image = function_exists( 'get_field' ) ? get_field( 'image_catalog', $term ) : false;
    							$type  = function_exists( 'get_field' ) ? get_field( 'catalog_type', $term ) : false;
    							
    							// Извлекаем URL из массива ACF
    							$image_url = '';
    							
    							if ( is_array( $image ) && isset( $image['url'] ) ) {
    							    $image_url = $image['url'];
    							} elseif ( is_string( $image ) ) {
    							    $image_url = $image;
    							}
    							
    							if ( $type === 'base' ) {
    								
    								// Ссылка на термин
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
						        
            				<div class="catalogPage_openMobile titleCardsRequest">Популярные запросы</div>
            				
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
                		
                		        <div class="view-allCards-request">смотреть все</div>
                		        
                		    </div>
                		</div><!-- .block-request -->				    
                		
						    
						<?php } ?>
						
						
						<?php 
						// ============================================
						// ВТОРОЕ ОПИСАНИЕ (ACF second_desc)
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
					// ФИЛЬТР ТОВАРОВ (если подключен)
					// ============================================
					
					if ( function_exists( 'wp_product_filter' ) ) { ?>
							<div id="productFilter">
								<?php wp_product_filter(); ?>
							</div>
					<?php } ?>
					
					
					
					<?php 
					// ============================================
					// ВЫВОД ПОСТОВ
					// ============================================
					
					if ( have_posts() ) { 
					    
					    $pageNum = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
					    $catID   = absint( get_queried_object_id() );
					    
					    // Функция плагина Argon Shop (includes/api/getData/getData.php)
					    $typeSort = function_exists( 'getCookie' ) ? getCookie( 'AS_CatalogSorting' ) : false;
					    
					    $settingSort = array(
					        
					        'baseSort' => array(
					            'typeSort' => 'date',
					            'howSort'  => 'DESC',
					        ),
					            
					        'typesSort' => array(
					            'price' => array( 'name' => 'Цене' ),
					            'name'  => array( 'name' => 'Названию' ),
					            'date'  => array( 'name' => 'Дате добавления' ),
					        ),
					    );
					    
					    ?>
					    
					    <div class="block-catalogSort">
					        
					        <div class="catalogPage_openMobile titleCatalogSort">Сортировать по:</div>
					        
					        <span class="catalogPage_mobileExtensible">
					        
					            <?php 
					            // Функция плагина Argon Shop (includes/interface/catalog/sort.php)
					            if ( function_exists( 'as_sort' ) ) {
					                as_sort( $settingSort, $catID, $pageNum );
					            }
					            ?>
					        
					        </span>
					        
					        <div class="as-error-sort"></div>
					        
					    </div>
					    
					    <div class="catalogProductList"> 
					    
					        <?php 
					        
					        // Функция плагина Argon Shop (includes/api/getData/product/productLists/catalog.php)
					        $productsList = false;
					        
					        if ( function_exists( 'get_sort_products' ) ) {
					            
    					        if ( $typeSort && isset( $typeSort['typeSort'], $typeSort['howSort'] ) ) {
    					            
    					            $productsList = get_sort_products( $typeSort['typeSort'], $typeSort['howSort'], $catID, $pageNum );
    					            
    					        } else {
    					            
    					            $productsList = get_sort_products( 'date', 'DESC', $catID, $pageNum );
    					        }
					        }
					        
					        // Функция плагина Argon Shop (includes/api/view/product/productsLists/productsLists.php)
					        if ( $productsList && function_exists( 'view_products_list' ) ) {
					            view_products_list( $productsList );
					        }
					        ?> 
    					
    					 </div> 
    					
					<?php } else {
					    
					    echo '<p>Категория пуста.</p>';
					    
					} ?>
					    
					    <div class="descr">
							<?php 
							// ============================================
							// НИЖНЕЕ ОПИСАНИЕ (ACF bottom_desc)
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
			// Функция плагина Argon Shop (includes/interface/pagenavi.php)
			if ( function_exists( 'kama_pagenavi' ) ) {
			    kama_pagenavi();
			}
			?>	
			</div><!-- #production_list_page -->

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>