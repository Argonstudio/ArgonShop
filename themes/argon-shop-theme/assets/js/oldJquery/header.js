/* Archived copy of the old 2018 jQuery code.

The files in the oldJquery folder are not needed when installing the plugin and can be deleted; they are kept only for GitHub.

Later I rewrote everything in plain modern JS, which now lives in the plugin. Only the CSS and PHP templates remained in the theme files. */

/* CITY SELECTION */

$(".sitySelect").change(function(event){
    
    var value = $(this).val();
    
    var mainID = $("main").attr("id");
    
    if( mainID === "block-contentPromo" || mainID === "block-contentPromoPage" ){
        
        console.log("to the promotions page");
        location.href = value + $(".sitySelect :selected").attr("data-promocategory");
        
    }else if(mainID === "block-contentPromoArchive"){
        
        location.href = value + $(".sitySelect :selected").attr("data-promoarchive");
        
    }else{
        
        location.href = value + location.pathname;
        
    }
    
    
    
    //console.log($(location).attr('href'));
    //console.log(location.pathname);
    //console.log($(location));
    
})