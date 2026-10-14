/* Archived copy of the old 2018 jQuery code.

The files in the oldJquery folder are not needed when installing the plugin and can be deleted; they are kept only for GitHub.

Later I rewrote everything in plain modern JS, which now lives in the plugin. Only the CSS and PHP templates remained in the theme files. */

$(".catalogPage_openMobile").click(function(){
    
    if( $(this).next(".catalogPage_mobileExtensible").css("display") !== "block"){
        
        $(this).next(".catalogPage_mobileExtensible").css("display","block");
        
    }else{
        
        $(this).next(".catalogPage_mobileExtensible").css("display","none");
        
    }
    
})
