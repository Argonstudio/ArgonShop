<?php

add_action('wp_footer', 'myCabinet', 99); // для фронта
function myCabinet() {
	?>
	
	<script>
	
	
	    function cabinetEditEmailPassword(userID, email, oldEmail, pass, newPass, newPassConfirm){
		    
		    var data = {
    			action: 'cabinetEditEmailPassword',
    			userID: userID,
    			email: email,
    			oldEmail: oldEmail,
    			pass: pass,
    			newPass: newPass,
    			newPassConfirm: newPassConfirm
    		};
    		
		    $.ajax({
                url: myajax.url,
                data: data,
                type: 'POST',
                success: function(response) {
                  
                  var response = JSON.parse(response); 
                  
                  var editEmailError = $(".editEmailError").children("span");
                  var editPassError = $(".editPassError").children("span");
                  var editNewPassError = $(".editNewPassError").children("span");
                  var editNewPassConfirmError = $(".editNewPassConfirmError").children("span");
                  var editResult = $(".editResult").children("span");
                  
                  $(".editError").children("span").removeClass("editErrorActive");
                  $(".editError").children("span").html(" ");
                  
                  //console.log(response["errors"]);
                  
                  if( response["errors"] ){
                      
                      editResult.html("");
                      
                      if( response["errors"]["email"] ){
                          
                          editEmailError.addClass("editErrorActive");
                          editEmailError.html( response["errors"]["email"] )
                      }
                      
                      if( response["errors"]["pass"] ){
                          
                          editPassError.addClass("editErrorActive");
                          editPassError.html( response["errors"]["pass"] )
                          //console.log(editPassError);
                      }
                      
                      if( response["errors"]["newPass"] ){
                          
                          editNewPassError.addClass("editErrorActive");
                          editNewPassError.html( response["errors"]["newPass"] )
                      }
                      
                      if( response["errors"]["newPassConfirm"] ){
                          
                          editNewPassConfirmError.addClass("editErrorActive");
                          editNewPassConfirmError.html( response["errors"]["newPassConfirm"] )
                      }
                      
                  }
                  
                  if( response["edited"] ){
                      
                      
                      $.each(response["edited"], function(index,element){
                          
                          if(index == "newPass"){
                              
                              $("#editNewPass").val("");
                              $("#editNewPassConfirm").val("");
                              
                              editPassError.removeClass("editErrorActive");
                              editPassError.html(" ");
                              
                              editNewPassError.removeClass("editErrorActive");
                              editNewPassError.html(" ");
                              
                              editNewPassConfirmError.removeClass("editErrorActive");
                              editNewPassConfirmError.html(" ");
                              
                          }
                          
                          if(index == "newEmail"){
                              
                              $("#editEmail").attr("data-oldemail", email)
                              
                              editEmailError.removeClass("editErrorActive");
                              editEmailError.html(" ");
                              
                              editPassError.removeClass("editErrorActive");
                              editPassError.html(" ");
                              
                          }
                          
                          if( editResult.html() ){
                              editResult.html( editResult.html() + ', ' + element);
                          }else{
                              editResult.html( element );
                          }
                      
                         
                      
                          
                      })
                      
                      
                  }
                  
                  //console.log(response);
                  
                },
                error: function(data){
                    console.log(data);
                    $(".editResult").children("span").html("Ошибка на сервере");
                }
              });
		    
    		
		}
		
		
	function accountSave(userID, forBack){
	    //console.log(forBack);
	    var data = {
    			action: 'cabinetAccountSave',
    			userID: userID,
    			forBack: forBack
    		};
    		
		    $.ajax({
                url: myajax.url,
                data: data,
                type: 'POST',
                success: function(response) {
                  
                  //var response = JSON.parse(response); 
                  //console.log(1);
                  console.log(response);
                  
                  $(".accountResult").children("span").html(response);
                  
                },
                error: function(data){
                    console.log(data);
                    $(".accountResult").children("span").html("Ошибка: данные не сохранены");
                }
              });
	    
	}

	</script>
	<?php
}

add_action('wp_ajax_cabinetEditEmailPassword', 'cabinetEditEmailPassword_callback');
add_action('wp_ajax_nopriv_cabinetEditEmailPassword', 'cabinetEditEmailPassword_callback');

add_action('wp_ajax_cabinetAccountSave', 'cabinetAccountSave_callback');
add_action('wp_ajax_nopriv_cabinetAccountSave', 'cabinetAccountSave_callback');


function cabinetAccountSave_callback(){
    
    $userID = $_POST['userID'];
    
    //Удаляем экранирование символов
    $data = stripslashes($_POST['forBack']);
    
    //Удаляем php и html теги из строки
    $data = strip_tags( $data );
    
    $data = json_decode( $data, true );        
    
    foreach($data as $dataType => $dataTypeValue){
        
        foreach( $data[$dataType] as $key => $value ){
            
            $data[$dataType][$key] = sanitize_text_field( wptexturize($value) );
            
        }
    }
    
    //Очищаем строку перед использованием в SQL запросе    
    $data = wp_slash( $data );
    
    if(!update_user_meta($userID, "accountData", $data)){
        wp_die("Вы не забыли внести изменения? Новые данные соответствуют старым");
    }
    
    if ( get_user_meta($userID,  'accountData', true ) != $data ){
        wp_die('Ошибка: новые данные не были сохранены');
    }
	
	echo "Информация успешно сохранена";
    
    wp_die();
    
}

function cabinetEditEmailPassword_callback(){
    
    $userID = $_POST['userID'];
    $userdata = get_user_by( 'id', $userID );
    
    $email = $_POST['email'];
    $oldEmail = $_POST['oldEmail'];
    $pass = $_POST['pass'];
    $newPass = $_POST['newPass'];
    $newPassConfirm = $_POST['newPassConfirm'];
    
    $forFront = array(
        
        "edited" => [],
        "errors" => [],
        //"user"=> $userdata
        
        );
    
    ;
    
    if( empty($pass) ){
        
        $forFront["errors"]["pass"] = "Введите пароль";
        
    }else if( !wp_check_password( $pass, $userdata->data->user_pass, $userID) ){
        
        $forFront["errors"]["pass"] = "Пароль указан неверно";
        
    }
    
    if( !empty($newPass) ){
        
        if(strlen($newPass) < 6){
            $forFront["errors"]["newPass"] = "Пароль слишком короткий, введите от 6 символов";
        }
        
        if($newPass !== $newPassConfirm){
            $forFront["errors"]["newPassConfirm"] = "Новый пароль и его подтверждение не совпадают";
        }
        
        
    }
    
    if($email != $oldEmail){
        
        if( email_exists( $email ) && $email != $userdata->user_email ) {
    		$forFront["errors"]["email"] = "Такой email уже зарегистрирован";
    	}
        
        
    }
    
    if( $forFront["errors"] ){
        
        $forFront = wp_json_encode($forFront);
    
        echo $forFront;
        
        wp_die();
        
    }
    
    if($email != $oldEmail){
        
        wp_update_user( array( 
			'ID' => $userID, 
			'user_email' => $email));
        
        
        //update_user_meta( $userID, 'user_email', $email);
        $forFront["edited"]["newEmail"] = "Email изменен";
    }
    
    if( !empty($newPass) ){
        
        wp_set_password( $newPass, $userID );
        
        $creds['user_login'] = $userdata->user_login;
		$creds['user_password'] = $newPass;
		$creds['remember'] = false;
		$user = wp_signon( $creds, false );
        
        $forFront["edited"]["newPass"] = "Пароль изменен, произведена авторизация с новым паролем";
    }
    
    
    $forFront = wp_json_encode($forFront);
    
    echo $forFront;
    
    wp_die();
    
}
