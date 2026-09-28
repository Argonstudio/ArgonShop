$(".accountCustomCheckbox").click(function(){
  
    if($('#accountLegal').is(':checked')){
        
        $('#accountLegal').prop('checked', false);
        $("#accountLegalBlock").css("display","none");
        
    } else {
        
        $('#accountLegal').prop('checked', true);
        $("#accountLegalBlock").css("display","block");
        
    } 
    
})