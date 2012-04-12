<?php
    /* 
    Plugin Name: Imperial College User Login
    Plugin URI: http://www.union.ic.ac.uk
    Description: Plugin to allow current and past members of the Imperial College Union to login into wordpress
    Author: Jonathan Kim, Jason Ye
    Version: 0.2
    Author URI: http://jkimbo.com
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
     * Get login box
     * If logged in the display logged in message. Else show login form 
     *
     * Returns string
     */
	function imperial_login_form() {
		if (is_user_logged_in()) {  // if logged in the just show logged in message
			$login_form = gen_imperial_logged_in_message();
		} else { // else show login form
			$wp_content_url = str_replace( 'http://' , 'https://' , get_option( 'siteurl' ) );
			$login_form = '<div id="imperial_login_form">';
			if($_GET['case']==2){ //password changed TODO: remove
				// invite users to relogin due to updating of password not committing til finishing of execution of secure_login.php
				$login_form .='<span id ="login_error">Your IC password has changed since your last visit to the ICHC website. <b>Please login with your new IC login credentials.</b></span>';				
			} else if ($_GET['login'] == error) { // login error
				$login_form .= '<span id="login_error">Woops! Wrong username or password!</span>';
			}
			$login_form .= gen_imperial_login_form($wp_content_url); // get login form
            $login_form .= '</div>';
		}
		return $login_form;
	}

    /*
     * Generate login form
     * Just a generic login form that posts to /wp-login
     *
     * $wp_content_url - site url (https)
     *
     * Returns string
     */
	function gen_imperial_login_form($wp_content_url) {
        ob_start(); ?>
            <form action="<?php echo site_url('wp-login.php', 'login_post') ?>" method="post">
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
                        <td>
                            <label id="rememberme">
                                <input name="rememberme" type="checkbox" value="forever" tabindex="90"<?php checked( $rememberme ); ?> />
                                Remember Me
                            </label>
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
        ob_end_clean();
		
		return $form;
	}
	
    /*
     * Generate logged in message
     *
     * Returns string
     */
	function gen_imperial_logged_in_message() {
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

    /*
     * Authentication filter
     * Override default authentication with Imperial College method
     */
    add_filter( 'authenticate', 'imperial_authenticate', 10, 3 );

    function imperial_authenticate($user, $username, $password) {
        if ( is_a($user, 'WP_User') ) { return $user; }

        if ( empty($username) || empty($password) ) {
            $error = new WP_Error();

            if ( empty($username) )
                $error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.'));

            if ( empty($password) )
                $error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));

            return $error;
        }


        /*
         * Case 1: User authenticates with IC
         *      -> if user is already in database then load user from database and return user
         *      -> else register user with secure random password (username + department + salt) and then return user
         *
         * Case 2: User doesn't authenticate with IC
         *      -> see if user is in database and if is try and authenticate normally
         */
        if(pam_auth($username, $password)) { // if user auths with IC
            if(username_exists($username)) { // user is already in WP
                $userdata = get_user_by('login', $username);
            } else { // user isn't in WP so create new user
                $pass = imperial_generate_password($username);
                $userid = create_imp_user($username, $pass);
                $userdata = get_user_by('id', $userid);
            }
            $user = new WP_User($userdata->ID);
        } else { // if failed login with IC
            $userdata = get_user_by('login', $username);

            if ( !$userdata )
                return new WP_Error('invalid_username', sprintf(__('<strong>ERROR</strong>: Invalid username or password. Please try again.'), site_url('wp-login.php', 'login')));

            $userdata = apply_filters('wp_authenticate_user', $userdata, $password);
            if ( is_wp_error($userdata) )
                return $userdata;

            if ( !wp_check_password($password, $userdata->user_pass, $userdata->ID) )
                return new WP_Error( 'incorrect_password', sprintf( __( '<strong>ERROR</strong>: The password you entered for the username <strong>%1$s</strong> is incorrect. <a href="%2$s" title="Password Lost and Found">Lost your password</a>?' ),
                $username, site_url( 'wp-login.php?action=lostpassword', 'login' ) ) );

            $user =  new WP_User($userdata->ID);
        }

        return $user;
    }

	/*
	 * Creates a new user in wp_users table with given login credentials
	 * @param 
	 * user: the username to be created
	 * pass: the password to be associated with account
	 * @return
	 * user_id of created user
	 */
	function create_imp_user($user, $pass) {
		$user_login = esc_sql($user);
		$user_email = $user."@imperial.ac.uk";
		$user_pass = $pass;
		$fullname = ldap_get_name($user);
        $user_nicename = $fullname;
        $display_name = $fullname;
        $nickname = $fullname;
		$fullname = explode(' ', $fullname);
		$first_name = $fullname[0];
		$last_name = end($fullname);
		$description = ldap_get_info($user);
		$description = imperial_format_description($description);
		$userdata = compact('user_login', 'user_email', 'user_pass', 'user_nicename', 'display_name', 'nickname', 'first_name', 'last_name', 'description');
		$user_id = wp_insert_user($userdata);
		
		return $user_id;
	}

    /*
     * Stub functions for local development
     */
    if(!function_exists('pam_auth')) {
        function pam_auth($user, $pass) {
            return true;
        }
    }

    if(!function_exists('ldap_get_name')) {
        function ldap_get_name($user) {
            return "Keith O'Nions";
        }
    }

    if(!function_exists('ldap_get_info')) {
        function ldap_get_info($user) {
            $info = array(
                'Rector', 
                'Bossman',
                'Ivory Tower',
                'Playgroup',
                'Ball Pit'
            );
            return $info;
        }
    }

	/*
	 * Helper function to parse the description array obtained via ldap
	 * @param 
	 * description: an array obtained through ldap_get_info() function
	 * @return
	 * string detailing the course details of person
	 */
	function imperial_format_description($description) {
		$course = $description[0];
		$student_type = $description[1];
		$department = $description[2];
		$degree = $description[3];
		$location = $description[4];
		
		$return_description = $course . "\n" . $student_type . "\n" . $department;
		return $return_description;
	}

    /*
     * Generate a secure password
     * Because we are not allowed to store users passwords in database we create a random one to store instead
     *
     * Returns string
     */
    function imperial_generate_password($username) {
        $info = ldap_get_info($username);
        $plain = $user+$info[2]+wp_salt();
        $hash = wp_hash_password($username);
        return $hash;
    }
?>
