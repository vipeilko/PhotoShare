
$(document).ready(function() {
	
	//Hide login form
	//$(".item6, .code").hide();
	//hide code
	$(".username, .password, .submit, .item13").hide();
	
	
	
	$(".item12").click(function() {
		$(".item6, .code").hide(1000);
		$(".username").delay(0).show(1000);
		$(".password").delay(500).show(1000);
		$(".submit").delay(1000).show(1000);
		$(".item12").hide(1000);
		$(".item13").show(1000);
	});
	
	$(".item13").click(function() {
		$(".submit").delay(0).hide(1000);
		$(".password").delay(500).hide(1000);
		$(".username").delay(1000).hide(1000);
		$(".item6, .code").delay(1500).show(1000);
		$(".item13").hide(1000);
		$(".item12").show(1000);
	});
	
});