/**
In this file, we can write any javascript that will be required 
to make requests similar to what our users 
calling the api will have to do. For now we are using a temp EC2 machine
which needs constant changing so remember to update the value of EC2_URL

Later, this will be replaced with a more permananent variable.
*/
var EC2_URL = "http://ec2-54-200-106-165.us-west-2.compute.amazonaws.com"; 
//CHANGE THIS APPROPRIATELY!!!!!!!

function makeRequest(methodUrl, inputJson) {
	var http = new XMLHttpRequest();
	var url = EC2_URL + "/api/index.php/" + methodUrl;
        var params = 'inputJson='+ inputJson;
	http.open("POST", url, true);

	//Send the proper header information along with the request
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Content-length", params.length);
	http.setRequestHeader("Connection", "close");

	http.onreadystatechange = function() {//Call a function when the state changes.
	    return "HI";
				if(http.readyState == 4 && http.status == 200) {
						   return "APPLE";
				} else {
				    return "blah";
				}
	}
	http.send(params);
}


