<?php
/**
 * Шаблон отдельной записи категории «Акции и скидки» (Москва)
 * 
 * Шаблон вывода отдельной акции из категории со слагом «aktsii-moskva»
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница отдельной акции
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (ручная разметка со ссылкой на категорию)
 * 2. Заголовок акции
 * 3. Изображение записи
 * 4. Содержимое акции
 * 5. Плашка «Акция в архиве» (если promo_active = 0)
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Этот шаблон не вызывает функции плагина напрямую.
 * 
 * Хлебные крошки выводятся ручной разметкой (не через kama_breadcrumbs),
 * потому что вторая ссылка должна вести на категорию записи — плагин
 * не строит такую цепочку для отдельных записей автоматически.
 * 
 * ВНЕШНИЕ ЗАВИСИМОСТИ:
 * - Мета-поле promo_active — определяет, активна ли акция (1) или в архиве (0)
 * - Категория акций — ID 36 (Акции и скидки)
 */

// Запрет прямого доступа к файлу
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
            // КАТЕГОРИЯ ЗАПИСИ И ЕЁ ССЫЛКА
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
            
            <!-- Хлебные крошки (ручная разметка со ссылкой на категорию) -->
            <div class="kama_breadcrumbs" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    <a href="<?php echo esc_url( get_home_url() ); ?>" itemprop="item"><span itemprop="name"><?php esc_html_e( 'Home', 'argon-shop-theme' ); ?></span></a>
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
                // ПЛАШКА «АКЦИЯ В АРХИВЕ»
                // ============================================
                // 
                // Если мета-поле promo_active пусто или равно 0, акция в архиве.
                
                $promoMeta = get_post_meta( get_the_ID(), 'promo_active', true );
                
                if ( empty( $promoMeta ) ) { ?>
                    
                    <div class="block-promoArchive"><?php esc_html_e( 'Promotion archived', 'argon-shop-theme' ); ?></div>
                    
                <?php } ?>
                
            </div>
            <?php endwhile; endif; ?>
        </main>

    </div>
</section>


<?php get_footer(); ?>