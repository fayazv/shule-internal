<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

// So the controller will always recieve a string that it will parse and send contents to the model. 
// The model will then take these values and make appropriate db calls. 

class NotesAdmin extends REST_Controller
{
    public function __construct()
    {
        parent::__construct(); 
        $this->load->model('NotesAdminModel');
    }


    function setAugmentedNotes_post()
    {
    	$object = json_decode($this->input->post("inputJson"), true);
        if (array_key_exists("id", $object) && array_key_exists("content", $object))
        {
            $subjectId = $object["id"];
            $newContent = $object["content"];
            $success = $this->NotesAdminModel->setAugmentedNotes($subjectId, $newContent); 
            $this->response($success);// change this once we figure out how to handle errors
        }
        else
        {
            //Do something with errors
        }
    }


    /**
     * Add the new content under the id provided. 
     *
     * Expected ids: project, form, subject, topic, subtopic, concept 
     * Unrecognized: no-op
     */
    function addContent_post()
    {
        $object = json_decode($this->post("inputJson"), true);
        if (array_key_exists("id", $object) && array_key_exists("content", $object))
        {
            $parentId = $object["id"];
            $newContent = $object["content"];
            $success = $this->NotesAdminModel->addContent($parentId, $newContent);
            $this->response($success);
        } else {
            // TODO ldoshi error condition
        }

        
    }

    function editContent()
    {
        $object = json_decode($inputJson, true);
        if (array_key_exists("id", $object) && array_key_exists("content", $object))
        {
            $id = $object["id"];
            $editedContent = $object["content"];
            $success = $this->NotesAdminModel->editContent($id, $editedContent);
            $this->response($success);
        } else {
            //handle the error
        }
        
    }

    function deleteContent($inputJson)
    {
        $object = json_decode($inputJson, true);
        if (array_key_exists("id", $object))
        {
            $id = $object["id"];
            $success = $this->NotesAdminModel->deleteContent($id);
            $this->response($success);
        } else {
            //Handle the error
        }
        

         
    }

    function addTag($inputJson)
    {
        $object = json_decode($inputJson, true);
        if (array_key_exists("id", $object) && array_key_exists("tag", $object))
        {
            $id = $object["id"];
            $newTag = $object["tag"];
            $success = $this->NotesAdminModel->addTag($id, $newTag);
            $this->response($success);
        } else {
            //Go nuts mate
        }

    }

    function deleteTag($inputJson)
    {
        $object = json_decode($inputJson, true);
        if (array_key_exists("parentId", $object) && array_key_exists("tagId", $object))
        {
            $parentId = $object["parentId"];
            $tagId = $object["tagId"];
            $success = $this->NotesAdminModel->deleteTag($parentId, $tagId);
            $this->response($success);
        }
        
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
            $success = $this->NotesAdminModel->addMedia($parentId, $newContent, $type, $description);
            $this->response($success);
    }
        }
    }

    function deleteMedia($inputJson)
    {
        $object = json_decode($inputJson, true);
        if (array_key_exists("parentId", $object) && array_key_exists("mediaId", $object))
        {
            $parentId = $object["parentId"];
            $mediaId = $object["mediaId"];
            $this->response($success);
            $success = $this->NotesAdminModel->deleteMedia($parentId, $mediaId);
        } else {
            //you know what to do
        }
    }

}