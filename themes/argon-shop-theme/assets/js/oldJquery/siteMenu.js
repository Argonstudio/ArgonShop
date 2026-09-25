/* Архивная копия старого Jquery 2018 года

При установке плагина файлы из папки oldJquery не нужны и их можно удалить, это только для Гитхаба

Позднее я переписал все на чистый современный JS, сейчас он в плагине.В файлах же темы остался css и php шаблоны. */

/* МЕНЮ САЙТА */

$(".openMobileMenu").click(function(){
    
    var topMenu = $("#block-menu");
    
    if( topMenu.is(':visible')  ) {
                        
                    topMenu.css("display","none");
                    $(".openMobileMenu").css("background-color","");
                    
                        
    }else{
        
        topMenu.css("display","block");
        $(".openMobileMenu").css("background-color","#659bdd");
        
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