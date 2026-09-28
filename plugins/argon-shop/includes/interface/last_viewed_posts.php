<?php
/*
Plugin Name: Last Viewed Posts for ZBK
Description: Показывает список недавно просмотренных товаров. Доработка плагина для ZBK выполнена Argon Studio
Author: Syed Balkhi
Version: 0.7.2
*/

/* Copyright 2007 Olaf Baumann  (http://zeitgrund.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


Use:
For the ouput use the sidebar widget OR place following code just anywhere (outside the loop) into your theme (e.g. sidebar.php).
Note that the output will not appear if there's no cookie set (because cookies are disabled or the user didn't view any single post).
-------------------------------------------

<?php if (function_exists('zg_recently_viewed')):  if (isset($_COOKIE["WP-LastViewedPosts"])) { ?>
 <h2>Last viewed posts</h2>
 <?php zg_recently_viewed(); ?>
<?php }  endif; ?>

------------------------------------------- */

/* Here are some parameters you may want to change: */
$zg_cookie_expire = 360; // After how many days should the cookie expire? Default is 360.
$zg_number_of_posts = 15; // How many posts should be displayed in the list? Default is 10.
$zg_recognize_pages = true; // Should pages to be recognized and listed? Default is true.

/* Do not edit after this line! */

function zg_lwp_header() { // Main function is called every time a page/post is being generated
    //echo 1;
    //wp_die;
    if( is_singular( product ) ) {
        
        $lastViewedPosts = Array();
	
    	if(getCookie("WP-LastViewedPosts1")){
    	    $lastViewedPosts = getCookie("WP-LastViewedPosts1");
    	    $lastViewedPosts["counter"] += 1;
    	}else{
    	    $lastViewedPosts["counter"] = 1;
    	}
    	
    	
    	
    	$lastViewedPosts = wp_json_encode($lastViewedPosts);
    	
    	setcookie( "WP-LastViewedPosts1", $lastViewedPosts, time()+1209600, COOKIEPATH, COOKIE_DOMAIN);
        
		zg_lw_setcookie();
	}
}

function zg_lw_setcookie() { // Do the stuff and set cookie
	global $wp_query;
	//echo 1;
	$zg_post_ID = $wp_query->post->ID; // Read post-ID
	
	if (! isset($_COOKIE["WP-LastViewedPosts"])) {
		$zg_cookiearray = array($zg_post_ID); // If there's no cookie set, set up a new array
	} else {
		$zg_cookiearray = unserialize(preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", stripslashes($_COOKIE["WP-LastViewedPosts"]))); // Read serialized array from cooke and unserialize it
		if (! is_array($zg_cookiearray)) {
			$zg_cookiearray = array($zg_post_ID); // If array is fucked up...just build a new one.
		}
	}
  	if (in_array($zg_post_ID, $zg_cookiearray)) { // If the item is already included in the array then remove it
		$zg_key = array_search($zg_post_ID, $zg_cookiearray);
		array_splice($zg_cookiearray, $zg_key, 1);
	}
	array_unshift($zg_cookiearray, $zg_post_ID); // Add new entry as first item in array
	global $zg_number_of_posts;
	while (count($zg_cookiearray) > $zg_number_of_posts) { // Limit array to xx (zg_number_of_posts) entries. Otherwise cut off last entry until the right count has been reached
		array_pop($zg_cookiearray);
	}
	$zg_blog_url_array = parse_url(get_bloginfo('url')); // Get URL of blog
	$zg_blog_url = $zg_blog_url_array['host']; // Get domain
	$zg_blog_url = str_replace('www.', '', $zg_blog_url);
	$zg_blog_url_dot = '.';
	$zg_blog_url_dot .= $zg_blog_url;
	$zg_path_url = $zg_blog_url_array['path']; // Get path
	$zg_path_url_slash = '/';
	$zg_path_url .= $zg_path_url_slash;
	global $zg_cookie_expire;
	//print_r($zg_cookiearray);
	
	
	
	$lastViewedPosts = Array();
	
	if(getCookie("WP-LastViewedPosts1")){
	    $lastViewedPosts = getCookie("WP-LastViewedPosts1");
	}
	
	array_unshift($lastViewedPosts, $zg_post_ID);
	
	$lastViewedPosts = wp_json_encode($lastViewedPosts);
	
	//setcookie( "WP-LastViewedPosts1", $lastViewedPosts, time()+1209600, COOKIEPATH, COOKIE_DOMAIN);
	
	setcookie("WP-LastViewedPosts", serialize($zg_cookiearray), (time()+($zg_cookie_expire*86400)), $zg_path_url, $zg_blog_url_dot, 0);
	//print_r( $_COOKIE["WP-LastViewedPosts"]) ;
	//wp_die();
}

function zg_recently_viewed($rightAmount,$sidebarOn) { // Output

    echo "<div class='list row'>";
      
	if (isset($_COOKIE["WP-LastViewedPosts"])) {
		echo "Cookie was set.<br/>";  // For bugfixing - uncomment to see if cookie was set
		echo $_COOKIE["WP-LastViewedPosts"]; // For bugfixing (cookie content)
		$zg_post_IDs = unserialize(preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", stripslashes($_COOKIE["WP-LastViewedPosts"]))); // Read serialized array from cooke and unserialize it
		$counter = 0;
		
		print_r($zg_post_IDs);
		
		foreach ($zg_post_IDs as $value) { // Do output as long there are posts
		    
			    if( $counter >= $rightAmount ){
			        break;
			    }
			    
			    $counter++;
			    
			    
			    //$meta_data = get_post_meta( $value+0, 'product_custom_options', true );
			    $price = "?";
				//$price = $meta_data['price'];
				
				 if(get_post_meta( $value+0, '_price', true )){
						        
					$price = get_post_meta( $value+0, '_price', true );
					//$area = get_post_meta( $value+0, '_area', true ); 
					//$floor = get_post_meta( $value+0, '_floor', true ); 
								
				} else {
				    
					$meta_data = get_post_meta( $value+0, 'product_custom_options', true );
					$price = $meta_data['price'];
				}
				
			    if($sidebarOn){
			        echo "<div class='column small-12 medium-6 large-12'>";
			    }else{
			        echo "<div class='column small-12 medium-4 large-4'>";
			    }
?>
                
                <div class='list_row'>

                    <div class="image">
                        
        				<?php if( get_field( '_hit', $value+0 ) ): ?>
        				<span class="goods-label goods-label-hit">хит</span>
        				<?php endif;?>
        				
                       <a href="<?php echo get_permalink($value+0) ?>"> <?php echo get_the_post_thumbnail($value+0,'blog_thumb',array( 'class' => 'img-responsive' )) ?> </a>
        				
        			</div>
        			
        			<div class="name"><?php echo get_the_title( $value+0 )  ?></div>
        			
        			<div class="price">
						<span class="summ"><?php echo $price; ?> руб</span>
					</div>
					
					<div class="link">
					    <a href="<?php echo get_permalink($value+0) ?>" rel="bookmark"> Подробнее </a>
					 </div> 


                </div> 
            
            </div>

<?php                

		}
	} else {
		//echo "No cookie found.";  // For bugfixing - uncomment to see if cookie was not set
	}

    echo "</div>";

}

function zg_lwp_widget($args) { // Widget output
	extract($args);
	$options = get_option('zg_lwp_widget');
	$title = htmlspecialchars(stripcslashes($options['title']), ENT_QUOTES);
	$title = empty($options['title']) ? 'Last viewed posts' : $options['title'];
	if (isset($_COOKIE["WP-LastViewedPosts"])) {
		echo $before_widget . $before_title . $title . $after_title;
		zg_recently_viewed();
		echo $after_widget;
	}
}

function zg_lwp_widget_control() { // Widget control
	$options = $newoptions = get_option('zg_lwp_widget');
	if ( $_POST['lwp-submit'] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST['lwp-title']));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('zg_lwp_widget', $options);
	}
	$title = attribute_escape( $options['title'] );
	?>
	<p><label for="lwp-title">
	<?php _e('Title:') ?> <input type="text" style="width:250px" id="lwp-title" name="lwp-title" value="<?php echo $title ?>" /></label>
	</p>
	<input type="hidden" name="lwp-submit" id="lwp-submit" value="1" />
	<?php
}

function zg_lwp_init() { // Widget init
  	if ( !function_exists('register_sidebar_widget') )
  		return;
	register_sidebar_widget('Last Viewed Posts','zg_lwp_widget');
  	register_widget_control('Last Viewed Posts','zg_lwp_widget_control', 250, 100);
}


//add_action('init','zg_lwp_header');
add_action('get_header','zg_lwp_header');
add_action('widgets_init', 'zg_lwp_init');
?>