/* МЕНЮ САЙТА */

$(".openMobileMenu").click(function(){
    
    var topMenu = $("#block-menu");
    
    //console.log(menu);
    //console.log(menu.find("#top-menu"));
    
    if( topMenu.is(':visible')  ) {
                    
                    //topMenu.find(".sub-menu").slideUp(300);    
                    topMenu.css("display","none");
                    $(".openMobileMenu").css("background-color","");
                    
                        
    }else{
        //console.log(menu.find("#top-menu"));
        topMenu.css("display","block");
        $(".openMobileMenu").css("background-color","#659bdd");
        //topMenu.slideDown(100);
        
    }
    
});

$(".menu-item-has-children").click(
        
    function(event) {
            
            if( $("body")[0].clientWidth <= 1200  ){
            
            //Проверка на самом элементе клик или на дочерних(выпадающих пунктах)
            if(event.target == this || event.target.parentNode == this){
            
             if( $(this).children(".sub-menu").is(':visible') ) {
                        
                    //$(this).children(".sub-menu").slideUp(300);
                        
             }else{
                    
                    event.preventDefault();    
                    $(this).children(".sub-menu").slideDown(300);
             }
                
            }
            
                    
            
        }
        
    }
)