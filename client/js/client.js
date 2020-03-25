/**
 * client.js
 * 
 * @author vipeilko
 * 
 * + 23.3.2020 Introduction to handle login information and get response from API
 * + 25.3.2020 Update response handling from api and redirecting when login succesfull. 
 * 			   Also changes links login->admin page & logout when tokens is stored.
 * 
 */

//SETTINGS
// API URL
const apiUrl = 'http://192.168.1.4/PhotoShare/API/';

//END SETTINGS

document.addEventListener('DOMContentLoaded', () =>  {
	console.log(sessionStorage.getItem('accessToken')); // debug
	console.log(sessionStorage.getItem('refreshToken')); // debug

	//if tokens is already issued display a bit different links
	if ( sessionStorage.getItem('accessToken') != null && sessionStorage.getItem('refreshToken') != null ) {
		$(".tologinbutton").html('<a href="admin/">admin page</a><br><a href="#logout">logout</a>');
	}
	
	//if #login form button is available add listener for doLogin
	if ($("#login").length) {
		$("#login").bind('click', doLogin);		
	}

}); //end of DOMContentLoaded


/**
 * doLogin 
 * 
 * 
 * POSTs ajax login request to api and handles feedback
 * 
 */
function doLogin(ev) {
	let username = $('#textusername').val();
	let password = $('#textpassword').val();
	
	let h = new Headers();
	
	let jsondata = {
		    "serviceName": "generateToken",
		    "param": {
		        "email": username,
		        "password": password
		    }	
	};
	
	let testdata = {
		    "serviceName": "generateToken",
		    "param": {
		        "email": "testi@domaini.fi",
		        "password": "M0n1muotoinenTest!SalaKala"
		    }	
	};
	
	let dataToPost = JSON.stringify(jsondata);
	//let dataToPost = JSON.stringify(testdata);
	
	$.ajax({
		url: apiUrl,
		type: 'post',
		contentType: 'application/json',
		data: dataToPost,
		success: function(data) {
			let str = JSON.stringify(data);
			console.log(str);
			
			let code;
			
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
				case 200:
					//if login credentials are ok and response 200, lets store given tokens
					if (data.response.message.accessToken && data.response.message.refreshToken) {
						//TODO:how to keep sessions alive when opening new tab in browser?
						sessionStorage.setItem('accessToken', data.response.message.accessToken);
						sessionStorage.setItem('refreshToken', data.response.message.refreshToken);
						//redirect to admin page
						window.location.replace('admin/index.php');
					} else {
						//not recieving access and refrestokens
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
} //end doLogin
