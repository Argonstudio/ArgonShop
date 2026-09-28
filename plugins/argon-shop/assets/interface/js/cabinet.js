jQuery(document).ready(function(){
    
    /**
     * Личный кабинет
     * =========================================== */     
     
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
    
    
    if( $("#accountLegal").attr("checked") != 'checked' ){
        $("#accountLegalBlock").css("display","none");
    }
    
    
    $("#accountLegal").click(function(){
        $("#accountLegalBlock").toggle();
    })
    
    $("#accountSaveButton").click( function(){
        
        var userID = $(this).attr("data-userid");
        
        var forBack = {
            "accountDetail" : {}
            
        };
        
        
        $(".accountDetail").each(function(){
                
            var name = $(this).attr("name");
                
            //Проверяем что имя поля содержит только латинские и русские буквы
            //^..$ отмечаем якорями указывая что имя целиком должно соответствовать паттерну
            //[\w] все латинские буквы
            //[\wА-яё]+ все русские и + проверяем все символы в строке
            if( !/^[\wА-яё]+$/.test(name) ){
                return true;
            }
                
            var value = $(this).val();
                
            forBack["accountDetail"][name] = value;
            
            
        })
        
        if( $("#accountLegal").prop('checked') ){
            
            $(".accountLegalDetail").each(function(){
                
                if( !forBack["accountLegalDetail"] ){
                    forBack["accountLegalDetail"] = {};
                }            
                    
                var name = $(this).attr("name");
                var value = $(this).val();
                    
                forBack["accountLegalDetail"][name] = value;                  
                
            })
            
        }
        
        forBack = JSON.stringify(forBack);
        
        accountSave(userID, forBack);
        
    })
    
    $("#editEmailActive").click(function(){
        
        var editEmail = $("#editEmail").val();
        
        $("#editEmail").prop("disabled",false);
        $("#editEmail").focus().val('').val(editEmail);
        
        
        $("#editEmailActive").css("display","none");
    })
    
    /**
     * Заказы
     * =========================================== */
     
    $(".as-orderCabinet-filterStatus").on("change", function(){        
        
        var userID = $(this).attr('data-userid');
        var status = $(this).val();
        var blockResult = $(".as-ordersCabinet-block");
        
        ordersFiltersStatus( userID, status, blockResult );
        
    })
});