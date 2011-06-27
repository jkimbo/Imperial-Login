<?php
	/* 
    Plugin Name: Imperial College User Login
    Plugin URI: http://www.union.ic.ac.uk
    Description: Plugin to allow members of the Imperial College Union to login into wordpress
    Author: J. Kim
    Version: 0.1
    Author URI: http://www.jkimbo.com
    */  
	
	function imperial_login_form() {
		
		//$login_form = "Hello World";
		if (is_user_logged_in()) { 
			$login_form = logged_in_message();
		} else {
			$wp_content_url = str_replace( 'http://' , 'https://' , get_option( 'siteurl' ) );
			$login_form = '<div id="imperial_login_form">';
			if ($_GET['login'] == error) $login_form .= '<span id="login_error">Woops! Wrong username or password!</span>';
			$login_form = append_login_form($login_form, $wp_content_url);
		}
		echo $login_form;
	}

	function append_login_form($login_form, $wp_content_url) {
	
		$login_form .= '<form action="'.$wp_content_url.'/wp-content/plugins/imperial_login/secure_login.php" method="POST">';
		$login_form .= '<table><tr>';
		$login_form .= '<td><label for="log" id="imp_name_label">IC Username: </label></td><td><input type="text" name="log" id="imp_name" class="required" minlength="4"/></td>';
		$login_form .= '</tr><tr>';
		$login_form .= '<td><label for="pwd" id="imp_pass_label">Password: </label></td><td><input type="password" name="pwd" id="imp_pass" class="required"/></td>';
		$login_form .= '</tr><tr>';
		//$login_form .= '<td><a target="_blank" href="http://www3.imperial.ac.uk/ict/services/securitynetworkdatacentreandtelephonyservices/security/securitypolicies/passwords/changingyourpassword">Forgotten password?</a></td>';
		$login_form .= '<td colspan=2><input type="submit" value="Login" id="imp_login_submit"/></td>';
		$login_form .= '</tr></table></form>';
		$login_form .= '</div>';
		
		return $login_form;
	}
	
	function logged_in_message() {
		global $current_user;
		get_currentuserinfo(); 
		$login_form = '<div id="userDetails"><a href="'.get_bloginfo('url').'/author/'.$current_user->user_login.'">';
		if ($current_user->user_firstname != "") {
			$login_form .= $current_user->user_firstname." ".$current_user->user_lastname; 
		} else { 
			$login_form .= $current_user->user_login;
		} 
		$login_form .= '</a>';
		$login_form .= ' <a href="'.esc_url(wp_logout_url($_SERVER['REQUEST_URI'])).'">Logout</a></div>';
		
		// If user can add posts
		if (current_user_can( 'publish_posts')){
			$login_form .= '<div> <a href="'.get_bloginfo('url').'/wp-admin" >Admin</a> </div>';
		}
		return $login_form;
	}
	
	
	/**
	* imperial_login Class
	*/
	class imperial_login extends WP_Widget {
		/** constructor */
		function imperial_login() {
			parent::WP_Widget(false, $name = 'Imperial Login');	
		}

		/** @see WP_Widget::widget */
		function widget($args, $instance) {		
			extract( $args );
			$title = apply_filters('widget_title', $instance['title']);
			$logintitle = apply_filters('widget_login_title', $instance['logintitle']);
				
				echo $before_widget; 
				if (!is_user_logged_in()) {
					if ( $title )
						echo $before_title . $title . $after_title;
				} else {
					if ( $logintitle )
						echo $before_title . $logintitle . $after_title;
				}
				imperial_login_form();
				echo $after_widget;
		}

		/** @see WP_Widget::update */
		function update($new_instance, $old_instance) {				
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['logintitle'] = strip_tags($new_instance['logintitle']);
			return $instance;
		}	

		/** @see WP_Widget::form */
		function form($instance) {				
			$title = esc_attr($instance['title']);
			$logintitle = esc_attr($instance['logintitle']);
			?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Form Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('logintitle'); ?>"><?php _e('Logged in Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('logintitle'); ?>" name="<?php echo $this->get_field_name('logintitle'); ?>" type="text" value="<?php echo $logintitle; ?>" /></label></p>
			<?php 
		}

	} // class imperial_login
	
	// register imperial_login widget
	add_action('widgets_init', create_function('', 'return register_widget("imperial_login");'));
?>
