<?php
/**
 * Template Name: About company
 * 
 * "About the company" page template
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * The "About the company" page
 * URL: /o-kompanii/ (or another one)
 * 
 * WHAT IT OUTPUTS:
 * 1. Breadcrumbs (via kama_breadcrumbs from the Argon Shop plugin)
 * 2. Page title and content
 * 3. The "Our advantages" block (4 static cards)
 * 4. The "About the advantages" text (ACF field our_advantages)
 * 5. The "Our partners" block (post type ourclients)
 * 6. The "About the clients" text (ACF field our_clients)
 * 7. The "Diplomas and certificates" block (post type sertificates)
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template uses the following plugin functions:
 * 
 * - as_kama_breadcrumbs() → breadcrumbs (includes/interface/breadcrumbs.php)
 * 
 * EXTERNAL DEPENDENCIES:
 * - ACF (Advanced Custom Fields) — our_advantages, our_clients fields
 * - Post types ourclients / sertificates (registered in the theme's functions.php)
 * - The client_link meta field of ourclients posts
 * - Swiper (from the ArgonShop plugin) — certificate slider
 * - GLightbox (from the ArgonShop plugin) — full-size certificate view
 * 
 * ============================================================
 
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentPage block-contentAboutCompany">

            <?php 
            // ============================================
            // BREADCRUMBS
            // ============================================
            // 
            // Argon Shop plugin function (includes/interface/breadcrumbs.php)
            // Outputs: Home » About the company
            
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( ' » ' );
            }
            ?>

            <?php 
            // Standard WordPress loop
            while ( have_posts() ) : the_post(); 
            ?>

            <div class="page">

                <h1 class="title titlePage"><?php the_title(); ?></h1>
                
                <?php the_content(); ?>
                
                <?php 
                // ============================================
                // "ABOUT THE ADVANTAGES" TEXT (ACF field)
                // ============================================
                // 
                
                $textOurAdvantages = '';
                
                if ( function_exists( 'get_field' ) ) {
                    $field_value = get_field( 'our_advantages', get_the_ID() );
                    
                    if ( $field_value ) {
                        $textOurAdvantages = wpautop( $field_value );
                    }
                }
                
                if ( $textOurAdvantages ) {
                    ?>
                    
                    <div class="text-ourAdvantages">
                        
                        <?php echo $textOurAdvantages; ?>
                        
                    </div>
                
                <?php } ?>
                
                
                <div class="block-ourClients">
                
                    <div class="aboutCompany-block-title"><?php esc_html_e( 'Our partners', 'argon-shop-theme' ); ?></div>
                    
                    <div class="block-logoClients">
                    
                        <?php
                        
                        // ============================================
                        // OUR PARTNERS (post type ourclients)
                        // ============================================
                        
                        $arg = array(
                            'post_status'    => 'publish',
                            'post_type'      => 'ourclients',
                            'posts_per_page' => -1,
                        );
                        
                        $clients = new WP_Query( $arg );
                        
                        if ( $clients instanceof WP_Query && $clients->have_posts() ) :
                            
                            while ( $clients->have_posts() ) : $clients->the_post();
                            
                                $linkClient = get_post_meta( get_the_ID(), 'client_link', true );
                                $linkClient_url = ( ! empty( $linkClient ) && is_string( $linkClient ) ) 
                                    ? esc_url( $linkClient ) 
                                    : '';
                                
                                ?>
                                
                                <div class="block-client">
                                    
                                    <?php if ( $linkClient_url ) : ?>
                                        <a href="<?php echo $linkClient_url; ?>" target="_blank" rel="noopener noreferrer">
                                    <?php endif; ?>
                                    
                                        <span><?php the_post_thumbnail( 'slider_thumb', array( 'class' => 'img-responsive' ) ); ?></span>
                                    
                                    <?php if ( $linkClient_url ) : ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                </div>
                                
                            <?php 
                            
                            endwhile; 
                            
                            wp_reset_postdata(); 
                            
                        endif;  
                        
                        ?>
                        
                    </div>
                    
                </div>
                
                <?php 
                // ============================================
                // "ABOUT THE CLIENTS" TEXT (ACF field)
                // ============================================
                
                $textOurClients = '';
                
                if ( function_exists( 'get_field' ) ) {
                    $field_value = get_field( 'our_clients', get_the_ID() );
                    
                    if ( $field_value ) {
                        $textOurClients = wpautop( $field_value );
                    }
                }
                
                if ( $textOurClients ) {
                    ?>
                    
                    <div class="text-ourClients">
                        
                        <?php echo $textOurClients; ?>
                        
                    </div>
                
                <?php } ?>
                
                <div class="block-sertificats">

                    <div class="aboutCompany-block-title"><?php esc_html_e( 'Diplomas and certificates', 'argon-shop-theme' ); ?></div>

                    <div class="block-sliderSertificats">

                        <?php

                        // ============================================
                        // CERTIFICATES (post type sertificates)
                        // ============================================
                        //
                        // Swiper slider markup. Initialization —
                        // in the theme's assets/js/slider-sertificates.js.
                        // Full-size view — GLightbox.

                        $arg = array(
                            'post_status'    => 'publish',
                            'post_type'      => 'sertificates',
                            'posts_per_page' => -1,
                        );

                        $sertificates = new WP_Query( $arg );

                        if ( $sertificates instanceof WP_Query && $sertificates->have_posts() ) :
                        ?>

                        <div class="as-slider as-slider-sertificates swiper">

                            <div class="swiper-wrapper">

                                <?php
                                while ( $sertificates->have_posts() ) : $sertificates->the_post();

                                    $imgSerticat     = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
                                    $imgSerticatFull = get_the_post_thumbnail_url( get_the_ID(), 'full' );

                                    if ( ! $imgSerticat || ! $imgSerticatFull ) {
                                        continue;
                                    }
                                    ?>

                                    <div class="swiper-slide">

                                        <a href="<?php echo esc_url( $imgSerticatFull ); ?>"
                                           class="glightbox-sertificate"
                                           data-glightbox="type: image;">

                                            <img src="<?php echo esc_url( $imgSerticat ); ?>"
                                                 alt="<?php echo esc_attr( get_the_title() ); ?>">

                                        </a>

                                    </div>

                                <?php endwhile; ?>

                            </div><!-- .swiper-wrapper -->

                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>

                        </div><!-- .as-slider-sertificates -->

                        <?php
                        wp_reset_postdata();
                        endif;
                        ?>

                    </div>

                </div>
                
            </div><!-- #production_list_page -->
                
            <?php endwhile; ?>

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>