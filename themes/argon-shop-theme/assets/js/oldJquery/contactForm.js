/* Архивная копия старого jQuery-кода 2018 года.

Файлы в папке oldJquery не нужны при установке плагина и могут быть удалены; они сохранены только для GitHub.

Позже я переписал всё на чистом современном JS, который теперь живёт в плагине. В файлах темы остались только CSS и PHP-шаблоны. */


/* ФОРМА ОБРАТНОЙ СВЯЗИ */

if ( typeof window.FormData !== 'function' ) {
    
    var feedback = $("#linkFeedback");
    
	feedback.attr("href","/kontakty/");
	feedback.removeAttr("data-fancybox");
	feedback.removeAttr("data-src");
	
}

//Выбираем контактную форму обратной связи
var wpcf7Elm = document.getElementById("wpcf7-f7-o1");
 
//Событие успешной отправки письма
wpcf7Elm.addEventListener( 'wpcf7mailsent', function( event ) {
    
    //Закрываем активное окно fancybox
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
        blockWpcf7.html("Your message has been sent successfully");
        setTimeout('$.fancybox.close()', 1500);
    }
})