    
    <?php
        $userdata = get_user_by( 'id', $user_ID );
    ?>
    
        
        <div class="block-settingForm">
                    
            <div class="block-settingField50">
                
                <div class="settingFieldTitle">Email:</div>
                
                <div class="block-settingFieldEmail">
                    
                    <input type="email" class="settingFieldInput" id="editEmail" data-oldEmail="<?php echo $userdata->user_email ?>" value="<?php echo $userdata->user_email ?>" disabled/>
                    
                    <span id="editEmailActive">Изменить</span>
                    
                    <div class="editError editEmailError"> <span></span> </div>
                    
                </div>
                
            </div>
                        
            
                        
            <div class="block-settingField50">
                
                <div class="settingFieldTitle">Старый пароль:</div>
                <input type="password" class="settingFieldInput" id="editOldPass" />
                
            </div>
                        
            <div class="editError editPassError"> <span></span> </div>
                        
            <div class="block-settingField50">
                
                <div class="settingFieldTitle">Новый пароль</div>
                <input type="password" class="settingFieldInput" id="editNewPass" />
                
            </div>
                        
            <div class="editError editNewPassError"> <span></span> </div>
                        
            <div class="block-settingField50">
                
                <div class="settingFieldTitle">Повторите новый пароль</div>
                <input type="password" class="settingFieldInput" id="editNewPassConfirm" />
                
            </div>
                        
            <div class="editError editNewPassConfirmError"> <span></span> </div>
            
        </div>
        
        <div class="block-settingSave">
            
            <input type="button" data-userid="<?php echo $user_ID ?>" id="editEmailPasswordButton" value="Сохранить" />
            <div class="editResult"><span></span></div>
            <div class="consentPersonalData">Нажимая сохранить Вы предоставляете согласие на <a href="/konfidentsialnost-personalnoj-informatsii/" target="_self">обработку ваших персональных данных</a></div>
            
        </div>   
        
        <a href="<?php echo wp_logout_url(); ?>" class="exitSite" title="Выход">Выход</a>