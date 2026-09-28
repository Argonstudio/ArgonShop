/*Карточки товаров*/
    
    $(".card-inBascet").click(function(){
        addProducts( $(this).attr("data-productid"), 1, "cardInBascet", $(this) );
    })
    
    $(".card-buy").click(function(){
        addProducts( $(this).attr("data-productid"), 1, "cardBuy", $(this) );
    })
    