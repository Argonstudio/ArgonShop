/* Архивная копия старого Jquery 2018 года

При установке плагина файлы из папки oldJquery не нужны и их можно удалить, это только для Гитхаба

Позднее я переписал все на чистый современный JS, сейчас он в плагине.В файлах же темы остался css и php шаблоны. */

/* ВЫБОР ГОРОДА*/

$(".sitySelect").change(function(event){
    
    var value = $(this).val();
    
    var mainID = $("main").attr("id");
    
    if( mainID === "block-contentPromo" || mainID === "block-contentPromoPage" ){
        
        console.log("на страницу акций");
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