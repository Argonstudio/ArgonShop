jQuery(document).ready(function(){
    
    $(".shoppingCartAmountProduct").on('input keyup', function(){
        
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
        
        deleteProducts(productID,productParentBlock,productWeight,productPrice);
        
    })
    
    $(".deleteAllProducts").click(function(){
        
        deleteAllProductsShoppingCart();
    })
    
    
    $(".plusProduct").click(function(){
        
        var productID = $(this).attr("data-productid");
        
        var blockAmount = $(".shoppingCartAmountProduct[data-productid='"+ productID +"' ]");
        
        blockAmount.val(+blockAmount.val()+1);
        
        blockAmount.trigger('input');
    })
    
    $(".minusProduct").click(function(){
        
        var productID = $(this).attr("data-productid");
        
        var blockAmount = $(".shoppingCartAmountProduct[data-productid='"+ productID +"' ]");
        
        if(+blockAmount.val() > 1){
            blockAmount.val(+blockAmount.val()-1);
            blockAmount.trigger('input');
        }
    })
    
    $("#editEmailPasswordButton").click(function(){
        
        var userID = $(this).attr("data-userid");
        var email = $("#editEmail").val();
        var oldEmail = $("#editEmail").attr("data-oldEmail");
        var pass = $("#editOldPass").val();
        var newPass = $("#editNewPass").val();
        var newPassConfirm = $("#editNewPassConfirm").val();
        
        console.log(newPass);
        
        if(email != oldEmail){
            console.log(email);
        }
        
        cabinetEditEmailPassword(userID, email,oldEmail, pass, newPass, newPassConfirm);        
        
    })
    
    /* ОФОРМЛЕНИЕ ЗАКАЗА */
    
    function viewBlockItemCart(){
         
         var activeItemId = $(".itemControlPanelActive").attr("id");
         
         $(".blockItemCart").css("display","none");
         $(".blockItemCart[id='block_"+ activeItemId +"' ]").css("display","block");
         
     }
     
     viewBlockItemCart();
     
     $(".itemControlPanel").click(function(){
         
         $(".itemControlPanel").removeClass("itemControlPanelActive");
         $(this).addClass("itemControlPanelActive");
         
         viewBlockItemCart();
     })
     
     
     $("#personFile,#legalPersonFile").change(function(event){
         
         var files = this.files;
         var idFiles = $(this).attr("id");         
         
         if( $("#"+idFiles).attr("data-amountFiles") ){
             
             $(".block-fileCartMessage").css("display","block");
             
             var fileCartMessage = $(".block-fileCartMessage").children(".fileCartMessage");
             var loadedFiles = $(".block-fileCartMessage").children(".loadedFiles");
             
             if( +$("#"+idFiles).attr("data-amountFiles") < this.files.length){
                 
                 $(".labelAddFile").html("Прикрепить файлы");
                 fileCartMessage.html("Превышено максимально допустимое количество файлов(не более " + $("#"+idFiles).attr("data-amountFiles") + " файлов)");
                 
                 fileCartMessage.removeClass("loadSaccess");
                 fileCartMessage.addClass("loadError");
                 
                 loadedFiles.html('');
                 loadedFiles.css("display","none");
                 
                 $(".infoFileCart").css("display","block");
                 
                 document.getElementById(idFiles).value = "";
                 
                 return;
                 
             }
             
         }
         
         if( $("#"+idFiles).attr("data-sizeFile") ){
             
             for(var i = 0; i < this.files.length; i++){
                 
                 if( this.files[i].size/1024/1024 > +$("#"+idFiles).attr("data-sizeFile") ){
                     
                     $(".labelAddFile").html("Прикрепить файлы");
                     fileCartMessage.html("Размер одного или более файлов превышает " + $("#"+idFiles).attr("data-sizeFile") + " mb");
                 
                     fileCartMessage.removeClass("loadSaccess");
                     fileCartMessage.addClass("loadError");
                     
                     loadedFiles.html('');
                     loadedFiles.css("display","none");
                     
                     $(".infoFileCart").css("display","block");
                     
                     document.getElementById(idFiles).value = "";
                     
                     return;
                     
                 } 
                 
             }
         }
         
         
         fileCartMessage.html("Загружено файлов: " + this.files.length);
         
         fileCartMessage.removeClass("loadError");
         fileCartMessage.addClass("loadSaccess");
         
         loadedFiles.css("display","block");
         $(".infoFileCart").css("display","none");
         
         var messageFiles = "<ol>";
         
         for(var fileNum = 0; fileNum < this.files.length; fileNum++){
             
             messageFiles += "<li>" + this.files[fileNum].name + " [" + (this.files[fileNum].size/1024/1024).toFixed(2) + "mb] " + "</li>";
         }
         
         messageFiles += "</ol>";         
         
         loadedFiles.html( messageFiles );
         
     })
     
    
    $(".cartReg").each(function(){
        
        if( $(this).prop("checked") ){
        
            var parentForm = $(this).parents(".form-cart");
            
            var inputEmail = parentForm.find('input[name="emailUser"]');
            var inputLogin = parentForm.find('input[name="loginUser"]');
            
            parentForm.find(".cartLogin").css("display","block");
            
            inputEmail.attr("required","required");
            inputLogin.attr("required","required");
        }        
        
    })  
    
    $(".cartReg, .block-regQuestion").click(function(e){ 
        
        var parentForm = $(this).parents(".form-cart");
        
        var inputEmail = parentForm.find('input[name="emailUser"]');
        var inputLogin = parentForm.find('input[name="loginUser"]');
        
        parentForm.find(".cartLogin").toggle();        
        
        if( inputEmail.attr("required") ){
            
            inputEmail.removeAttr("required");
            inputLogin.removeAttr("required");
            
        }else{
            
            inputEmail.attr("required","required");
            inputLogin.attr("required","required");            
            
        }
		
        if( $(this).hasClass("block-regQuestion") ){
            
            if($('.cartReg').is(':checked')){
        
                $('.cartReg').prop('checked', false);
                
            } else {
                
                $('.cartReg').prop('checked', true);
                
            }
            
            
            
        }
        
        e.stopPropagation();
        
    })
    
    
    $(".form-cart").submit(function(){
                 
        var dataForm = new FormData(this);
        
        var productsCart = {};
        
        $(".shoppingCartAmountProduct").each(function(){
            
            productsCart[$(this).attr("data-productid")] = {"amountProduct": $(this).val()}
            
        })       
        
        var required = {};
        
        $(this).find("input,textarea").each(function(){
            
            if($(this).attr("required")){
                required[$(this).attr("name")] = true;
            }
        })
        
        productsCart = JSON.stringify(productsCart);
        required = JSON.stringify(required);
        
        dataForm.append("typeForm", $(this).attr("data-type") );
        dataForm.append("required", required );
        dataForm.append("productsCart", productsCart );
                 
        submitCart(dataForm);
        return false;
                 
    });
    
    
});