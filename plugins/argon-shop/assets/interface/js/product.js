jQuery(document).ready(function(){
    
    $(window).load(function () {
        
       var productID = $("#addShoppingCart").attr("data-productid");
       
       if(productID){
           addCookieHistory(productID);
       }
       
    });
    
    
    getWholesalePrice();
    
    $("#amountProduct").on('input', function(){
        getWholesalePrice();
    });
    
    $("#addShoppingCart").click(function(){
        
        var productID = $(this).attr("data-productid");
        console.log(productID);
        var amountProduct = $("#amountProduct").val();        
        
        addProducts(productID,amountProduct);
        
    })
    
    $("#productPageCartBuy").click(function(){
        
        var productID = $(this).attr("data-productid");
        var amountProduct = $("#amountProduct").val();        
        
        addProducts( productID,amountProduct, "productBuy", $(this) );
    })
    
    $(".plusProduct-Page").click(function(){
        
        var amountProduct = $("#amountProduct");
        
        amountProduct.val(+amountProduct.val()+1);
        
        amountProduct.trigger('input');
    })
    
    $(".minusProduct-Page").click(function(){
        
        var amountProduct = $("#amountProduct");
        
        if(+amountProduct.val() > 1){
            
            amountProduct.val(+amountProduct.val()-1);
            amountProduct.trigger('input');
        }
    })
    
    
    
});