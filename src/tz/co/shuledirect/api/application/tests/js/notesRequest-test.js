/**
This file will be used to test web api calls. 
We simply write as many unit tests we want and 
use the qunit.html file to see our results.

Note: These are only to test whether an API call 
from an external source receives a proper response.
The actual testing of validation of the data and 
interactions with the db are NOT tested in this file. 

*/


// IMPORT JQUERY!!!!!!
var EC2_URL = "ec2-54-200-106-165.us-west-2.compute.amazonaws.com";

function ajaxRequest(methodURL, inputJson, handler) {
    var eurl = "/api/index.php/" + methodURL;
    var params = 'inputJson='+ inputJson;   
    
    var post = $.ajax({
    	type: "POST",
    	url: eurl,
    	data: params
    });

    post.done(function(result) {
        handler(result);
    });

    post.fail(function() {
        handler('error');
    });
}


asyncTest('getAugmentedNotes test1', function() {
    var result = 0;
    var methodURL = "notes/getAugmentedNotes";
    var inputJson = '{"id": 123}';

    var output = '123';

    ajaxRequest(methodURL,inputJson,function(response) {
        result = response;

	    equal(result, output, "The result is exactly as expected");
	    start();
        
    });

});
