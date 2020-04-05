<?php
/** 
 * client/admin/index.php 
 * 
 * 1.4.2020 First introduction, still work to do with layout
 * 2.4.2002 Layout fixes and additions
 * 
 */



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
		<div class="subitem tools">
			<h2>ADMINISTRATIVE</h2>
			<span class="subheader">TOOLS</span>
		</div>
		<nav class="subitem navigation">
			<a id="main" href="#main">main</a>
			<a id="user" href="#users">users</a>
			<a id="code" href="#code">codes</a>
			<a id="event" href="#event">events</a>
			<a id="logout" href="#logout">logout</a>
		</nav>
		<!-- maybe one more for logout? -->
	</div><!-- end of left -->
	
	<div class="item right">
		<div class="subitem header logo"><img src="../content/logo.png" alt="logo"></div>
		<div id="rightcontainer" class="rightcontainer">
    		<!-- all dynamic contents is loaded here -->

		</div><!-- end of rightcontainer -->
		
	</div><!-- end of right -->
	
	

</div><!-- end of container -->


</body>

</html>