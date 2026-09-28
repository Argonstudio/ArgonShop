<?php 

$post = $wp_query->post;
 
  if (in_category('36')) { 
      
      include(TEMPLATEPATH.'/single-aktsii-moskva.php');
      
  } else if(in_category('35')){
      
      include(TEMPLATEPATH.'/404.php');
      //include(TEMPLATEPATH.'/single-aktsii-istra.php');
      
   }else {
      include(TEMPLATEPATH.'/single-default.php');
  }