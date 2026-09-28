<?php

/**
 * Перенаправление пользователя после успешного входа в систему.
 *
 * @param string $ redirect_to URL для перенаправления.
 * @param string $ request URL-адрес пользователя.
 * @param object $ user Записанные данные пользователя.
 * @return string
 */

function my_login_redirect( $redirect_to, $request, $user ) {
	//is there a user to check?
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check for admins
		if ( in_array( 'administrator', $user->roles ) ) {
			// redirect them to the default place
			return $redirect_to;
		} else {
			return get_cabinetPageURL();
		}
	} else {
		return $redirect_to;
	}
}

//Переадресация на главную после выхода
function redirect_after_logout($logout_url, $redirect) {
    if ( empty($redirect) )
        $logout_url = add_query_arg('redirect_to', urlencode( home_url() ), $logout_url);

    return $logout_url;
}


function my_function_admin_bar($content) {
	return ( current_user_can("administrator") ) ? $content : false;
}

add_filter( 'show_admin_bar' , 'my_function_admin_bar');

add_filter('logout_url', 'redirect_after_logout', 10, 2);
add_filter( 'login_redirect', 'my_login_redirect', 10, 3 );


//Переадресация из личного кабинета на страницу регистрации (указываем ID страницы с кабинетом)
add_action( 'template_redirect', function() {
    
	if( !is_user_logged_in() && is_page( get_cabinetPageID() )  ){
		wp_redirect( '/wp-login.php');
		
		exit;
	}
} );