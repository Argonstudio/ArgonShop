jQuery(document).ready(function(){
    
    
    $(".valueAddOrder").on('input', function(){
    
        var searchStr = $(this).val();
        
        var blockResult = $(".addOrderResult");
        
        var addedProduct = [];
        
        $(".orderAmountProduct").each(function(){
            
            addedProduct.push( $(this).attr("data-productid") );
            
        });
        
        console.log(addedProduct);
            
        if( searchStr.length > 1 ){
                
            searchSuitableProduct(searchStr, blockResult, addedProduct);
                
        }else if(blockResult.css("display") == "block"){
                
            blockResult.css("display","none");
                
        }
        
        
    });
    
    jQuery(".as-order-addFieldsSend").click(function(){
        
        var type = $(".as-order-addFieldsSelect").val();
        var blockFields = $(".advanced-fields");
        
        var fieldsInStock = [];
        
        $(".advanced-field").each(function(){
            
            fieldsInStock.push($(this).attr("data-keyfield"));
        })
        
        console.log(fieldsInStock);
       
        addFields(type, fieldsInStock, blockFields);
        
    });
    
    jQuery(".as-addProduct").click(function(){
        console.log(1);
        var blockProducts = $(".orderAllProducts");
        var blockError = jQuery(".orderError");
        
        addProduct($(this), blockProducts, blockError);
        
    })

    
    jQuery(".addOrder").click(function(){
        //console.log(1);
        
        var typeOrder = jQuery(".valueAddOrder").val();
        var valueOrder = jQuery(".typeAddOrder").val();
        
        var blockProducts = jQuery(".orderAllProducts");
        var blockError = jQuery(".orderError");
        
        addOrderProduct(typeOrder, valueOrder, blockProducts, blockError);
        
    })
    
    $(".orderAmountProduct").on('input keyup', function(){
        
        var productID = $(this).attr("data-productid");
        
        //Блок оборачивающий удаляемую или редактирую карточку с товаром
        var productParentBlock = $(this).parents("[data-productid='"+ productID +"' ]");
        
        //Цена товара, ищем через родительский блок найденный выше
        var productPrice = productParentBlock.find(".productPrice").children("span").html();
        
        var productAmountWeightBlock = productParentBlock.find(".amountProductWeight").children("span");
        var productAmountPriceBlock = productParentBlock.find(".amountProductPrice").children("span");
        
        //Проверка введенного значения на число больше нуля
        if(!$(this).val() || $(this).val() <= 0 || !$.isNumeric( $(this).val() ) ){
            
            $(this).val(1)
            cartAmountProductsAjax(productID,1,productPrice,productAmountWeightBlock,productAmountPriceBlock);
        }else{
            cartAmountProductsAjax(productID,$(this).val(),productPrice,productAmountWeightBlock,productAmountPriceBlock);
        }
        
    });  
    
    $(".deleteProduct").click(function(){
        
        var productID = $(this).attr("data-productid");
        var productParentBlock = $(this).parents("[data-productid='"+ productID +"' ]");
        var productWeight = productParentBlock.find(".amountProductWeight").children("span").html();
        var productPrice = productParentBlock.find(".amountProductPrice").children("span").html();
        
        //console.log(productWeight);
        
        deleteProducts(productID,productParentBlock,productWeight,productPrice);
        
    })
    
     $(".deleteAllProducts").click(function(){
        
        deleteAllProductsShoppingCart();
    })
    
    $("body").on("click", ".plusProduct", function(){
      
        var productID = $(this).attr("data-productid");
        
        var blockAmount = $(".orderAmountProduct[data-productid='"+ productID +"' ]");
        
        blockAmount.val(+blockAmount.val()+1);
		
    })
    
    $("body").on("click", ".minusProduct", function(){
        
        var productID = $(this).attr("data-productid");
        
        var blockAmount = $(".orderAmountProduct[data-productid='"+ productID +"' ]");
        
        if(+blockAmount.val() > 1){
            blockAmount.val(+blockAmount.val()-1);
        }
    })
    
    $("body").on("click", ".deleteProduct", function(){
        
        if($(".deleteProduct").length > 1){
            $(this).parents(".blockOrderProduct").remove();
        }
        
        
    
    })
    
    
});