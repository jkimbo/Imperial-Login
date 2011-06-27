<?php 
	//TODO
	//format description
	
	$rooturl = dirname(dirname(dirname(dirname(__FILE__))));;
	require( $rooturl . '/wp-load.php' );
	require( $rooturl . '/wp-includes/registration.php' );
	require( $rooturl . '/wp-includes/user.php' );
	
	$user = $_POST['log'];
	$pass = $_POST['pwd'];
	$urlref = $_SERVER['HTTP_REFERER'];
	$urlref = strtok($urlref, '?');
	
	/*
		Case 1: IC and WP credentials match - majority of time (success)
		Case 2: IC password different from wordpress password: hence update WP password and get them to relogin with new details
		Case 3: IC login successful and not in WP yet: hence create new account (newuser)
		Case 4: Username in neither IC nor WP; or alumni password wrong (error)
		Case 5: Username not in IC db; Password correct = Alumni logged in = OK (success)
		Case 6: Username in IC db; Password incorrect = idiot. (error)
		Case 7: Username field not filled in
	*/
	
	// is username field filled in?
	if(isset($_POST['log'])){
		
		// find out if credentials are in Wordpress
		$wpusername = username_exists( $user );
		//check if credentials are correct for Imperial user
		$member=pam_auth($user,$pass);
		if($member){ //IC login successful
			
			// Check if member is a member of wordpress
			if ($wpusername) { // WP credentials = IC credentials
				
				//attempt to log into WP
				$return = loginUser($user, $pass);
				
				if($return=='fail'){ 
					// case 2: passwords inconsistent
					//change WP pw to new validated IC pw
					change_wp_password($user,$pass);
					// case 2: changed password failed
					header("Location:$urlref?login=error&case=2");
					exit;
				}
				
				//case 1: IC and WP credentials match - majority of time (success)
				header("Location:$urlref?login=success&case=1");
				exit;
				
				
			} else { 
				// case 3: new valid IC visitor - create new WP user
				echo "Imperial Member!";
				include_once(ABSPATH . 'wp-admin/includes/admin.php');
				echo ABSPATH . 'wp-admin/includes/admin.php';
				// Create new user with Imperial information
				$return = createImpUser($user, $pass);
				if ( is_wp_error($return) )
					echo $return->get_error_message();
				// If error message then redirect to request page with error
				
				// Then login user and redirect back to request page
				loginUser($user, $pass);
				header("Location:$urlref?newuser=true&case=3"); /* Redirect browser */
				exit;
			}
			
		} else if(!ldap_get_name($user)){ // username not in IC database - see whether previously logged in to wordpress successfully?
			//check wp login
			$return = loginUser($user, $pass);
			if ($return == 'fail') {
				// case 4: wrong username & wrong password
				header("Location:$urlref?login=error&case=4"); /* Redirect browser */
				exit;
			} else { 
				// case 5: correct alumni user
				header("Location:$urlref?login=success&case=5"); /* Redirect browser */
				exit;
			}
			
		} else {
			// Case 6: valid IC username, wrong password
			echo "Error";
			header("Location:$urlref?login=error&case=6"); /* Redirect browser */
			exit;
		}
		
	} else {
		//Case 7: Username field not filled in 
		echo "No username provided.";
		header("Location:$urlref?login=error&case=7"); /* Redirect browser */
		exit;
	}
	
	/*
	* Attempts to login specified user with the given password into WordPress
	* @param 
	* user: the username to be tested
	* pass: the password to be tested
	* @return
	* result of login
	*/
	function loginUser($user, $pass) {
		$creds = array();
		$creds['user_login'] = $user;
		$creds['user_password'] = $pass;
		$creds['remember'] = true;
		$user_nicename = ldap_get_name($user);
		$user = wp_signon( $creds, false );
		if ( is_wp_error($user) )
			return "fail";
		return "Logged in!";
	}
	
	/*
	* Creates a new user in wp_users table with given login credentials
	* @param 
	* user: the username to be created
	* pass: the password to be associated with account
	* @return
	* user_id of created user
	*/
	function createImpUser($user, $pass) {
		$user_login = esc_sql($user);
		$user_email = $user."@imperial.ac.uk";
		$user_pass = $pass;
		$fullname = ldap_get_name($user);
		$fullname = explode(' ', $fullname);
		$first_name = $fullname[0];
		$last_name = end($fullname);
		$user_id = $user_email;
		$description = ldap_get_info($user);
		$description = format_description($description);
		//$description = implode(",", $description);
		$userdata = compact('user_login', 'user_email', 'user_pass', 'first_name', 'last_name', 'description');
		$user_id = wp_insert_user($userdata);
		
		return $user_id;
	}
	
	/*
	* Helper function to parse the description array obtained via ldap
	* @param 
	* description: an array obtained through ldap_get_info() function
	* @return
	* string detailing the course details of person
	*/
	function format_description($description) {
		$course = $description[0];
		$student_type = $description[1];
		$department = $description[2];
		$degree = $description[3];
		$location = $description[4];
		
		$return_description = $course . "\n" . $student_type . "\n" . $department;
		return $return_description;
	}
	
	/*
	* Changes password of user to specified new password $pass in wp_users table
	* @param 
	* user: the username of the user account to be changed
	* pass: the new password
	* @return
	* none
	*/
	function change_wp_password($user,$pass){
		global $wpdb;
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		$hashpass=wp_hash_password($pass);
		$wpdb->update('wp_users', array('user_pass'=>$hashpass), array('user_login'=>$user), array('%s'), array('%s') );
	}
	
?>