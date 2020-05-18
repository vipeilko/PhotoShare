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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="js/transition.js"></script>
    <script src="js/client.js"></script>
</head>

<body>

<div class="container">
	<div class="logo"><img src="content/logo.png" alt="logo"></div>

	<!-- dynamically generated content -->
	<?php 
	/*
	if ( !isset($load) ) {
	    exit("Sorry, Something went wrong...");
	}
	include($load);
	*/
	include 'login.php';
	?>


</div><!-- end of container -->

</body>

</html>
