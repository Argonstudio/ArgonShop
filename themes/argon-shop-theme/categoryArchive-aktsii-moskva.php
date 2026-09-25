<?php
/**
 * Template Name: Архив акций Москва
 * 
 * Шаблон страницы "Архив акций Москва"
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0 
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки
 * 2. Заголовок "Архив акций"
 * 3. Список архивных акций (post_type = post, promo_active = 0, category = 36)
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * В этом шаблоне используются функции плагина:
 * 
 * - kama_pagenavi()    → пагинация (includes/interface/pagenavi.php) * 
 * 
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentPage block-contentRubric" id="block-contentPromoArchive">
            
            <?php 
            // ============================================
            // ХЛЕБНЫЕ КРОШКИ
            // ============================================
            ?>
            
            <div class="kama_breadcrumbs" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    <a href="<?php echo esc_url( get_home_url() ); ?>" itemprop="item"><span itemprop="name">Главная</span></a>
                </span>
                
                <span class="kb_sep"> » </span>
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    <a href="/aktsii-moskva/" itemprop="item"><span itemprop="name">Акции и скидки</span></a>
                </span>
                
                <span class="kb_sep"> » </span>
                
                <span class="kb_title">Архив акций</span>
                
            </div>
           
            
            
            <h1 class="title">Архив акций</h1>
            
            <div class="block-rubricEntrys">
            
            <?php 
            
            // ============================================
            // АРХИВНЫЕ АКЦИИ
            // ============================================
            
            $arg = array(
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
                
                wp_reset_postdata();
                
            } else {
                
                echo 'Архив пуст';
            }
            ?>
            
            <?php 
            // Функция плагина Argon Shop (includes/interface/pagenavi.php)
            // 
            // ВНИМАНИЕ: posts_per_page => -1 значит одна страница.
            //if ( function_exists( 'kama_pagenavi' ) ) {
                //kama_pagenavi();
            //}
            ?>
            
            </div>

        </main>

    </div>
</section>

<?php get_footer(); ?>