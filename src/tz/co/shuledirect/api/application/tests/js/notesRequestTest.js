/**
This file will be used to test web api calls. 
We simply write as many unit tests we want and 
use the qunit.html file to see our results.

Note: These are only to test whether an API call 
from an external source receives a proper response.
The actual testing of validation of the data and 
interactions with the db are NOT tested in this file. 

*/

function ajaxRequest(methodURL, inputJson) {
    var eurl = "/api/index.php/" + methodURL;
    var params = 'inputJson='+ inputJson;   
    
    var post = $.ajax({
    	type: "POST",
    	url: eurl,
    	data: params,
        dataType: 'json',
        async: false
    });
    return $.parseJSON($.parseJSON(post.responseText)); //dunno why we have to parse this twice
}

//given the entire augmented notes and a specific id, this method returns the 
//content associated with that particular id
function getContentFromId(contentTree,id) {
    // ensure there is an id element to have a chance
    if(contentTree.hasOwnProperty('id')) {
        // check if we already found the id. if so, return it's contents
        if(contentTree['id'] == id) {
            return contentTree['content'];
        } else {
            // otherwise recurse on every child (if any exist). otherwise we're done. 
            if(contentTree.hasOwnProperty('children')) {
                for(i=0; i<contentTree['children'].length;i++) {
                    var content = getContentFromId(contentTree['children'][i], id);
                    if(content != null){
                        // we're done
                        return content;
                    }
                }           
            } else {
                return null;
            }
        }
    } else {
        // no id --> return NULL
        return null;
    }
}


/**
Test 1

We run the db scripts
Call getId of Form I
addContent()
getId of the content just added
getAugmented notes of the id
(make sure this is the same as the content we put in!)


*/
test('addContent, getId and getAugmentedNotes test1', function() {
    //getId of form I = 2
    output = ajaxRequest("notes/getId",'{"form": "Form 1"}');
    equal(output["id"],2, "The id of form 1 should be 2");
    var formId = output["id"];

    //now we add some subjects
    var subjectJson1 = '{"parentId":' + formId + ',"content":"Physics"}';
    var subjectJson2 = '{"parentId":' + formId + ',"content":"Chemistry"}';
    var subjectJson3 = '{"parentId":' + formId + ',"content":"History"}';
    output = ajaxRequest("notesAdmin/addContent", subjectJson1);
    equal(output, true, "added physics as a subject");

    output = ajaxRequest("notesAdmin/addContent", subjectJson2);
    equal(output, true, "added chemistry as a subject");

    output = ajaxRequest("notesAdmin/addContent", subjectJson3);
    equal(output, true, "added history as a subject");


    //we call getId of physics that we just added
    output = ajaxRequest("notes/getId",'{"form": "Form 1", "subject": "Physics"}');
    ok(output["id"],"The request went through");
    var subjectId = output["id"];

    //add some topics to physics
    var topicJson1 = '{"parentId":' + subjectId + ',"content":"Mechanics"}';
    var topicJson2 = '{"parentId":' + subjectId + ',"content":"Electricity"}';

    output = ajaxRequest("notesAdmin/addContent", topicJson1);
    equal(output, true, "added mechanics as a topic");

    output = ajaxRequest("notesAdmin/addContent", topicJson2);
    equal(output, true, "added electricity as a topic");


    //we call getId of Mechanics that we just added
    output = ajaxRequest("notes/getId",'{"form": "Form 1", "subject": "Physics", "topic":"Mechanics"}');
    ok(output["id"],"The request went through");
    var topicId = output["id"];
    //alert(topicId);

    //add some subTopics to physics
    var subTopicJson1 = '{"parentId":' + topicId + ',"content":"Force"}';
    var subTopicJson2 = '{"parentId":' + topicId + ',"content":"Kinematics"}';


    output = ajaxRequest("notesAdmin/addContent", subTopicJson1);
    equal(output, true, "added mechanics as a topic");

    output = ajaxRequest("notesAdmin/addContent", subTopicJson2);
    equal(output, true, "added electricity as a topic");

    output = ajaxRequest("notes/getId",'{"form": "Form 1", "subject": "Physics", "topic":"Mechanics", "subtopic":"Force"}');
    ok(output["id"],"The request went through");
    var subtopicId = output["id"];

    //now we getAugmentedNotes of the physics subject that we just put in
    var subjectIdJson = '{"id":' + subjectId +'}';
    output = ajaxRequest("notes/getAugmentedNotes", subjectIdJson);
    ok(output,"This returns some notes");
    var augmentedNotes = output;

    // //parse the Augmented Notes to find what we want
    // //TODO do this
    content = getContentFromId(augmentedNotes, subjectId);
    equal(content, "Physics", "The subjectid and content match correctly!");

    content = getContentFromId(augmentedNotes, topicId);
    equal(content, "Mechanics", "The topicid and content match correctly!");

    content = getContentFromId(augmentedNotes, subtopicId);
    equal(content, "Force", "The subtopicid and content match correctly!");
});



test("adding tags and media", function() {

    output = ajaxRequest("notes/getId",'{"form": "Form 1", "subject": "Physics"}');
    ok(output["id"],"The request went through");
    var subjectId = output["id"];

    output = ajaxRequest("notes/getId",'{"form": "Form 1", "subject": "Physics", "topic":"Mechanics"}');
    ok(output["id"],"The request went through");
    var topicId = output["id"];

    output = ajaxRequest("notes/getId",'{"form": "Form 1", "subject": "Physics", "topic":"Mechanics", "subtopic":"Force"}');
    ok(output["id"],"The request went through");
    var subtopicId = output["id"];

    var subjectTag1 = '{"parentId":' + subjectId + ',"content":"motion"}';
    var subjectTag2 = '{"parentId":' + subjectId + ',"content":"proton"}';


    var topicTag1 = '{"parentId":' + topicId + ',"content":"newton"}';
    var topicTag2 = '{"parentId":' + topicId + ',"content":"velocity"}';


    output = ajaxRequest("notesAdmin/addTag", subjectTag1);
    equal(output, true, "added motion as a tag");

    output = ajaxRequest("notesAdmin/addTag", subjectTag2);
    equal(output, true, "added proton as a tag");

    output = ajaxRequest("notesAdmin/addTag", topicTag1);
    equal(output, true, "added newton as a tag");

    output = ajaxRequest("notesAdmin/addTag", topicTag2);
    equal(output, true, "added velocity as a tag");

});
