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


/**
Test 1

We run the db scripts
Call getId of Form I
addContent()
getId of the content just added
getAugmented notes of the id
(make sure this is the same as the content we put in!)


*/
asyncTest('addContent, getId and getAugmentedNotes test1', function() {

    //getId of form I
    ajaxRequest("notes/getId",'{"form": "Form 1"}',function(response) {
        var formId = response; 
        equal(response, 2, "The id of form 1 should be 2");
        start();
    });

    //now we add some subjects
    var subjectJson1 = '{"parentId":' + formId + ',"content":"Physics"';
    var subjectJson2 = '{"parentId":' + formId + ',"content":"Chemistry"';
    var subjectJson3 = '{"parentId":' + formId + ',"content":"History"';
    ajaxRequest("notesAdmin/addContent", subjectJson1,function(response) {
        equal(response, true, "added physics as a subject");
        start();
    });

    ajaxRequest("notesAdmin/addContent", subjectJson2,function(response) {
        equal(response, true, "added chemistry as a subject");
        start();
    });

    ajaxRequest("notesAdmin/addContent", subjectJson3,function(response) {
        equal(response, true, "added history as a subject");
        start();
    });

    //we call getId of physics that we just added
    ajaxRequest("notes/getId",'{"form": "Form 1", "subject": "Physics"}',function(response) {
        var subjectId = response; 
        ok(response, "The request went through");
        start();
    });

    //add some topics to physics
    var topicJson1 = '{"parentId":' + subjectId + ',"content":"Mechanics"';
    var topicJson2 = '{"parentId":' + subjectId + ',"content":"Electricity"';

    ajaxRequest("notesAdmin/addContent", topicJson1,function(response) {
        equal(response, true, "added mechanics as a topic");
        start();
    });

    ajaxRequest("notesAdmin/addContent", topicJson2,function(response) {
        equal(response, true, "added electricity as a topic");
        start();
    });

    //we call getId of Mechanics that we just added
    ajaxRequest("notes/getId",'{"form": "Form 1", "subject": "Physics", "topic":"Mechanics"}',function(response) {
        var topicId = response; 
        ok(response, "The request went through");
        start();
    });    

    //add some subTopics to physics
    var subTopicJson1 = '{"parentId":' + topicId + ',"content":"Force"';
    var subTopicJson2 = '{"parentId":' + topicId + ',"content":"Kinematics"';

    ajaxRequest("notesAdmin/addContent", subTopicJson1,function(response) {
        equal(response, true, "added Force as a subtopic to mechanics");
        start();
    });

    ajaxRequest("notesAdmin/addContent", subTopicJson2,function(response) {
        equal(response, true, "added kinematics as a subtopic to mechanics");
        start();
    });

    //now we getAugmentedNotes of the physics subject that we just put in
    var subjectIdJson = '{"id":' + subjectId +'}';
    ajaxRequest("notes/getAugmentedNotes", subjectIdJson,function(response) {
        ok(response, "getting notes from physics");
        start();
    });

    //parse the Augmented Notes to find what we want
    //TODO do this

});
