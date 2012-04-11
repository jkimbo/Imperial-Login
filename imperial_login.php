<?php
    /* 
    Plugin Name: Imperial College User Login
    Plugin URI: http://www.union.ic.ac.uk
    Description: Plugin to allow current and past members of the Imperial College Union to login into wordpress
    Author: Jonathan Kim, Jason Ye
    Version: 0.2
    Author URI: http://www.jkimbo.com
	License: GPL2
	
		Copyright 2011 Jason Ye (email : jason.ye@me.com)
			  2011 Jonathan Kim (email : jkimbo@gmail.com)

	    This program is free software; you can redistribute it and/or modify
	    it under the terms of the GNU General Public License, version 2, as 
	    published by the Free Software Foundation.

	    This program is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU General Public License for more details.

	    You should have received a copy of the GNU General Public License
	    along with this program; if not, write to the Free Software
	    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    */  
	
    /*
     * Get login form
     *
     * Returns string
     */
	function imperial_login_form() {
		if (is_user_logged_in()) {  // if logged in the just show logged in message
			$login_form = logged_in_message();
		} else { // else show login form
			$wp_content_url = str_replace( 'http://' , 'https://' , get_option( 'siteurl' ) );
			$login_form = '<div id="imperial_login_form">';
			if($_GET['case']==2){ //password changed TODO: remove
				// invite users to relogin due to updating of password not committing til finishing of execution of secure_login.php
				$login_form .='<span id ="login_error">Your IC password has changed since your last visit to the ICHC website. <b>Please login with your new IC login credentials.</b></span>';				
			} else if ($_GET['login'] == error) { // login error
				$login_form .= '<span id="login_error">Woops! Wrong username or password!</span>';
			}
			$login_form .= login_form($wp_content_url); // get login form
            $login_form .= '</div>';
		}
		return $login_form;
	}

    /*
     * Generate login form
     *
     * $wp_content_url - site url (https)
     *
     * Returns string
     */
	function login_form($wp_content_url) {
        ob_start(); ?>
            <form action="<?php echo $wp_content_url;?>/wp-content/plugins/imperial_login/secure_login.php" method="POST">
                <table>
                    <tr>
                        <td>
                            <label for="log" id="imp_name_label">IC Username: </label>
                        </td>
                        <td>
                            <input type="text" name="log" id="imp_name" class="required" minlength="4"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="pwd" id="imp_pass_label">Password: </label>
                        </td>
                        <td>
                            <input type="password" name="pwd" id="imp_pass" class="required"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <input type="submit" value="Login" id="imp_login_submit"/>
                        </td>
                    </tr>
                </table>
            </form>
        <?php 
        $form = ob_get_contents();
        $ob_end_clean();
		
		return $form;
	}
	
    /*
     * Generate logged in message
     *
     * Returns string
     */
	function logged_in_message() {
		global $current_user;
		get_currentuserinfo(); 
        ob_start(); ?>
            <div id="userDetails">
                <a href="<?php echo get_bloginfo('url'); ?>/author/<?php echo $current_user->user_login; ?>"><?php 
                    if ($current_user->user_firstname != "") {
                        echo $current_user->user_firstname." ".$current_user->user_lastname; 
                    } else { 
                        echo $current_user->user_login;
                    } 
                ?></a> <a href="<?php echo esc_url(wp_logout_url($_SERVER['REQUEST_URI'])); ?>">Logout</a>
            </div>
            
            <?php
                // If user can add posts
                if (current_user_can( 'publish_posts')){ ?>
                    <div>
                        <a href="<?php echo get_bloginfo('url'); ?>/wp-admin">Admin</a>
                    </div>
            <?php } ?>
        <?php
        $message = ob_get_contents();
        ob_end_clean();

		return $message;
	}
	
	
	/*
	 * Imperial_Login Class
	 */
	class Imperial_Login extends WP_Widget {
		/** constructor */
		function __construct() {
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
            echo imperial_login_form();
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
	add_action('widgets_init', create_function('', 'return register_widget("Imperial_Login");'));
?>
