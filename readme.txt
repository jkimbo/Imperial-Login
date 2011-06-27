==Imperial College Login Form==
Author: J. Kim

Plugin to allow members of the Imperial College Union to login into wordpress

==Notes==

Add "imperial_login_form()" (with php tags around it) to the part of your page that you want to display the login form. 
It will then display a simple username and password form that sends the information (over ssl) to a login file that does the following: 
	Checks whether user is already a member of the wordpress site - logs user in if true and redirects to referal site
	If not a member checks if member of Imperial -  if true then creates user with details from Imperial (username and full name) and then logs user in and returns to referal page
	If not a member of either wordpress or Imperial then redirects to referal url and displays error above form
	
Inputs are ready to be validated using the validation jquery plugin: http://bassistance.de/jquery-plugins/jquery-plugin-validation/
Name input has minlength of 4

Will return:
	?login=success if login is successful
	?login=error if login is unsuccessful
	?newuser=true if new user is created (if first user login)
	
==CSS id==
Form is enclosed in a div with id = "imperial_login_form"
Label for name id = "imp_name_label"
Label for password id= "imp_pass_label"
Input for name id = "imp_name"
Input for password id = "imp_pass"
Submit button id = "imp_login_submit"

==TODO==
Return value if first time user has logged onto site - recommend that he/she edits their profile etc..