<?php
/**
 * Шаблон рубрики "Акции и скидки" (Москва)
 * 
 * Шаблон для рубрики category с slug "aktsii-moskva"
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.1
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница рубрики "Акции и скидки"
 * URL: /category/aktsii-moskva/
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (через kama_breadcrumbs из плагина Argon Shop)
 * 2. Заголовок рубрики
 * 3. Список активных акций (post_type = post, promo_active = 1, category = 36)
 * 4. Ссылку на архив акций (если в архиве есть посты)
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * В этом шаблоне используются функции плагина:
 * 
 * - kama_breadcrumbs() → хлебные крошки (includes/interface/breadcrumbs.php)
 * - kama_pagenavi()    → пагинация (includes/interface/pagenavi.php)
 *  
 * ВНЕШНИЕ ЗАВИСИМОСТИ:
 * - ID рубрики 36 = "Акции и скидки"
 * - Мета-поле promo_active = 1 (акция активна), 0 (в архиве)
 * 
 * ============================================================
 */

// Защита от прямого вызова файла
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
            // с микроразметкой schema.org (BreadcrumbList)
            // 
            // Разделитель по умолчанию — ' » ' оборачивается 
            // в <span class="kb_sep">...</span> самой функцией.
            
            if ( function_exists( 'kama_breadcrumbs' ) ) {
                kama_breadcrumbs( ' » ' );
            }
            ?>
            
            <h1 class="title">Акции и скидки</h1>
            
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
                                <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">Узнать больше</a>
                            </div>
                            
                        </div>
                        
                    </div>
                    
                    <?php 
                    
                endwhile;
                
                // PHP 8.5: сброс глобального $post после цикла
                wp_reset_postdata();
                
            } else {
                
                echo 'Актуальных акций нет';
            }
            ?>
            
            <?php 
            // Функция плагина Argon Shop (includes/interface/pagenavi.php)
            // 
            // ВНИМАНИЕ: posts_per_page => -1 значит одна страница.
            // Пагинация не имеет смысла — kama_pagenavi() вернёт false.
            // Оставлено для совместимости, если в будущем posts_per_page изменится.
            if ( function_exists( 'kama_pagenavi' ) ) {
                kama_pagenavi();
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
                    
                    <a href="/arhiv-aktsij-moskva/">Архив акций</a>
                    
                </div>
                
                <?php 
            }
            
            wp_reset_postdata();
            
            ?>

        </main>

    </div>
</section>

<?php get_footer(); ?>