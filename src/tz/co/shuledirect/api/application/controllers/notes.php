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
            echo("Integer is not valid"); //TODO: change this so it errors out
        }
        else
        {
            echo("Integer is valid");
        }
    }

    function getAugmentedNotes_post()
    {
    	
	$object = json_decode($this->input->post("inputJson"), true);
	
        if (array_key_exists("id", $object))
        {
            $subjectId = $object["id"];
	    
        } else {
            $this->response("You suck at life");
        }
        //validateInt($subjectId);
        $augmentedNotesObject = $this->Notes_m->getAugmentedNotes($subjectId);
	    $this->response($augmentedNotesObject);	
    }

    function setAugmentedNotes_post()
    {
    	$object = json_decode($this->input->post("inputJson"), true);
        if (array_key_exists("id", $object) && array_key_exists("content", $object))
        {
            $subjectId = $object["id"];
            $newContent = $object["content"];
        }

        //validateInt($subjectId);
        $success = $this->Notes_m->setAugmentedNotes($subjectId, $newContent); 
        $this->response($success);
    }

    function addContent($inputJson)
    {
        $object = json_decode($inputJson, true);
        if (array_key_exists("id", $object) && array_key_exists("content", $object))
        {
            $parentId = $object["id"];
            $newContent = $object["content"];
        }

        validateInt($parentId);
        $success = $this->Notes_m->addContent($parentId, $newContent);
        //success is a boolean
        $this->response($success);
    }

    function editContent($inputJson)
    {
        $object = json_decode($inputJson, true);
        if (array_key_exists("id", $object) && array_key_exists("content", $object))
        {
            $id = $object["id"];
            $editedContent = $object["content"];
        }

        validateInt($id);
        $success = $this->Notes_m->editContent($id, $editedContent);
        //success is a boolean
        $this->response($success);
    }

    function deleteContent($inputJson)
    {
        $object = json_decode($inputJson, true);
        if (array_key_exists("id", $object))
        {
            $id = $object["id"];
        }
        validateInt($id);

        $success = $this->Notes_m->deleteContent($id);
        $this->response($success); 
    }

    function addTag($inputJson)
    {
        $object = json_decode($inputJson, true);
        if (array_key_exists("id", $object) && array_key_exists("tag", $object))
        {
            $id = $object["id"];
            $newTag = $object["tag"];
        }

        validateInt($id);
        $success = $this->Notes_m->addTag($id, $newTag);
        //success is a boolean
        $this->response($success);
    }

    function deleteTag($inputJson)
    {
        $object = json_decode($inputJson, true);
        if (array_key_exists("parentId", $object) && array_key_exists("tagId", $object))
        {
            $parentId = $object["parentId"];
            $tagId = $object["tagId"];
        }

        validateInt($parentId);
        validateInt($tagId);
        $success = $this->Notes_m->deleteTag($parentId, $tagId);
        //success is a boolean
        $this->response($success);
    }

    function addMedia($inputJson)
    {
        $object = json_decode($inputJson, true);
        if (array_key_exists("id", $object) && array_key_exists("content", $object) && array_key_exists("type", $object) && array_key_exists("description", $object))
        {
            $parentId = $object["id"];
            $newContent = $object["content"];
            $type = $object["type"];
            $description = $object["description"];
        }

        validateInt($parentId);
        $success = $this->Notes_m->addMedia($parentId, $newContent, $type, $description);
        //success is a boolean
        $this->response($success);
    }

    function deleteMedia($inputJson)
    {
        $object = json_decode($inputJson, true);
        if (array_key_exists("parentId", $object) && array_key_exists("mediaId", $object))
        {
            $parentId = $object["parentId"];
            $mediaId = $object["mediaId"];
        }

        validateInt($parentId);
        validateInt($mediaId);
        $success = $this->Notes_m->deleteMedia($parentId, $mediaId);
        //success is a boolean
        $this->response($success);
    }
}