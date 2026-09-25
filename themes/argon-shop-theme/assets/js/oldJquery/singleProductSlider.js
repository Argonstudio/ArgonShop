/* СЛАЙДЕР НА СТРАНИЦЕ ТОВАРА */

/* fancybox + slick */

$().fancybox({
  //selector : '.slick-slide:not(.slick-cloned)',
  selector : '[data-fancybox="productSlider"]',
  hash     : false
});

/* slick */

 $('.slider-for').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  arrows: false,
  fade: true,
  responsive : [
    {
      breakpoint : 960,
      settings : {
        slidesToShow   : 1,
        slidesToScroll : 1
      }
    }
  ]
});

$('.slider-nav').slick({
   slidesToShow: 3,
  slidesToScroll: 1,
  infinite:true,
  draggable:false,
  asNavFor: '.slider-for',
  dots: false,
  focusOnSelect: true,
  responsive : [
    {
      breakpoint : 388,
      settings : {
        slidesToShow   : 2,
        slidesToScroll : 1
      }
    }
  ]
});

//if($(".slick-slide").width())


$(".slider-for").find(".slick-slide").each(function(){
    
    if( $(this).width() < $(this).find(".mediumImgProductSlider").children('img').width() ){
    
        $(this).find(".mediumImgProductSlider").children('img').css("max-width", $(this).width() );
        
    }
    
    //console.log($(".slick-list"));
    //console.log($(this)[0].clientHeight);
    //console.log($(this).find(".mediumImgProductSlider").children('img').height());
    
    if( $(".slick-list").height() < $(this).find(".mediumImgProductSlider").children('img').height() ){
        
        $(this).find(".mediumImgProductSlider").children('img').css("max-height", $(".slick-list").height() );
        
    }
    
})



$(window).resize( function() {
    
    if( $(".block-productSlider").length > 0 ){
        
       $(".slider-for").find(".slick-slide").each(function(){
    
            if( $(this).width() < $(this).find(".mediumImgProductSlider").children('img').width() ){
            
                $(this).find(".mediumImgProductSlider").children('img').css("max-width", $(this).width() );
                
            }
            
            //console.log($(".slick-list"));
            //console.log($(this)[0].clientHeight);
            //console.log($(this).find(".mediumImgProductSlider").children('img').height());
            
            if( $(".slick-list").height() < $(this).find(".mediumImgProductSlider").children('img').height() ){
                
                $(this).find(".mediumImgProductSlider").children('img').css("max-height", $(".slick-list").height() );
                
            }
            
        })
        
    }
    
})