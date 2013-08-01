<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Notes extends REST_Controller
{
    function getAugmentedNotes_get()
    {
         $array = array(
	     "foo" => "bar",
 	     "bar" => "foo",
	 );
	 $this->response($array);	
    }

}