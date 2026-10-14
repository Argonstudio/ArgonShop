/* Archived copy of the old 2018 jQuery code.

The files in the oldJquery folder are not needed when installing the plugin and can be deleted; they are kept only for GitHub.

Later I rewrote everything in plain modern JS, which now lives in the plugin. Only the CSS and PHP templates remained in the theme files. */

//Adjust the height if the dropdown menu block does not fit on the page (large resolutions)
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
                
            //Check whether the click is on the element itself or on its children (dropdown items)
            //$(this).closest(".submenuCatalog") checks for a click on the child dropdown list (looks for a parent with the child ul id at the click source)
            if(event.target == this || $(this).closest(".submenuCatalog").length === 0){
            
                 if( $(this).children(".submenuCatalog").is(':visible') ) {
                            
                        //$(this).children(".sub-menu").slideUp(300);
                            
                 }else{
                        
                        event.preventDefault();    
                        $(this).children(".submenuCatalog").slideDown(300);
                 }
                
            }
            
            //stop the click propagation
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