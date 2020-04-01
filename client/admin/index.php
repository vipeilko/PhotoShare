<?php




?>
<!DOCTYPE html>
<html>
<head>
    <title>PhotoShare</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Arvo&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="js/aclient.js"></script>
</head>

<body>

<div class="container"><!-- container -->
	<div class="item left">
		<div class="subitem tools">tooolzlogo</div>
		<nav class="subitem navigation">navigation</nav>
		<!-- maybe one more for logout? -->
	</div><!-- end of left -->
	
	<div class="item right">
		<div class="subitem header logo"><img src="../content/logo.png" alt="logo"></div>
		<div class="rightcontainer">
    		<h1 id="contentheader">USER SETTINGS</h1>
    		<div class="subitem content">
    			<div class="subitem secondsubitem currentusers">
    			<span class="subitemheader">current users</span>
    				<select name="userlist" id="userlist" size="21" multiple="multiple">
    					<option value="0">add user</option>
    					<option disabled>───────────────</option>
    					<option value="1">Ville Kouhia</option>
    					<option value="2">Petteri Testi</option>
    					<option value="3">Tero Testaa</option>
    				</select>
    				<input id="submitRemoveUsers" type="submit" value="delete selected users">
    			</div>
    			
    			<div class="subitem secondsubitem adduser edituser">
    				<span class="subitemheader">add user</span>
    				<input id="firstname" type="text" placeholder="Charlie">
    				<input id="lastname" type="text" placeholder="Brown">
    				<input id="email" type="text" placeholder="charlie.brown@cartoo.ns">
    				<input id="password" type="password" placeholder="password">
    				<input id="retypepassword" type="password" placeholder="re-type password again">
    				<span class="subitemheader">user permissions</span>
    				<label><input id="perm_user_add" type="checkbox"><span>add users</span></label>
    				<label><input id="perm_user_edit" type="checkbox"><span>edit users</span></label>
    				<label><input id="perm_user_delete" type="checkbox"><span>delete users</span></label>
    				<span class="subitemheader">code permissions</span>
    				<label><input id="perm_code_generate" type="checkbox"><span>generate codes</span></label>
    				<label><input id="perm_code_clear" type="checkbox"><span>clear unused codes</span></label>
    				<label><input id="perm_code_process" type="checkbox"><span>process images</span></label>
					<span class="subitemheader">event permissions</span>
    				<label><input id="perm_event_create" type="checkbox"><span>create new events</span></label>
    				<label><input id="perm_event_edit" type="checkbox"><span>edit events</span></label>
    				<label><input id="perm_event_delete" type="checkbox"><span>delete events</span></label>    			
					<span class="subitemheader">event permissions</span>
    				<label><input id="perm_event_create" type="checkbox"><span>create new events</span></label>
    				<label><input id="perm_event_edit" type="checkbox"><span>edit events</span></label>
    				<label><input id="perm_event_delete" type="checkbox"><span>delete events</span></label>    	
    				<input id="submitRemoveUsers" type="submit" value="create new user">
    			</div>
    			
    		</div> <!-- end of content -->
		</div><!-- end of rightcontainer -->
		
	</div><!-- end of right -->
	
	

</div><!-- end of container -->


</body>

</html>