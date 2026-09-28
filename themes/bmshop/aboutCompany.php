<?php
/**
 * Template Name: О компании
 */


get_header(); ?>
<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentPage block-contentAboutCompany">

            <?php if (function_exists('kama_breadcrumbs')) kama_breadcrumbs(' » '); ?>

            <?php while (have_posts()) the_post(); ?>

            <div class="page">

                <h1 class="title titlePage"><?php the_title(); ?></h1>
                
                <?php the_content(); ?>
                
                <div class="block-advantages">
                
                    <div class="aboutCompany-block-title">Наши преимущества</div>
                    
                    <div class="block-advantages-cards">
                    
                        <div class="card-advantage twentyYear">
                            
                            <div class="advantage-img"><span>+</span></div>
                            <div class="advantage-title">20 лет</div>
                            <div class="advantage-descr">на рынке стройматериалов</div>
                            
                        </div>
                        
                        <div class="card-advantage assortment">
                            
                            <div class="advantage-img"><span>+</span></div>
                            <div class="advantage-title">Ассортимент</div>
                            <div class="advantage-descr">Более 60 тысяч наименований товаров</div>
                            
                        </div>
                        
                        <div class="card-advantage trust">
                            
                            <div class="advantage-img"><span>+</span></div>
                            <div class="advantage-title">Нам доверяют</div>
                            <div class="advantage-descr">Более тысячи клиентов в месяц </div>
                            
                        </div>
                        
                        <div class="card-advantage autopark">
                            
                            <div class="advantage-img"><span>+</span></div>
                            <div class="advantage-title">Автопарк</div>
                            <div class="advantage-descr">12 машин и манипуляторы, доставка по МО</div>
                            
                        </div>
                    
                    </div>
                    
                </div>
                
                
								    
				<?php $textOurAdvantages = wpautop (get_field ('our_advantages', get_the_ID())); ?>	
					
				<?php if($textOurAdvantages){ ?>
					
				    <div class="text-ourAdvantages">
				        
				        <?=$textOurAdvantages ?>
				        
				    </div>
                
                <?php } ?>
                
                
                <div class="block-ourClients">
                
                    <div class="aboutCompany-block-title">Наши партнеры</div>
                    
                    <div class="block-logoClients">
                    
                        <?php
                            $arg = array(
                                'post_status' => 'publish',
                                'post_type' => 'ourclients',
                                'posts_per_page' => -1
                            );
                            
                            $сlients = new WP_Query( $arg );
                            
                            if ( $сlients -> have_posts() ) :
                                while ( $сlients -> have_posts() ) : $сlients -> the_post();
                                
                                $linkClient = get_post_meta( get_the_ID(), 'client_link', true );
                                
                        ?>
                        
                            <div class="block-client">
                                
                                <?php echo ($linkClient) ? '<a href="'.$linkClient.'" target="_blank">' : ''; ?>
                                
                                    <span><?php the_post_thumbnail('slider_thumb', array( 'class' => 'img-responsive' )); ?></span>
                                
                                <?php echo ($linkClient) ? '</a>' : ''; ?>
                                
                            </div>
                            
                        <?php 
                        
                            endwhile; 
                            wp_reset_postdata(); 
                            endif;  
                            
                        ?>
                        
                    </div>
                    
                </div>
                
                <?php $textOurClients = wpautop (get_field ('our_clients', get_the_ID())); ?>	
					
				<?php if($textOurClients){ ?>
					
				    <div class="text-ourClients">
				        
				        <?=$textOurClients ?>
				        
				    </div>
                
                <?php } ?>
                
                <div class="block-sertificats">
                
                    <div class="aboutCompany-block-title">Дипломы и сертификаты</div>
                    
                    <div class="block-sliderSertificats">
                    
                        <?php
                            $arg = array(
                                'post_status' => 'publish',
                                'post_type' => 'sertificates',
                                'posts_per_page' => -1
                            );
                            
                            $sertificates = new WP_Query( $arg );
                            
                            if ( $sertificates -> have_posts() ) :
                                while ( $sertificates -> have_posts() ) : $sertificates -> the_post();
                                
                                $imgSerticat = get_the_post_thumbnail_url($post,'medium');
                                $imgSerticatFull = get_the_post_thumbnail_url($post,'full');
                        ?>
                        
                            <div class="block-sertificat">
                                
                                    <a href="<?=$imgSerticatFull ?>" data-fancybox="productSlider">
                                        
                                        <img src="<?=$imgSerticat ?>">
                                        
                                    </a>
                                
                            </div>
                            
                        <?php 
                        
                            endwhile; 
                            wp_reset_postdata(); 
                            endif;  
                            
                        ?>
                        
                    </div>
                    
                </div>
                
            </div><!-- #production_list_page -->
                
            

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>
