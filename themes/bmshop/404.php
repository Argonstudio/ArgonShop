<?php

get_header(); ?>
<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
            
            <?php if (function_exists('kama_breadcrumbs')) kama_breadcrumbs('<span class="b"> » </span>'); ?>
            
            <div class="page">
                <h1 class="title">Данная страница не существует!</h1>
                <article class="top_line">
                    <div class="text">
                        
                        <p>К сожалению, запрашиваемой Вами страницы не существует на сайте.</p>
                        
                        <p>Возможно, это случилось по одной из этих причин:</p>
                        
                        <ul>
                            
                            <li>Вы ошиблись при наборе адреса страницы (URL)</li>
                            <li>перешли по «битой» (неработающей, неправильной) ссылке</li>
                            <li>запрашиваемой страницы никогда не было на сайте или она была удалена</li>
                            
                        </ul>
                        
                        
                        <p><a href="/">« на главную страницу</a></p>
                    </div>
                </article>

            </div>
        </main>

        </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>
