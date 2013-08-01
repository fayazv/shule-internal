<?php 

$app_info = file_get_contents('http://ec2-54-213-104-138.us-west-2.compute.amazonaws.com/index.php/notes/getAugmentedNotes');
var_dump($app_info);
$yay = json_decode($app_info,true);  
var_dump($yay);    

?>