<?php
/**
 * Сайдбар
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Все страницы сайта — вывод сайдбара
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Меню каталога товаров
 * 2. Облако тегов (для категорий новостей и статей и их потомков)
 * 3. Блоки «Вы смотрели», «Хиты продаж», «Новинки»
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Этот файл использует следующие функции плагина:
 * 
 * - as_get_catalog_terms()      → дерево каталога (catalog.php)
 * - as_splitMenu()              → разбиение меню на колонки (catalog.php)
 * - as_view_menu_elements()     → вывод меню (catalog.php)
 * - as_getCookie()              → чтение cookie корзины (getData.php)
 * - as_get_history_products()   → история просмотров (history.php)
 * - as_get_marked_products()    → хиты/новинки (marked.php)
 * - as_view_products_list()     → вывод списка товаров (productsLists.php)
 * 
 * ============================================================
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<aside id="aside" class="block-sidebar">
    
    <div class="openMobileCatalog"><?php esc_html_e( 'Product catalog', 'argon-shop-theme' ); ?></div>
    
    <?php 
    
    $blockMenuCatalogClass = ( ! is_front_page() ) ? 'block-menuCatalog' : 'block-menuCatalog block-menuCatalogHomepage'; 
    $blockLinkSitebarClass = ( ! is_front_page() ) ? 'block-linkSidebar' : 'block-linkSidebar block-linkSidebarHomepage'; 
    
    ?>
    
    <div class="<?php echo esc_attr( $blockMenuCatalogClass ); ?>">
        
        <?php
        // Функция плагина Argon Shop (includes/api/getData/catalog.php)
        $menuCatalog = as_get_catalog_terms();
        
        if ( ! is_array( $menuCatalog ) ) {
            $menuCatalog = array();
        }
        ?>
        
        <ul class="menuCatalog">
            
            <?php
            
            foreach ( $menuCatalog as $key => $value ) {
                
                if ( ! is_array( $value ) || ! isset( $value['data'] ) ) {
                    continue;
                }
                
                $term_id = isset( $value['data']['term_id'] ) ? absint( $value['data']['term_id'] ) : 0;
                $term_slug = isset( $value['data']['slug'] ) ? $value['data']['slug'] : '';
                $term_name = isset( $value['data']['name'] ) ? $value['data']['name'] : '';
                
                if ( $term_id === 0 ) {
                    continue;
                }
                
                // ============================================
                // ИКОНКА КАТЕГОРИИ (base64)
                // ============================================
                // 
                // get_attached_file() для получения локального пути.
                
                $imageID = get_option( 'catalog_' . $term_id . '_image_catalog' );
                $imageBase64 = '';
                
                if ( $imageID ) {
                    
                    // Получаем путь файла на диске (не URL)
                    $image_path = get_attached_file( $imageID );
                    
                    if ( $image_path && file_exists( $image_path ) ) {
                        
                        $image_content = @file_get_contents( $image_path );
                        
                        if ( $image_content !== false ) {
                            $imageBase64 = base64_encode( $image_content );
                        }
                    }
                }
                
                // Есть ли дочерние элементы
                $has_children = isset( $value['children'] ) && ! empty( $value['children'] ) && is_array( $value['children'] );
                
                // Фоновый стиль
                $style_attr = '';
                
                if ( $imageBase64 ) {
                    $style_attr = "background-image: url(data:image/png;base64," . $imageBase64 . ");";
                }
                
                ?>
                
                <li style="<?php echo esc_attr( $style_attr ); ?>" class="menuCatalogItem<?php echo $has_children ? ' itemHasChildren' : ''; ?>">
                    
                    <a href="/catalog/<?php echo esc_attr( $term_slug ); ?>">
                        <div class="block-textMenuUrl">
                             <span><?php echo esc_html( $term_name ); ?></span>
                        </div>
                    </a>
                
                    <?php
                    
                    if ( $has_children ) {
                        
                        $first_level_child  = isset( $value['data']['firstLevelChild'] ) ? absint( $value['data']['firstLevelChild'] ) : 0;
                        $second_level_child = isset( $value['data']['secondLevelChild'] ) ? absint( $value['data']['secondLevelChild'] ) : 0;
                        
                        ?>
                        
                        <div class="submenuCatalog">
                            
                         <?php 
                         
                            // Функция плагина Argon Shop (includes/api/getData/catalog.php)
                            $splitMenuQuery = as_splitMenu( $value['children'], 3, $first_level_child, $second_level_child );
                            
                            // PHP 8.5: проверяем результат
                            if ( is_array( $splitMenuQuery ) && isset( $splitMenuQuery[0] ) && is_array( $splitMenuQuery[0] ) ) {
                                
                                $splitMenu = $splitMenuQuery[0];
                                
                                foreach ( $splitMenu as $subMenuID => $subMenuValue ) {
                                     
                                     if ( ! empty( $subMenuValue ) && is_array( $subMenuValue ) ) {
                                         // Функция плагина Argon Shop (includes/api/getData/catalog.php)
                                         as_view_menu_elements( $subMenuValue, 'submenuCatalogUl', 3 );
                                     }
                                 }
                            }
                         
                         ?>
                         
                        </div>
                        
                        <?php
                    }
                    ?>
                
                </li>
                
                <?php
            }
            
            ?>
            
        </ul>
        
    </div>
    
    <div class="<?php echo esc_attr( $blockLinkSitebarClass ); ?>">
        
        <a href="/aktsii-moskva/" class="promoDiscontLink"><span>%</span><?php esc_html_e( 'Promotions and discounts', 'argon-shop-theme' ); ?></a>
        <a href="/history/" class="historyLink"><span></span><?php esc_html_e( 'View history', 'argon-shop-theme' ); ?></a>
        
    </div>
    
    
    <?php
    
    // ============================================
    // ОБЛАКО ТЕГОВ
    // ============================================
    
    $viewTag = false;
    
    $queried_object = get_queried_object();
    
    // PHP 8.5: проверяем, что объект существует и является объектом
    if ( $queried_object && is_object( $queried_object ) ) {
        
        $current_taxonomy = isset( $queried_object->taxonomy ) ? $queried_object->taxonomy : '';
        $current_postType = isset( $queried_object->post_type ) ? $queried_object->post_type : '';
        $current_id       = isset( $queried_object->term_id ) ? absint( $queried_object->term_id ) : 0;
        
        if ( $current_taxonomy === 'category' ) {
            
            if ( $current_id === 37 ) {
                
                $viewTag = true;
                
            } else {
                
                $ancestors = get_ancestors( $current_id, 'category' );
                
                if ( is_array( $ancestors ) ) {
                    foreach ( $ancestors as $ancestorID ) {
                        
                        if ( $ancestorID === 37 ) {
                            $viewTag = true;
                        }
                    }
                }
            }
            
        } elseif ( $current_taxonomy === 'post_tag' ) {
            
            $viewTag = true;
            
        } elseif ( $current_postType === 'post' ) {
            
            // PHP 8.5: проверяем, что функция as_post_in_term() существует
            if ( function_exists( 'as_post_in_term' ) && isset( $queried_object->ID ) ) {
                
                if ( as_post_in_term( array( 37, 1 ), 'category', $queried_object->ID ) ) {
                    $viewTag = true;
                }
            }
        }
    }
    
    if ( $viewTag ) {
        
        if ( function_exists( 'wp_tag_cloud' ) ) {
            
            $tag_args = array(
                'smallest'                  => 12,
                'largest'                   => 22,
                'unit'                      => 'px',
                'number'                    => 25,
                'format'                    => 'flat',
                'separator'                 => "\n",
                'orderby'                   => 'name',
                'order'                     => 'ASC',
                'exclude'                   => null,
                'include'                   => null,
                'link'                      => 'view',
                'taxonomy'                  => 'post_tag',
                'echo'                      => true,
                'topic_count_text_callback' => 'default_topic_count_text',
            );
            ?>
               
            <div class="block-entryTags">   
                
                <div class="h3 title-entry-tags"><?php esc_html_e( 'Tag cloud', 'argon-shop-theme' ); ?></div>
                
                 <?php wp_tag_cloud( $tag_args ); ?>
            
            </div>
            
            <?php
        }
    }
    
    ?>
    
    <div class="block-marked-lists">
        
    <?php 
    
    // Функция плагина Argon Shop (includes/api/getData/getData.php)
    $productsShoppingCart = as_getCookie( 'productsShoppingCart' );
    
    // Функция плагина Argon Shop (includes/api/getData/product/productLists/history.php)
    $history_products = as_get_history_products( 2 );
    
    // PHP 8.5: проверяем, что вернулся WP_Query
    if ( $history_products instanceof WP_Query && $history_products->have_posts() && ! is_page( 23 ) ) :
     
     ?>
         <div class="block-marked-products block-history">
             
            <div class="h3 title-marked-products"><?php esc_html_e( 'Recently viewed', 'argon-shop-theme' ); ?></div>
            
            <div class="list-marked-products">
        
                <?php 
                // Функция плагина Argon Shop (includes/api/view/product/productsLists/productsLists.php)
                as_view_products_list( $history_products ); 
                ?>
                
            </div>
            
            <a href="/history/" class="linkHistory"><?php esc_html_e( 'view history', 'argon-shop-theme' ); ?></a>
            
        </div>
        
    <?php endif; ?>
    
    
    <?php 
    
    /* ХИТЫ ПРОДАЖ */
    
    // Функция плагина Argon Shop (includes/api/getData/product/productLists/marked.php)
    $sidebar_hits = as_get_marked_products( 2, '_hit' );
            
     // PHP 8.5: проверяем, что вернулся WP_Query
     if ( $sidebar_hits instanceof WP_Query && $sidebar_hits->have_posts() ) :
     
     ?>
         <div class="block-marked-products block-hits">
             
            <div class="h3 title-marked-products"><?php esc_html_e( 'Bestsellers', 'argon-shop-theme' ); ?></div>
            
            <div class="list-marked-products">
        
                <?php 
                // Функция плагина Argon Shop
                as_view_products_list( $sidebar_hits ); 
                ?>
                
            </div>
            
        </div>
        
    <?php endif; ?>
    
    <?php
    
    /* НОВИНКИ */
    
    // Функция плагина Argon Shop
    $sidebar_newProducts = as_get_marked_products( 2, '_newProduct' );
            
     // PHP 8.5: проверяем, что вернулся WP_Query
     if ( $sidebar_newProducts instanceof WP_Query && $sidebar_newProducts->have_posts() ) :
     
     ?>
         <div class="block-marked-products block-newProducts">
             
            <div class="h3 title-marked-products"><?php esc_html_e( 'New arrivals', 'argon-shop-theme' ); ?></div>
            
            <div class="list-marked-products">
        
                <?php 
                // Функция плагина Argon Shop
                as_view_products_list( $sidebar_newProducts ); 
                ?>
                
            </div>
            
        </div>
        
    <?php endif; ?>
        
    </div>    
</aside>