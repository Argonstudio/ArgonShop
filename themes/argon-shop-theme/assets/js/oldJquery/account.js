/* Архивная копия старого jQuery-кода 2018 года.

Файлы в папке oldJquery не нужны при установке плагина и могут быть удалены; они сохранены только для GitHub.

Позже я переписал всё на чистом современном JS, который теперь живёт в плагине. В файлах темы остались только CSS и PHP-шаблоны. */

$(".accountCustomCheckbox").click(function(){
  
    if($('#accountLegal').is(':checked')){
        
        $('#accountLegal').prop('checked', false);
        $("#accountLegalBlock").css("display","none");
        
    } else {
        
        $('#accountLegal').prop('checked', true);
        $("#accountLegalBlock").css("display","block");
        
    } 
    
})