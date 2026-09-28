/* slick */

$('.block-sliderSertificats').slick({
   slidesToShow: 3,
  slidesToScroll: 1,
  infinite:true,
  draggable:false,
  dots: false,
  focusOnSelect: false,
  responsive : [
    {
      breakpoint : 780,
      settings : {
        slidesToShow   : 2,
        slidesToScroll : 1
      }
    },
    {
      breakpoint : 550,
      settings : {
        slidesToShow   : 1,
        slidesToScroll : 1
      }
    }
  ]
});