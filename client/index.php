<?php




?>
<!DOCTYPE html>
<html>
<head>
    <title>PhotoShare</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Arvo&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="js/transition.js"></script>
    <script src="js/client.js"></script>
</head>

<body>

<div class="container">
	<!-- top row -->
	<div class="item item1"></div>
	<div class="item item2">PhotoShare</div>
	<div class="item item3"></div>
	
	<!-- middle row -->
	<div class="item item4"></div>
	
	<div class="item item5">
		<div class="subitem item6">Searching for a photo? Enter your 8 digt code or use qr reader.</div>
		
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
	</div>
	
	
	
	
	<div class="item item8"></div>

	<!-- bottom row -->
	<div class="item item9"></div>
	<div class="item item10"></div>
	<div class="item item11">
		<div class="subitem item12 tologinbutton">login</div>
		<div class="subitem item13 tocodebutton">code</div>

	</div>
	
</div><!-- end of container -->


		<!--	<form>
        		<input maxlength="8" size="12" value="12345678" type="text" id="code" name="code">
        		<input type="submit" id="send">
    		</form>-->

</body>

</html>
