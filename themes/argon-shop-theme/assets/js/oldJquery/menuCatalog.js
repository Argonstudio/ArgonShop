/* Архивная копия старого jQuery-кода 2018 года.

Файлы в папке oldJquery не нужны при установке плагина и могут быть удалены; они сохранены только для GitHub.

Позже я переписал всё на чистом современном JS, который теперь живёт в плагине. В файлах темы остались только CSS и PHP-шаблоны. */

//Подгоняем высоту, если блок выпадающего меню не помещается на странице (большие разрешения)
$('.menuCatalogItem').hover(
    function(){
        if( $("body")[0].clientWidth > 1000  ){
            
            var pageHeight = $("body").height();
            var needHeight = $("#header").height() + 46 + $(this).children(".submenuCatalog").height();
            //console.log(needHeight);
            //console.log(pageHeight);
            
            if( needHeight >= pageHeight ){
                
                $("#out").children(".row").css("min-height", $("body").height() + (needHeight - pageHeight) + 300 + "px");
                
            }
            
        }
    },
    function(){
        $("#out").children(".row").css("min-height", "");
    }
);

$(".openMobileCatalog").click(function(){
    
    var menuCatalog = $(".block-menuCatalog");
    var linkSidebar = $(".block-linkSidebar");
    
    if( menuCatalog.is(':visible')  ) {
                       
        menuCatalog.css("display","none");
        linkSidebar.css("display","none");
                    
                        
    }else{
        
        menuCatalog.css("display","block");
        linkSidebar.css("display","block");
        
    }
    
})



$(".itemHasChildren").click(
    function(event) {
              
            if( $("body")[0].clientWidth <= 1000  ){
                
            //Проверяем, клик по самому элементу или по его дочерним (пунктам выпадающего списка)
            //$(this).closest(".submenuCatalog") проверяет клик по дочернему выпадающему списку (ищет родителя с дочерним ul id у источника клика)
            if(event.target == this || $(this).closest(".submenuCatalog").length === 0){
            
                 if( $(this).children(".submenuCatalog").is(':visible') ) {
                            
                        //$(this).children(".sub-menu").slideUp(300);
                            
                 }else{
                        
                        event.preventDefault();    
                        $(this).children(".submenuCatalog").slideDown(300);
                 }
                
            }
            
            //останавливаем всплытие клика
            event.stopPropagation();        
            
        }
        
    }
)

$(".block-textMenuUrl").click(
    function(event) {
            //console.log($(this));  
            if( $("body")[0].clientWidth <= 1000  ){
                //event.preventDefault();  
            }    
})                