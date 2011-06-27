<?php 
	
	//TODO 
	//update password if password is changed
	//format description
	
	$rooturl = dirname(dirname(dirname(dirname(__FILE__))));;
	require( $rooturl . '/wp-load.php' );
	require( $rooturl . '/wp-includes/registration.php' );
	require( $rooturl . '/wp-includes/user.php' );
	
	$user = $_POST['log'];
	$pass = $_POST['pwd'];
	$urlref = $_SERVER['HTTP_REFERER'];
	$urlref = strtok($urlref, '?');
	
	if(isset($_POST['log'])){
		// Check if member is a member of wordpress
		$wpusername = username_exists( $user );
		if ($wpusername) {
			// If is a member then login and redirect back to page that request originated
			$return = loginUser($user, $pass);
			// If login fails then check that member is member of Imperial and if true then update password with new password - should account for users changing passwords on Imperial system
			if ($return == 'fail') {
				header("Location:$urlref?login=error"); /* Redirect browser */
				exit;
			} else {
				header("Location:$urlref?login=success"); /* Redirect browser */
				exit;
			}
		} else {
			// If not already member check if user is at Imperial
			$member = pam_auth($user,$pass);
			if ($member){
				echo "Imperial Member!";
				include_once(ABSPATH . 'wp-admin/includes/admin.php');
				echo ABSPATH . 'wp-admin/includes/admin.php';
				// If Imperial member then create new user with Imperial information
				$return = createImpUser($user, $pass);
				if ( is_wp_error($return) )
					echo $return->get_error_message();
				// If error message then redirect to request page with error
				
				// Then login user and redirect back to request page
				loginUser($user, $pass);
				header("Location:$urlref?newuser=true"); /* Redirect browser */
				exit;
			} else {
				// Else user is not a wordpress user or member of Imperial
				// Send back error of "Wrong Username or Password"
				echo "Error";
				header("Location:$urlref?login=error"); /* Redirect browser */
				exit;
			}
		}
	} else {
		echo "No username provided.";
		header("Location:$urlref?login=error"); /* Redirect browser */
		exit;
	}
	
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
	
	function format_description($description) {
		$course = $description[0];
		$student_type = $description[1];
		$department = $description[2];
		$degree = $description[3];
		$location = $description[4];
		
		$return_description = $course . "\n" . $student_type . "\n" . $department;
		return $return_description;
	}	
?>