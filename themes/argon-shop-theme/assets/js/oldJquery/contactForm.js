/* Archived copy of the old 2018 jQuery code.

The files in the oldJquery folder are not needed when installing the plugin and can be deleted; they are kept only for GitHub.

Later I rewrote everything in plain modern JS, which now lives in the plugin. Only the CSS and PHP templates remained in the theme files. */


/* CONTACT FORM */

if ( typeof window.FormData !== 'function' ) {
    
    var feedback = $("#linkFeedback");
    
	feedback.attr("href","/kontakty/");
	feedback.removeAttr("data-fancybox");
	feedback.removeAttr("data-src");
	
}

//Select the feedback contact form
var wpcf7Elm = document.getElementById("wpcf7-f7-o1");
 
//Successful mail submission event
wpcf7Elm.addEventListener( 'wpcf7mailsent', function( event ) {
    
    //Close the active fancybox window
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