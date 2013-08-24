<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

// So the controller will always recieve a string that it will parse and send contents to the model. 
// The model will then take these values and make appropriate db calls. 

class Notes extends REST_Controller
{
    public function __construct()
    {
        parent::__construct(); 
        $this->load->model('Notes_m');
    }

    function validateInt($int)
    {
        if(!filter_var($int, FILTER_VALIDATE_INT))
        {
            echo("Integer is not valid");
        }
        else
        {
            echo("Integer is valid");
        }
    }

    function getAugmentedNotes_get($inputJson)
    {
    	$object = json_decode($inputJson, true);
        if (array_key_exists("id", $object))
        {
            $subjectId = $object["id"];
        }
        validateInt($subjectId);

        $augmentedNotesObject = $this->Notes_m->getAugmentedNotes($subjectId);
	    $this->response($augmentedNotesObject);	
    }

    function setAugmentedNotes($inputJson)
    {
    	$object = json_decode($inputJson, true);
        if (array_key_exists("id", $object) && array_key_exists("content", $object))
        {
            $subjectId = $object["id"];
            $newContent = $object["content"];
        }

        validateInt($subjectId);
        $success = $this->Notes_m->setAugmentedNotes($subjectId, $newContent); 
        //success is a boolean
        $this->response($success);
    }

    function addContent($inputJson)
    {
        $object = json_decode($inputJson, true);
    }

    function editContent($inputJson)
    {
        $object = json_decode($inputJson, true);
    }

    function deleteContent($inputJson)
    {
        $object = json_decode($inputJson, true);
    }

    function addTag($inputJson)
    {
        $object = json_decode($inputJson, true);
    }

    function deleteTag($inputJson)
    {
        $object = json_decode($inputJson, true);
    }

    function addMedia($inputJson)
    {
        $object = json_decode($inputJson, true);
    }

    function deleteMedia($inputJson)
    {
        $object = json_decode($inputJson, true);
    }
}