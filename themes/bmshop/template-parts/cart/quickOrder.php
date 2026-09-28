
<form enctype="multipart/form-data" method="post" data-type="quick" class="form-cart form-cartquick">


    <?php 
        $required = Array("telefonUser","addressUser");
        show_user_fields($user_ID, "quick", "div", "cartFields50","cartFields100","cartFieldsTitle","cartFieldInput", $required);  
    ?>
    
    <div class="cartFields100">
        
        <div class="cartFieldsTitle">Комментарий:</div>
        <textarea name="messageUser" placeholder="Ваш комментарий"></textarea>
        
    </div>
    
				
    <input value="ОТПРАВИТЬ ЗАКАЗ" type="submit" class="submitCart">
    <div class="submitError"><span></span></div>
                    
</form>