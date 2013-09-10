<h1> hello </h1>

<script>

//Testing getAugmentedNotes()
var xhr = new XMLHttpRequest();
    xhr.open("GET", "[EC2_MACHINE_URL]/api/index.php/notes/getAugmentedNotes");
    xhr.onreadystatechange = function () {
      if (this.readyState == 4) {
        alert('Status: '    + this.status + '<br>' +
              'Headers: ' + JSON.stringify(this.getAllResponseHeaders()) + '<br><br>' +
              'Body: '    + this.responseText);
      }
    };
    var data = "[{id: 123}]";
    xhr.send(data);



//setAugmentedNotes()
var xhr1 = new XMLHttpRequest();
    xhr1.open("POST", "[EC2_MACHINE_URL]/api/index.php/notes/setAugmentedNotes");
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
    xhr2.open("POST", "[EC2_MACHINE_URL]/api/index.php/notes/setAugmentedNotes");
    xhr2.onreadystatechange = function () {
      if (this.readyState == 4) {
        alert('Status: '    + this.status + '<br>' +
              'Headers: ' + JSON.stringify(this.getAllResponseHeaders()) + '<br><br>' +
              'Body: '    + this.responseText);
      }
    };
    var data2 = "[{id: 123, content: 'blah blah blah'}]";
    xhr2.send(data2);


</script>
