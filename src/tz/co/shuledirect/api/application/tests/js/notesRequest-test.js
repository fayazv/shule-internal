/**
This file will be used to test web api calls. 
We simply write as many unit tests we want and 
use the qunit.html file to see our results.

Note: These are only to test whether an API call 
from an external source receives a proper response.
The actual testing of validation of the data and 
interactions with the db are NOT tested in this file. 

*/


test("Testing getAumentedNotes", function() {
	ok(makeRequest("notes/getAugmentedNotes", '{"id":123}'), "Response is good i suppose");

})