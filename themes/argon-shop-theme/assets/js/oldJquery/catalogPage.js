/* Архивная копия старого Jquery 2018 года

При установке плагина файлы из папки oldJquery не нужны и их можно удалить, это только для Гитхаба

Позднее я переписал все на чистый современный JS, сейчас он в плагине.В файлах же темы остался css и php шаблоны. */

$(".catalogPage_openMobile").click(function(){
    
    if( $(this).next(".catalogPage_mobileExtensible").css("display") !== "block"){
        
        $(this).next(".catalogPage_mobileExtensible").css("display","block");
        
    }else{
        
        $(this).next(".catalogPage_mobileExtensible").css("display","none");
        
    }
    
})
