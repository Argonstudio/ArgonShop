/* Архивная копия старого jQuery-кода 2018 года.

Файлы в папке oldJquery не нужны при установке плагина и могут быть удалены; они сохранены только для GitHub.

Позже я переписал всё на чистом современном JS, который теперь живёт в плагине. В файлах темы остались только CSS и PHP-шаблоны. */

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
            
            //Проверяем, клик по самому элементу или по его дочерним (пунктам выпадающего списка)
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