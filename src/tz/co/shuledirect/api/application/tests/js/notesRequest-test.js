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

function ajaxRequest(handler) {
    var EC2_URL = "ec2-54-200-106-165.us-west-2.compute.amazonaws.com";
    var eurl = "/api/index.php/" + "notes/getAugmentedNotes";
    var params = 'inputJson='+ '{"id":123}';   
    
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


asyncTest('asyncronous text', function() {
    var result = 0;
    
    //ok(true);
    console.log(result);
    ajaxRequest(function(response) {
        result = response;
        console.log(result);
	    equal(result, '123', "YEAH BUDDY");
	    start();
        console.log("blah");
    });

});
