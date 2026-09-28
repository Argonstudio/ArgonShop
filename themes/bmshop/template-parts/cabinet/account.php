<?php
        $userdata = get_user_by( 'id', $user_ID );
        $accountData = get_user_meta($user_ID, "accountData",true);        
    ?>
    
        
    <div class="account"></div>
    
        <table>
            
            <?php show_user_fields($user_ID, "person","tr","block-userField50","block-userField100","userFieldTitle","userFieldInput"); ?>
                  
        </table>
        
        
        
        <div class="accountLegalBlock">
             
            <input type="checkbox" id="accountLegal" style="display:none" <?php if( $accountData["accountLegalDetail"]){ echo "checked"; } ; ?> >  
            <div class="accountCustomCheckbox"></div> 
            <span>Юридическое лицо</span> 
            
        </div>
        
        
        <table id="accountLegalBlock">
            
            <?php show_user_fields($user_ID, "legalPerson","tr","block-userField50","block-userField100","userFieldTitle","userFieldInput"); ?>
            
        </table>
        
        <div class="block-accountSave">
            
            <input type="button" data-userid="<?php echo $user_ID ?>" id="accountSaveButton" value="Сохранить" />
            <div class="accountResult"><span></span></div>
            <div class="consentPersonalData">Нажимая сохранить Вы предоставляете согласие на <a href="/konfidentsialnost-personalnoj-informatsii/" target="_self">обработку ваших персональных данных</a></div>
            
        </div>        
        