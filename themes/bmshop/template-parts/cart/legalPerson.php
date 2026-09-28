<?php 
    $userdata = get_user_by( 'id', $user_ID );
	$accountData = get_user_meta($user_ID, "accountData", true);
?>


<form enctype="multipart/form-data" method="post" data-type="legalPerson" class="form-cart form-cartlegalPerson">
    
    <?php 
    
    $required = Array("nameUser","telefonUser");
    
    show_user_fields($user_ID, "person", "div", "cartFields50","cartFields100","cartFieldsTitle","cartFieldInput", $required); 
    
    ?>
    
    <div class="cartFields50 cartFieldsEmail-person">
        
        <div class="cartFieldsTitle">Email</div>
        <input type="text" class="cartFieldInput" name="emailUser" placeholder="mail@example.com" value="<?php echo $userdata->user_email ?>">
        
    </div>
    
    <?php 
    
    $required = Array("companyNameUser","innUser","kppUser","legalAddressUser");
    
    show_user_fields($user_ID, "legalPerson","div","cartFields50 fields50legalPerson","cartFields100","cartFieldsTitle","cartFieldInput",$required); 
    
    ?>
    
    <div class="cartFields100">
        
        <div class="cartFieldsTitle">Комментарий:</div>
        <textarea name="messageUser" placeholder="Ваш комментарий"></textarea>
        
    </div>
    
    <label for="legalPersonFile" class="labelAddFile"><span id="foto">Прикрепить файлы</span></label>
    
    <div class="block-fileCartMessage">
        
        <div class="fileCartMessage"></div>
        <div class="loadedFiles"></div>
        
    </div>
            
    <div class="infoFileCart">Допустимые форматы файлов: doc, docx, xls, xlsx, pdf, jpg, jpeg, png, bmp</div>
    <div class="infoFileCart">Прикрепите до 3-х файлов, до 5 mb один файл </div>
    <div class="infoFileCart">Зажмите ctrl для выбора нескольких файлов(windows)</div>
            
            
    <input type="file" multiple="" data-sizeFile="5" data-amountFiles="3" name="fileCart[]" id="legalPersonFile" accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
    
    <!--<input multiple="" name="fileCart1[]" style="display:none" id="fileCart1" type="file" accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">-->
    
    <div class="cartRegistration" <?php if( is_user_logged_in() ){ echo 'style="display:none"'; } ?> >
        
        <div class="block-regQuestion">
            
            <input type="checkbox" name="cartReg" class="cartReg"> 
            <span>Зарегистрировать вас?</span>
            
        </div>
        
        <div class="cartLogin">
        
            <div class="cartFieldsTitle">Логин</div>
            <input type="text" class="cartFieldInput" name="loginUser">
            
        </div>
        
    
    </div>
    
    <div class="block-shippingPayment">
    
        <div class="block-shippingMethod">
                            
            <div class="shippingyMethodTitle">Способ доставки:</div>
             
            <div class="block-shippingMethodTypes">  
            
                <div class="shippingMethodType"><input type="radio" name="shipping" value="selfExport"> Самовывоз</div>
                <div class="shippingMethodType"><input type="radio" name="shipping" value="shippingToAddress" checked> Доставка по адресу</div>
            
            </div>
                            
        </div>
    
        <div class="block-paymentMethod">
                            
            <div class="paymentMethodTitle">Способ оплаты:</div>
            
            <div class="block-paymentMethodTypes">
                            
                <div class="paymentMethodType"><input type="radio" name="payment" value="paimentUponReceipt" checked> Оплата при получении</div>
            
            </div>  
            
        </div>
    
    </div>
				
    <input value="ОТПРАВИТЬ ЗАКАЗ" type="submit" class="submitCart">
    <div class="submitError"><span></span></div>
                    
</form>