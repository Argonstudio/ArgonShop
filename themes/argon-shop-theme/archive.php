<?php
/**
 * Category archive template (news, articles)
 * 
 * Template for displaying posts in categories, including:
 * - News
 * - Articles
 * - Child promotion categories
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * Category pages: /category/novosti/, /category/stati/, etc.
 * 
 * WHAT IT OUTPUTS:
 * 1. Breadcrumbs (via kama_breadcrumbs from the Argon Shop plugin)
 * 2. Category title
 * 3. Child categories block (if any)
 * 4. Tag cloud (only for the category with ID = 37)
 * 5. List of category posts as cards
 * 6. Pagination
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template uses the following plugin functions:
 * 
 * - as_kama_breadcrumbs() → breadcrumbs (includes/interface/breadcrumbs.php)
 * - as_kama_pagenavi()    → pagination (includes/interface/pagenavi.php)
 *  * 
 * EXTERNAL DEPENDENCIES:
 * - ACF (Advanced Custom Fields) — for the image_catalog field of categories
 * - Category ID 37 — "Articles" (the category where the tag cloud is shown)
 * 
 * ============================================================
 *
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

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

?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentRubric">
            
            <?php 
            // ============================================
            // BREADCRUMBS
            // ============================================
            // 
            // Argon Shop plugin function (includes/interface/breadcrumbs.php)
            // Automatically builds the chain: Home » Parent » Current category
            
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( ' » ' );
            }
            ?>

            <h1 class="title"><?php single_cat_title(); ?></h1>
            
            
            <?php 
            
            // ============================================
            // CHILD CATEGORIES
            // ============================================
            
            $terms = get_terms( array(
                'taxonomy'   => 'category',
                'hide_empty' => false,
                'parent'     => $current_id,
            ) );
            
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                
                $count = count( $terms );
                $i = 0;
                ?>
                    
                <div class="itemControlPanel" data-type="openingList" name="childRubric"><?php esc_html_e( 'Subcategories', 'argon-shop-theme' ); ?></div>
                    
                <div class="blockItemPage block-cards-childRubric" id="block_childRubric">
                        
                    <?php foreach ( $terms as $term ) { ?>
                        
                        <?php 
                        // ============================================
                        // CATEGORY IMAGE (ACF field)
                        // ============================================
                        // 
                        
                        $image = function_exists( 'get_field' ) ? get_field( 'image_catalog', $term ) : false;
                        
                        $image_url = '';
                        
                        if ( is_array( $image ) && isset( $image['url'] ) ) {
                            $image_url = $image['url'];
                        } elseif ( is_string( $image ) ) {
                            // If ACF returned just a URL string
                            $image_url = $image;
                        }
                        
                        // Category link
                        $term_link = get_term_link( $term );
                        
                        if ( is_wp_error( $term_link ) ) {
                            $term_link = '#';
                        }
                        
                        $term_name = isset( $term->name ) ? $term->name : '';
                        ?>
                        
                        <span class="block-card-childRubric">
                            
                            <a href="<?php echo esc_url( $term_link ); ?>" 
                               class="card-childRubric<?php echo $image_url ? '' : ' card-childRubric-notImg'; ?>" 
                               style="background-image:url(<?php echo esc_url( $image_url ); ?>)">
                                
                                <span><?php echo esc_html( $term_name ); ?></span>
                                
                            </a>
                            
                        </span>
                        
                    <?php } ?>
                        
                     </div><!-- .block-cards-childRubric -->
                    
                <?php } ?>
                
                <?php 
                
                // ============================================
                // TAG CLOUD (only for the category with ID = 37)
                // ============================================
                
                if ( $current_id === 37 ) {
    
                    if ( function_exists( 'wp_tag_cloud' ) ) {
                        
                        $tag_args = array(
                            'smallest'                  => 11,
                            'largest'                   => 18,
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
                        
                        <div class="itemControlPanel itemControlPanel-tags" data-type="openingList" name="entryTags"><?php esc_html_e( 'Tag cloud', 'argon-shop-theme' ); ?></div>
                           
                        <div class="blockItemPage block-mobileEntryTags" id="block_entryTags">
                            
                             <?php wp_tag_cloud( $tag_args ); ?>
                        
                        </div>
                        
                        <?php
                    }
                }
                
                ?>
            
            <div class="block-rubricEntrys">
                
                <?php 
                // Standard WordPress loop
                if ( have_posts() ) { 
                
                    while ( have_posts() ) : the_post(); 
                    
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
                                <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php esc_html_e( 'Read more', 'argon-shop-theme' ); ?></a>
                            </div>
                            
                        </div>
                        
                    </div>
                    
                    <?php 
                    
                    endwhile;
                    
                    wp_reset_postdata();
                    
                } else {
                    
                    echo __( 'No posts', 'argon-shop-theme' );
                }
                
                ?>
                
                <?php 
                // Argon Shop plugin function (includes/interface/pagenavi.php)
                if ( function_exists( 'as_kama_pagenavi' ) ) {
                    as_kama_pagenavi();
                }
                ?>
                
            </div>

        </main>

    </div>
</section>

<?php get_footer(); ?>