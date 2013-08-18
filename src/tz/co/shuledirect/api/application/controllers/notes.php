<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Notes extends REST_Controller
{
    function getAugmentedNotes_get()
    {
    	$this->load->model('notes');
    	$test = $this->notes->getAugmentedNotes();
        $array = array(
	    "foo" => $test,
 	    "bar" => "foo",
	 );
	 $this->response($array);	
    }

}