<?php
?>
			<h1 id="contentheader">USER SETTINGS</h1>
    		<div class="subitem content">
    			<div class="subitem secondsubitem currentusers">
    			<span class="subitemheader">current users</span>
    				<select class="list" name="userlist" id="userlist" size="21">

    				</select>
    				<input id="submitRemoveUser" type="submit" value="delete selected user">
    			</div>
    			
    			<div class="subitem secondsubitem adduser edituser">
    				<span class="subitemheader">add user</span>
    				<input id="firstname" type="text" placeholder="Charlie">
    				<input id="lastname" type="text" placeholder="Brown">
    				<input id="email" type="text" placeholder="charlie.brown@cartoo.ns">
    				<input id="password" type="password" placeholder="password">
    				<input id="retypepassword" type="password" placeholder="re-type password again">
    				<div id="permissions" class="subitem adduser edituser secondsubitem">
					<!-- Dynamic content is created here -->	
    				</div>
    				<input id="submitCreateUser" type="submit" value="create new user">
    				<input id="submitEditUser" type="submit" value="edit user">
    				
    				
    			</div>
    			
    		</div> <!-- end of content -->