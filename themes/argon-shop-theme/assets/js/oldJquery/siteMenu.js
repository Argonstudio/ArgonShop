/* Archived copy of the old 2018 jQuery code.

The files in the oldJquery folder are not needed when installing the plugin and can be deleted; they are kept only for GitHub.

Later I rewrote everything in plain modern JS, which now lives in the plugin. Only the CSS and PHP templates remained in the theme files. */

/* SITE MENU */

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
            
            //Check whether the click is on the element itself or on its children (dropdown items)
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