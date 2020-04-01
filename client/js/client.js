/**
 * client.js
 * 
 * @author vipeilko
 * 
 * 23.3.2020 Introduction to handle login information and get response from API
 * 25.3.2020 Update response handling from api and redirecting when login succesfull. 
 * 			   Also changes links login->admin page & logout when tokens is stored.
 * 27.3.2020 Updated more useful post function
 * 
 */

//SETTINGS
// API URL
const apiUrl = 'http://192.168.1.4/PhotoShare/API/';	// Application program interface url
var code = null; 										// Stores latest response code from API

//END SETTINGS

$(document).ready(function() {
	console.log(sessionStorage.getItem('accessToken')); // debug
	console.log(sessionStorage.getItem('refreshToken')); // debug

	//if tokens is already issued display a bit different links
	if ( sessionStorage.getItem('accessToken') != null && sessionStorage.getItem('refreshToken') != null ) {
		//console.log("true"); //debug
		$(".tologinbutton, .tocodebutton").hide();
		$(".toadminpage, .logout").show();
		//$(".tologinbutton").html('<a href="admin/">admin page</a><br><a href="#logout">logout</a>');
	} else {
		//console.log("false"); //debug
		$(".tologinbutton, .tocodebutton").show();
		$(".toadminpage, .logout").hide();
		
	}
	
	//if #login form button is available add listener for doLogin
	if ($("#login").length) {
		$("#login").bind('click', doLogin);		
	}
	if ($("#logout").length) {
		$("#logout").bind('click', doLogout);		
	}

}); //end document ready

/*
 * 
 */
function validateRefreshToken() {
	let data = 
	{
		"serviceName":"validateRefreshToken",
		"param":{
			"parametri":"juu"
		}
	};
	
	postToApi(data);
	if (ans) {
		//if refreshtoken is still valid lets set new access and refreshtokens
		if (ans.response.message.accessToken && ans.response.message.refreshToken) {
			
			sessionStorage.setItem('accessToken', ans.response.message.accessToken);
			sessionStorage.setItem('refreshToken', ans.response.message.refreshToken);

		} else {
			//not recieving access and refrestokens
		}
	}
}

/**
 * ajaxStop
 * 
 * This runs after every ajax function is finished. For example handles redirection on logon.
 * So this helps with asyncronous responses
 * 
 * 
 * @returns
 */
$(document).ajaxStop(function() {
	
	//console.log("ajaxStop code: " + JSON.stringify(code)); //debug
	switch (code) {
		case 200:
			//redirect to admin page
			window.location.replace('admin/index.php');
			break;
		case 201:
			//refreshtoken updated
			break;
		case 666:
			break;
			
		default:
			//do nothing
			break;
	}
	
});

/**
 * 
 * @param dataToPost
 * @returns
 */
function postToApi(dataToPost) {
	let headerType, authHeader;
	// if tokens is already available pass them in header
	if ( sessionStorage.getItem('accessToken') != null && sessionStorage.getItem('refreshToken') != null ) {
		let headerType = 'Authorization';
		let authHeader = 'Bearer ' + sessionStorage.getItem('accessToken');
		//console.log(headerType + ": " + authHeader); //debug
	}

	$.ajax({
		url: apiUrl,
		headers: {
			headerType:authHeader
		},
		type: 'post',
		contentType: 'application/json',
		data: JSON.stringify(dataToPost),
		success: function(data) {
			let str = JSON.stringify(data);
			console.log(str);
			
			//determine which type is response/error/warning
			if (data.hasOwnProperty('response')) {
				code = data.response.status;
			} else if (data.hasOwnProperty('error')) {
				code = data.error.status;
			} else if (data.hasOwnProperty('warning')) {
				code = data.warning.status;
			} else {
				//there is no understandable reply
			}
			
			//console.log(code);
			
			//lets read response
			switch (code) {
				// login
				case 200:
				// refresh token updated
				case 201:
					// if login credentials are ok and response 200, lets store given tokens
					// other case with code 200 is when we are refreshing tokens with refreshtoken, still same procedure 
						if (data.response.message.accessToken && data.response.message.refreshToken) {
							//TODO:how to keep sessions alive when opening new tab in browser?
							sessionStorage.setItem('accessToken', data.response.message.accessToken);
							sessionStorage.setItem('refreshToken', data.response.message.refreshToken);
							return true;
						} else {
							return false;
						}

					break;

				//field is empty but mandatory
				case 103:
				//username error
				case 112:
				//password error
				case 113:
				//highlight all empty fields for few seconds
					 $( "input" ).each( function(){
						let value = $(this).val();
						//console.log(this.id);
						if ( value.length == 0 || this.id == "textusername" && code == 112 || this.id == "textpassword" && code == 113) {
							let originalColor = $(this).css("background-color");
							//console.log(originalColor);
							$(this).animate({'backgroundColor': "#e49b9b"}, 1000, function() {
								$(this).animate({'backgroundColor': originalColor }, 1000);
							});
						}
					 });
					 
					break;
					
				//login failed incorrect password or email; shakes login button
				case 108:
					$(".loginsubmit").effect("shake");
					break;
					
				default:
					
					break;
				
			}
		
		}
	}) //end ajax
} //end postToApi

/**
 * 
 * @returns
 */
function doLogout() {
	sessionStorage.removeItem('accessToken');
	sessionStorage.removeItem('refreshToken');
	window.location.replace('');
}

/**
 * doLogin 
 * 
 * 
 * POSTs ajax login request to api and handles feedback
 * 
 */
function doLogin() {
	let username = $('#textusername').val();
	let password = $('#textpassword').val();
	
	let jsondata = {
		    "serviceName": "generateToken",
		    "param": {
		        "email": username,
		        "password": password
		    }	
	};
	//for quicker test, no need to fill form
	let testdata = {
		    "serviceName": "generateToken",
		    "param": {
		        "email": "testi@domaini.fi",
		        "password": "M0n1muotoinenTest!SalaKala"
		    }	
	};

	postToApi(jsondata);
	//end of call is processed by ajaxStop
} //end doLogin
