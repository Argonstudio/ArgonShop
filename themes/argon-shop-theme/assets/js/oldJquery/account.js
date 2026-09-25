/* Архивная копия старого Jquery 2018 года

При установке плагина файлы из папки oldJquery не нужны и их можно удалить, это только для Гитхаба

Позднее я переписал все на чистый современный JS, сейчас он в плагине.В файлах же темы остался css и php шаблоны. */

$(".accountCustomCheckbox").click(function(){
  
    if($('#accountLegal').is(':checked')){
        
        $('#accountLegal').prop('checked', false);
        $("#accountLegalBlock").css("display","none");
        
    } else {
        
        $('#accountLegal').prop('checked', true);
        $("#accountLegalBlock").css("display","block");
        
    } 
    
})