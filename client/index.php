<?php
/**
 * client/index.php
 *
 * 06.03.2020 First introduction
 * 27.03.2020 First version ready
 * 16.05.2020 New layout; simplier and more like admin panel
 *            Fisrt introduction of code gallery
 * 
 *
 */

?>
<!DOCTYPE html>
<html>
<head>
    <title>PhotoShare</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Arvo&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="js/transition.js"></script>
    <script src="js/client.js"></script>
</head>

<body>

<div class="container">
	<div class="logo"><img src="content/logo.png" alt="logo"></div>

	<div class="subcontainer">
		<span class="subitem">Searching for a photo? Enter your 8 digt code or use QR-reader</span>
		
		<div class="subitem code form-group">
			<span class="left">CODE</span>
        	<input class="form-field" placeholder="C6DEFC9E" maxlength="8" type="text" id="code" name="code"><br>
    	</div>
    	<div class="subitem username form-group">
    		<span class="left">USERNAME</span>
        	<input class="form-field" placeholder="username" maxlength="20" type="text" id="textusername" name="username"><br>
    	</div>
    	<div class="subitem password form-group">
        	<span class="left">PASSWORD</span>
        	<input class="form-field" placeholder="password" maxlength="50" type="password" id="textpassword" name="username"><br>
    	</div>
    	<div class="subitem submit loginsubmit form-group">
        	<input class="form-field" type="submit" value="Login" id="login" name="login"><br>
    	</div>
    	<!-- navigation -->
    	<nav class="subitem navigation">
        	<ul>
        		<li class="active"><a id="navcode" href="#">enter code</a></li>
        		<li><a id="navlogin" href="#">login</a></li>
        		<li><a id="navadminpage" href="admin/">admin page</a></li>
        		<li><a id="navlogout" href="#">logout</a></li>
        	</ul>
    	</nav>
    	<!-- end of navigation -->
	</div> <!-- end of subcontainer -->

</div><!-- end of container -->

</body>

</html>
