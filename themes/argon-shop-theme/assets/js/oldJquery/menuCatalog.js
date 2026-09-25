/* Архивная копия старого Jquery 2018 года

При установке плагина файлы из папки oldJquery не нужны и их можно удалить, это только для Гитхаба

Позднее я переписал все на чистый современный JS, сейчас он в плагине.В файлах же темы остался css и php шаблоны. */

//Правим высоту если выпадающий блок меню не помешается на странице(большие разрешения)
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
                
            //Проверка на самом элементе клик или на дочерних(выпадающих пунктах)
            //$(this).closest(".submenuCatalog") проверям на клик по дочернему выпадающему списку(ищем у источника клика родителя с id дочернего ul)
            if(event.target == this || $(this).closest(".submenuCatalog").length === 0){
            
                 if( $(this).children(".submenuCatalog").is(':visible') ) {
                            
                        //$(this).children(".sub-menu").slideUp(300);
                            
                 }else{
                        
                        event.preventDefault();    
                        $(this).children(".submenuCatalog").slideDown(300);
                 }
                
            }
            
            //прекращаем всплытие клика
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