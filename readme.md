# Imperial College Login Form

*Author: Jonathan Kim and Jason Ye*

## Plugin to allow members of the Imperial College Union to login into wordpress

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

## Notes

You need to add `define('FORCE_SSL_LOGIN', true);` to wp-config for `pam_auth` to work
Add `imperial_login_form()` (with php tags around it) to the part of your page that you want to display the login form. 
Plugin arguments existing wordpress login system by first checking if the username and password is a valid Imperial College user. If it is then creates a new user with Imperial details. If not then just follows standard wordpress procedure. 

Inputs are ready to be validated using the validation jquery plugin: http://bassistance.de/jquery-plugins/jquery-plugin-validation/
Name input has minlength of 4

Will return:
	?login=success if login is successful
	?login=error if login is unsuccessful
	?newuser=true if new user is created (if first user login)
	
## CSS id
Form is enclosed in a div with id = `imperial_login_form`
Label for name id = `imp_name_label`
Label for password id= `imp_pass_label`
Input for name id = `imp_name`
Input for password id = `imp_pass`
Submit button id = `imp_login_submit`

## TODO
Return value if first time user has logged onto site - recommend that he/she edits their profile etc..
