<?php
/**
 * Шаблон категории «Акции и скидки» (Москва)
 * 
 * Шаблон для категории со слагом «aktsii-moskva»
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница категории «Акции и скидки»
 * URL: /category/aktsii-moskva/
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (через kama_breadcrumbs из плагина Argon Shop)
 * 2. Заголовок категории
 * 3. Список активных акций (post_type = post, promo_active = 1, category = 36)
 * 4. Ссылка на архив акций (если в архиве есть записи)
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Этот шаблон использует следующие функции плагина:
 * 
 * - as_kama_breadcrumbs() → хлебные крошки (includes/interface/breadcrumbs.php)
 * - as_kama_pagenavi()    → пагинация (includes/interface/pagenavi.php)
 *  
 * ВНЕШНИЕ ЗАВИСИМОСТИ:
 * - Категория с ID 36 = «Акции и скидки»
 * - Мета-поле promo_active = 1 (акция активна), 0 (архивная)
 * 
 * ============================================================
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentPage block-contentRubric" id="block-contentPromo">
            
            <?php 
            // ============================================
            // ХЛЕБНЫЕ КРОШКИ
            // ============================================
            // 
            // Функция плагина Argon Shop (includes/interface/breadcrumbs.php)
            // 
            // Выводит: Главная » Акции и скидки
            // с микроданными schema.org (BreadcrumbList)
            // 
            // Стандартный разделитель ' » ' оборачивается
            // в <span class="kb_sep">...</span> самой функцией.
            
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( ' » ' );
            }
            ?>
            
            <h1 class="title"><?php esc_html_e( 'Promotions and discounts', 'argon-shop-theme' ); ?></h1>
            
            <div class="block-rubricEntrys">
            
            <?php 
            
            // ============================================
            // АКТИВНЫЕ АКЦИИ
            // ============================================
            
            $arg = array(
                'post_type'      => 'post',
                'posts_per_page' => -1,
                
                'meta_query' => array(
                    array(
                        'key'     => 'promo_active',
                        'value'   => 1,
                        'compare' => '==',
                    ),
                ),
                
                'tax_query' => array(
                    array(
                        'taxonomy' => 'category',
                        'field'    => 'id',
                        'terms'    => 36,
                    ),
                ),
            );
            
            $promoList = new WP_Query( $arg );
            
            if ( $promoList instanceof WP_Query && $promoList->have_posts() ) {
                
                while ( $promoList->have_posts() ) : $promoList->the_post();
                    
                    $imgEntry = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
                    
                    ?>
                    
                    <div class="block-entry">
                        
                        <div class="block-entryImage">
                            
                            <?php if ( $imgEntry ) : ?>
                                
                                <a href="<?php the_permalink(); ?>">
                                    
                                    <img src="<?php echo esc_url( $imgEntry ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
                                    
                                </a>
                                
                            <?php endif; ?>
                            
                        </div>
                        
                        <div class="block-entryPreview">
                            
                            <div class="entryName">
                                <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                            </div>
                            
                            <div class="entryDescr">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <div class="entryButtons">
                                <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php esc_html_e( 'Learn more', 'argon-shop-theme' ); ?></a>
                            </div>
                            
                        </div>
                        
                    </div>
                    
                    <?php 
                    
                endwhile;
                
                // PHP 8.5: сбрасываем глобальную $post после цикла
                wp_reset_postdata();
                
            } else {
                
                echo __( 'No current promotions', 'argon-shop-theme' );
            }
            ?>
            
            <?php 
            // Функция плагина Argon Shop (includes/interface/pagenavi.php)
            // 
            // ПРИМЕЧАНИЕ: posts_per_page => -1 означает одну страницу.
            // Пагинация не имеет смысла — as_kama_pagenavi() возвращает false.
            // Оставлено для совместимости на случай изменения posts_per_page в будущем.
            if ( function_exists( 'as_kama_pagenavi' ) ) {
                as_kama_pagenavi();
            }
            ?>
            
            </div>
            
            
            <?php 
            
            // ============================================
            // АРХИВ АКЦИЙ (проверяем, есть ли записи)
            // ============================================
            
            $argArchive = array(
                'post_type'      => 'post',
                'posts_per_page' => -1,
                
                'meta_query' => array(
                    array(
                        'key'   => 'promo_active',
                        'value' => 0,
                    ),
                ),
                
                'tax_query' => array(
                    array(
                        'taxonomy' => 'category',
                        'field'    => 'id',
                        'terms'    => 36,
                    ),
                ),
            );
            
            $promoListArchive = new WP_Query( $argArchive );
            
            if ( $promoListArchive instanceof WP_Query && $promoListArchive->have_posts() ) {
                ?>
                
                <div class="block-archive">
                    
                    <a href="/arhiv-aktsij-moskva/"><?php esc_html_e( 'Promotions archive', 'argon-shop-theme' ); ?></a>
                    
                </div>
                
                <?php 
            }
            
            wp_reset_postdata();
            
            ?>

        </main>

    </div>
</section>

<?php get_footer(); ?>