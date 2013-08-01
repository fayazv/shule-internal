<?php 

$app_info = file_get_contents('http://ec2-54-213-109-207.us-west-2.compute.amazonaws.com/api/index.php/notes/getAugmentedNotes');
var_dump($app_info);
echo "<bR>";
$yay = json_decode($app_info,true);  
var_dump($yay);    
echo "<bR>";
$yay = json_decode(json_decode($app_info,true),true);  
var_dump($yay);    

?>