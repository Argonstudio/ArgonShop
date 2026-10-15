/* Архивная копия старого jQuery-кода 2018 года.

Файлы в папке oldJquery не нужны при установке плагина и могут быть удалены; они сохранены только для GitHub.

Позже я переписал всё на чистом современном JS, который теперь живёт в плагине. В файлах темы остались только CSS и PHP-шаблоны. */

/* ВЫБОР ГОРОДА */

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