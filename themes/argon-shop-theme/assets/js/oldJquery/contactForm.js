/* Архивная копия старого Jquery 2018 года

При установке плагина файлы из папки oldJquery не нужны и их можно удалить, это только для Гитхаба

Позднее я переписал все на чистый современный JS, сейчас он в плагине.В файлах же темы остался css и php шаблоны. */


/* КОНТАКТНАЯ ФОРМА */

if ( typeof window.FormData !== 'function' ) {
    
    var feedback = $("#linkFeedback");
    
	feedback.attr("href","/kontakty/");
	feedback.removeAttr("data-fancybox");
	feedback.removeAttr("data-src");
	
}

//Выбор контактной формы обратная связь
var wpcf7Elm = document.getElementById("wpcf7-f7-o1");
 
//Событие успешной отправки письма
wpcf7Elm.addEventListener( 'wpcf7mailsent', function( event ) {
    
    //Закрытие активного окна fancybox
    setTimeout('$.fancybox.close()', 1500);
    
}, false );

$(".wpcf7-submit").on("click",function(e){
    
    var offset = $(this).offset();
    
    var relativeX = (e.pageX - offset.left);
    var relativeY = (e.pageY - offset.top);
    
    if(relativeX>0&&relativeY>0){
        //console.log("X: " + relativeX + "  Y: " + relativeY);
        
    }else{
        e.preventDefault();
        
        var blockWpcf7 = $(".wpcf7-response-output"); 
        
        blockWpcf7.addClass("wpcf7-mail-sent-ok");
        blockWpcf7.css("display","block");
        blockWpcf7.html("Ваше сообщение успешно отправлено");
        setTimeout('$.fancybox.close()', 1500);
    }
})