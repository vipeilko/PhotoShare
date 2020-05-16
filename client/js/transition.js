
$(document).ready(function() {
	//Hide login form
	$(".username, .password, .submit").hide();
	
	
	// hides code form and shows login form
	$("#navlogin").click(function() {
		$(".code").hide(1000);
		$(".username").delay(0).show(1000);
		$(".password").delay(500).show(1000);
		$(".submit").delay(1000).show(1000);
	});
	// hides login form and shows code form
	$("#navcode").click(function() {
		$(".submit").delay(0).hide(1000);
		$(".password").delay(500).hide(1000);
		$(".username").delay(1000).hide(1000);
		$(".code").delay(1500).show(1000);

	});
	
});