//$("#slider_index .nav_link li").each(function(e) {
    //$(this).attr("data-index", $(this).index())
//})

$("#slider_index .list").slick({
    /* УПРАВЛЕНИЕ СЛАЙДЕРОМ НА ГЛАВНОЙ */
    dots: true,
    autoplay: true,
    autoplaySpeed: 5000,
    //dots: true,
    //infinite: true,
    speed: 1500,
    //cssEase: 'ease',
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: !0
})
///$("#slider_index .nav_link li").click(function(e) {
    //e.preventDefault(), slideIndex = $(this).index(), $("#slider_index .list").slick//("slickGoTo", parseInt(slideIndex))
//})



