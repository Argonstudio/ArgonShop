<?php 

/**
 * Шаблон главной страницы темы ArgonShopTheme
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * @license GPL-3.0-or-later
 */

get_header(); ?>


<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
           
           <?php
            // Слайдер главной страницы.
            // Выводится функцией плагина ArgonShop (Swiper).
            // См. includes/interface/slider/sliderMain.php.
            if ( function_exists( 'as_slider_main' ) ) {
                as_slider_main();
            }
            ?>
            
            <div class="block-advantages">
                
                <div class="homepage-block-title"><?php esc_html_e( 'Our advantages', 'argon-shop-theme' ); ?></div>
                
                <div class="block-advantages-cards">
                
                    <div class="card-advantage twentyYear">
                        
                        <div class="advantage-img"><span>+</span></div>
                        <div class="advantage-title"><?php esc_html_e( '20 years', 'argon-shop-theme' ); ?></div>
                        <div class="advantage-descr"><?php esc_html_e( 'in the building materials market', 'argon-shop-theme' ); ?></div>
                        
                    </div>
                    
                    <div class="card-advantage assortment">
                        
                        <div class="advantage-img"><span>+</span></div>
                        <div class="advantage-title"><?php esc_html_e( 'Product range', 'argon-shop-theme' ); ?></div>
                        <div class="advantage-descr"><?php esc_html_e( 'More than 60 thousand products', 'argon-shop-theme' ); ?></div>
                        
                    </div>
                    
                    <div class="card-advantage trust">
                        
                        <div class="advantage-img"><span>+</span></div>
                        <div class="advantage-title"><?php esc_html_e( 'Trusted by', 'argon-shop-theme' ); ?></div>
                        <div class="advantage-descr"><?php esc_html_e( 'More than a thousand customers a month', 'argon-shop-theme' ); ?> </div>
                        
                    </div>
                    
                    <div class="card-advantage autopark">
                        
                        <div class="advantage-img"><span>+</span></div>
                        <div class="advantage-title"><?php esc_html_e( 'Fleet', 'argon-shop-theme' ); ?></div>
                        <div class="advantage-descr"><?php esc_html_e( '12 trucks and crane trucks, delivery across the Moscow region', 'argon-shop-theme' ); ?></div>
                        
                    </div>
                
                </div>
                
            </div>
            
            <div class="block-homePageCatalog">
                
                <div class="homepage-block-title"><?php esc_html_e( 'Building materials catalog', 'argon-shop-theme' ); ?></div>
                
                <?php 
                
                $catalog = as_get_catalog_terms();
                
                if ( ! is_array( $catalog ) ) {
                    $catalog = array();
                }
                
                $catalogFirstLevelChild = count( $catalog );
                $catalogSecondLevelChild = 0;
                
                foreach ( $catalog as $key => $value ) {
                    
                    // PHP 8.5: проверки вложенных ключей
                    if ( isset( $value['data']['firstLevelChild'] ) ) {
                        $catalogSecondLevelChild += absint( $value['data']['firstLevelChild'] );
                    }
                }
                
                // ============================================
                // БЛОК «АКЦИИ И СКИДКИ»
                // ============================================
                
                $promoDiscontArgs = array( 
                    'numberposts' => 10, 
                    'category'    => 36,
                    'meta_query'  => array(
                        array(
                            'key'     => 'promo_active',
                            'value'   => 1,
                            'compare' => '==',
                        ),
                    ),
                );
                
                $promoDiscont = get_posts( $promoDiscontArgs );
                
                if ( ! is_array( $promoDiscont ) ) {
                    $promoDiscont = array();
                }
                
                $countPromoDiscont = count( $promoDiscont );
                
                $countPromoDiscont++;
                
                // Разбиваем каталог на 3 колонки + передаём количество значений в блоке акций
                $splitMenuQuery = as_splitMenu( $catalog, 3, $catalogFirstLevelChild, $catalogSecondLevelChild, 3, 5, $countPromoDiscont );
                
                if ( ! is_array( $splitMenuQuery ) || ! isset( $splitMenuQuery[0] ) || ! isset( $splitMenuQuery[1] ) ) {
                    $splitMenu = array();
                    $splitMenuCountsList = array();
                } else {
                    $splitMenu = $splitMenuQuery[0];
                    $splitMenuCountsList = $splitMenuQuery[1];
                }
                
                if ( ! is_array( $splitMenu ) ) {
                    $splitMenu = array();
                }
                
                if ( ! is_array( $splitMenuCountsList ) ) {
                    $splitMenuCountsList = array();
                }
                
                // Находим самую короткую колонку
                $leactList = null;
                
                if ( ! empty( $splitMenuCountsList ) ) {
                    $min_value = min( $splitMenuCountsList );
                    $leactList_keys = array_keys( $splitMenuCountsList, $min_value );
                    
                    if ( ! empty( $leactList_keys ) && isset( $leactList_keys[0] ) ) {
                        $leactList = $leactList_keys[0];
                    }
                }
                
                // ============================================
                // ДОБАВЛЯЕМ «АКЦИИ И СКИДКИ» В САМУЮ КОРОТКУЮ КОЛОНКУ
                // ============================================
                
                if ( $leactList !== null && isset( $splitMenu[ $leactList ] ) ) {
                    
                    // Убеждаемся, что $splitMenu[$leactList] — массив
                    if ( ! is_array( $splitMenu[ $leactList ] ) ) {
                        $splitMenu[ $leactList ] = array();
                    }
                    
                    $splitMenu[ $leactList ][36]['data'] = array(
                        'term_id' => 36,
                        'name'    => __( 'Promotions and discounts', 'argon-shop-theme' ),
                        'class'   => 'catalog-promoDiscontLink',
                    );
                    
                    if ( ! isset( $splitMenu[ $leactList ][36]['children'] ) || ! is_array( $splitMenu[ $leactList ][36]['children'] ) ) {
                        $splitMenu[ $leactList ][36]['children'] = array();
                    }
                    
                    foreach ( $promoDiscont as $key => $value ) {
                        
                        if ( ! is_object( $value ) || ! isset( $value->ID ) ) {
                            continue;
                        }
                        
                        $post_id = absint( $value->ID );
                        
                        if ( $post_id === 0 ) {
                            continue;
                        }
                        
                        $splitMenu[ $leactList ][36]['children'][ $post_id ]['data'] = array(
                            'post_id' => $post_id,
                            'name'    => $value->post_title,
                        );
                    }
                }
                
                // ============================================
                // ВЫВОД КОЛОНОК КАТАЛОГА
                // ============================================
                
                foreach ( $splitMenu as $subMenuID => $subMenuValue ) {
                    
                    if ( ! empty( $subMenuValue ) && is_array( $subMenuValue ) ) {
                        as_view_menu_elements( $subMenuValue, 'homePageCatalogList', 2 );
                    }
                }
                
                ?>
                
            </div>
            
            
        </main>

    </div>
</section>

<?php get_footer(); ?>