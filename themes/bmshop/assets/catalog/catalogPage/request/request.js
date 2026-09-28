
if($(".block-cards-request").length > 0){
    
    if($(".block-cards-request").height()+20 < $(".block-cards-request")[0].scrollHeight){
        $(".view-allCards-request").css("display","block");
    }
    
}

$(".view-allCards-request").click(function(){
    
    var blockCardsRequest = $(".block-cards-request");
    
    if( blockCardsRequest.css("max-height") !== "83px" ){
       
        blockCardsRequest.css("max-height", "" );
        
    }else{
        
        blockCardsRequest.css("max-height", blockCardsRequest[0].scrollHeight+30+"px" );
        
    }
    
    
    //$(".view-allCards-request").css("display","none");
    
})