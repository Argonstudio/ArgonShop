$(".catalogPage_openMobile").click(function(){
    
    if( $(this).next(".catalogPage_mobileExtensible").css("display") !== "block"){
        
        $(this).next(".catalogPage_mobileExtensible").css("display","block");
        
    }else{
        
        $(this).next(".catalogPage_mobileExtensible").css("display","none");
        
    }
    
})
