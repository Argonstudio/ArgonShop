$(".block-regQuestion1").click(function(){
  
    if($('.cartReg').is(':checked')){
        
        $('.cartReg').prop('checked', false);
        $(".cartLogin").css("display","none");
        
    } else {
        
        $('.cartReg').prop('checked', true);
        $(".cartLogin").css("display","block");
        
    } 
    
})