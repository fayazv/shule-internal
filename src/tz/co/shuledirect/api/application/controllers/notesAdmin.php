<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

// So the controller will always recieve a string that it will parse and send contents to the model. 
// The model will then take these values and make appropriate db calls. 

class NotesAdmin extends REST_Controller
{
    public function __construct()
    {
        parent::__construct(); 
        $this->load->model('Notes_admin_model');
    }


    function setAugmentedNotes_post()
    {
    	$object = json_decode($this->input->post("inputJson"), true);
        if (array_key_exists("id", $object) && array_key_exists("content", $object))
        {
            $subjectId = $object["id"];
            $newContent = $object["content"];
            $success = $this->Notes_admin_model->setAugmentedNotes($subjectId, $newContent); 
            $this->response($success);// change this once we figure out how to handle errors
        }
        else
        {
            //TODO Do something with errors
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
        if (array_key_exists("parentId", $object) && array_key_exists("content", $object))
        {
            $parentId = $object["parentId"];
            $newContent = $object["content"];
            $success = $this->Notes_admin_model->addContent($parentId, $newContent);
            $this->response($success);
        } else {
            // TODO ldoshi error condition
        }

        
    }

    function editContent_post()
    {
        $object = json_decode($this->post("inputJson"), true);
        if (array_key_exists("id", $object) && array_key_exists("content", $object))
        {
            $id = $object["id"];
            $editedContent = $object["content"];
            $success = $this->Notes_admin_model->editContent($id, $editedContent);
            $this->response($success);
        } else {
            //TODO handle the error
        }
        
    }

    function deleteContent_post()
    { 
        $object = json_decode($this->post("inputJson"), true);
        if (array_key_exists("id", $object))
        {
            $id = $object["id"];
            $success = $this->Notes_admin_model->deleteContent($id);
            $this->response($success);
        } else {
            //TODO Handle the error
        }
        
    }

    function addTag_post()
    {
        $object = json_decode($this->post("inputJson"), true);
        if (array_key_exists("parentId", $object) && array_key_exists("content", $object))
        {
            $parentId = $object["parentId"];
            $newContent = $object["content"];
            $success = $this->Notes_admin_model->addTag($parentId, $newContent);
            $this->response($success);
        } else {
            // TODO handle error
        }

    }

    function deleteTag_post()
    {
        $object = json_decode($this->post("inputJson"), true);
        if (array_key_exists("id", $object))
        {
            $id = $object["id"];
            $success = $this->Notes_admin_model->deleteTag($id);
            $this->response($success);
        } else {
            // TODO handle error
        }        
    }

    function addMedia_post()
    {
        $object = json_decode($this->post("inputJson"), true);
        if (array_key_exists("parentId", $object) && array_key_exists("content", $object) && array_key_exists("type", $object)) {
            $parentId = $object["parentId"];
            $newContent = $object["content"];
            $type = $object["type"];
            if(array_key_exists("description", $object)) {
                $description = $object["description"];
            } else {
                $description = NULL;
            }
           
            $success = $this->Notes_admin_model->addMedia($parentId, $newContent, $type, $description);
            $this->response($success);
        } else {
            // TODO handle error 
        }
    }

    function deleteMedia_post()
    {
        $object = json_decode($this->post("inputJson"), true);
        if (array_key_exists("id", $object))
        {
            $id = $object["id"];
            $success = $this->Notes_admin_model->deleteMedia($id);
            $this->response($success);
        } else {
            // TODO handle errors
        }
    }

}