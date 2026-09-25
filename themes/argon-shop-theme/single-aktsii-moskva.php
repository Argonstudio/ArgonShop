<?php
/**
 * Шаблон одиночной записи рубрики "Акции и скидки" (Москва)
 * 
 * Шаблон для вывода отдельной акции из рубрики category с slug "aktsii-moskva"
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница отдельной акции
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (ручная разметка с ссылкой на рубрику)
 * 2. Заголовок акции
 * 3. Изображение записи
 * 4. Контент акции
 * 5. Плашку "Акция в архиве" (если promo_active = 0)
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * В этом шаблоне функции плагина не вызываются напрямую.
 * 
 * Хлебные крошки выводятся ручной разметкой (не через kama_breadcrumbs),
 * так как вторая ссылка должна вести на рубрику записи — плагин
 * не строит такую цепочку автоматически для одиночных записей.
 * 
 * ВНЕШНИЕ ЗАВИСИМОСТИ:
 * - Мета-поле promo_active — определяет, активна ли акция (1) или в архиве (0)
 * - Рубрика акций — ID 36 (Акции и скидки)
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentPage block-entryPage" id="block-contentPromoPage">
            
            
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
            <?php 
            
            // ============================================
            // РУБРИКА ЗАПИСИ И ССЫЛКА НА НЕЁ
            // ============================================
            // 
            // Используется для построения второй ссылки в хлебных крошках.
            
            $categoryPost  = get_the_category();
            $categoryLink  = '';
            $category_name = '';
            
            if ( ! empty( $categoryPost ) && isset( $categoryPost[0] ) ) {
                
                $categoryLink = get_category_link( $categoryPost[0]->term_id );
                $category_name = isset( $categoryPost[0]->name ) ? $categoryPost[0]->name : '';
                
                if ( is_wp_error( $categoryLink ) ) {
                    $categoryLink = '#';
                }
            }
            
            ?>
            
            <!-- Хлебные крошки (ручная разметка с ссылкой на рубрику) -->
            <div class="kama_breadcrumbs" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    <a href="<?php echo esc_url( get_home_url() ); ?>" itemprop="item"><span itemprop="name">Главная</span></a>
                </span>
                
                <span class="kb_sep"> » </span>
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    <a href="<?php echo esc_url( $categoryLink ); ?>" itemprop="item"><span itemprop="name"><?php echo esc_html( $category_name ); ?></span></a>
                </span>
                
                <span class="kb_sep"> » </span>
                
                <span class="kb_title"><?php the_title(); ?></span>
                
            </div>
            
            <h1 class="title"><?php the_title(); ?></h1>
            
            <div class="block-entryContent">
                
                <?php if ( has_post_thumbnail() ) { ?>
                    <div class="block-entryImage">
                        <?php the_post_thumbnail( '', array( 'class' => '' ) ); ?>
                    </div>
                <?php } ?>
                
                <div class="block-entryText">
                    <?php the_content(); ?>
                </div>
                
                <?php
                
                // ============================================
                // ПЛАШКА "АКЦИЯ В АРХИВЕ"
                // ============================================
                // 
                // Если мета-поле promo_active пустое или 0 — акция в архиве.
                
                $promoMeta = get_post_meta( get_the_ID(), 'promo_active', true );
                
                if ( empty( $promoMeta ) ) { ?>
                    
                    <div class="block-promoArchive">Акция в архиве</div>
                    
                <?php } ?>
                
            </div>
            <?php endwhile; endif; ?>
        </main>

    </div>
</section>


<?php get_footer(); ?>