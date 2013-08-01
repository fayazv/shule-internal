<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Notes extends REST_Controller
{
    function getAugmentedNotes_get()
    {
	this->response("sample naotes",200);
//	$this->output->set_content_type('application/json');
//	$this->output->set_output('{"a":1,"b":"hi"}');
    }

}