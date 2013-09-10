<h1> hello </h1>

<script>
var http = new XMLHttpRequest();
var url = "http://ec2-54-200-19-24.us-west-2.compute.amazonaws.com/api/index.php/notes/getAugmentedNotes";
var params = 'inputJson={"id":123}';
http.open("POST", url, true);

//Send the proper header information along with the request
http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
http.setRequestHeader("Content-length", params.length);
http.setRequestHeader("Connection", "close");

http.onreadystatechange = function() {//Call a function when the state changes.
			if(http.readyState == 4 && http.status == 200) {
					   alert(this.responseText);
					   }
}
http.send(params);


/**
//Testing getAugmentedNotes()
var xhr = new XMLHttpRequest();
    xhr.open("GET", "http://ec2-54-200-19-24.us-west-2.compute.amazonaws.com/api/index.php/notes/getAugmentedNotes");
    xhr.onreadystatechange = function () {
      if (this.readyState == 4) {
        alert('Status: '    + this.status + '<br>' +
              'Headers: ' + JSON.stringify(this.getAllResponseHeaders()) + '<br><br>' +
              'Body: '    + this.responseText);
      }
    };
    var data = 'inputJson=[{"id": 123}]';
    xhr.send(data);



//setAugmentedNotes()
var xhr1 = new XMLHttpRequest();
    xhr1.open("POST", "ec2-54-200-19-24.us-west-2.compute.amazonaws.com/api/index.php/notes/setAugmentedNotes");
    xhr1.onreadystatechange = function () {
      if (this.readyState == 4) {
        alert('Status: '    + this.status + '<br>' +
              'Headers: ' + JSON.stringify(this.getAllResponseHeaders()) + '<br><br>' +
              'Body: '    + this.responseText);
      }
    };
    var data1 = "[{id: 123, content: 'blah blah blah'}]";
    xhr1.send(data1);

//addContent()
var xhr2 = new XMLHttpRequest();
    xhr2.open("POST", "ec2-54-200-19-24.us-west-2.compute.amazonaws.com/api/index.php/notes/setAugmentedNotes");
    xhr2.onreadystatechange = function () {
      if (this.readyState == 4) {
        alert('Status: '    + this.status + '<br>' +
              'Headers: ' + JSON.stringify(this.getAllResponseHeaders()) + '<br><br>' +
              'Body: '    + this.responseText);
      }
    };
    var data2 = "[{id: 123, content: 'blah blah blah'}]";
    xhr2.send(data2);
**/

</script>
