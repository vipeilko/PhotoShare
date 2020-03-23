/**
 * client.js
 * 
 * @author vipeilko
 * 
 * + 23.3.2020 Introduction to handle login information and get response from API
 * 
 * 
 */

const apiUrl = 'http://192.168.1.4/PhotoShare/API/';
document.addEventListener('DOMContentLoaded', () =>  {
	
	//TODO: which way is better and more readable?
	//$("#login").addEventListener('click', doLogin);
	document.getElementById('login').addEventListener('click', doLogin);
}); //end of DOMContentLoaded

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
		        "password": "M0n1muotoinenTest!SalaKala",
		        "parametri": "juu",
		        "toinen_parametri": "joo"
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
			}
			else if (data.hasOwnProperty('error')) {
				code = data.error.status;
			} else if (data.hasOwnProperty('warning')) {
				code = data.warning.status;
			} else {
				//there is no understandable reply
			}
			
			//lets read response
			switch (code) {
				case 200:
					//if login credentials are ok and response 200, lets store given tokens
					if (data.response.message.accessToken && data.response.message.refreshToken) {
						sessionStorage.setItem('accessToken', data.response.message.accessToken);
						sessionStorage.setItem('refreshToken', data.response.message.refreshToken);
					} else {
						//not recieving access and refrestokens
					}
					break;
				case 103:
					//datatype not valid (display just message for few secs and then hide)
					break;
					
				default:
					
					break;
				
			}
			
			

			/*for ( x in data ) {
				console.log(data[x]);
			}*/
		}
		/*headers: {
			
		}*/
		
	})
}
