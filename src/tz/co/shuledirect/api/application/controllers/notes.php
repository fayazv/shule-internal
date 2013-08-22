<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Notes extends REST_Controller
{
    public function __construct()
    {
        parent::__construct(); 
        $this->load->model('Notes_m');
    }

    function getAugmentedNotes_get()
    {
    	$test = $this->Notes_m->getAugmentedNotes();
        $data["foo"] = $test;
        $data["bar"] = "lalala";
	$this->response($data);	
    }

    function setAugmentedNotes($subjectId, $newContent)
    {
    	
    }

    function addContent($parentId, $newContent)
    {

    }

    function editContent($id, $editedContent)
    {

    }

    function deleteContent($id)
    {

    }

    function addTag($parentId, $newTag)
    {

    }

    function deleteTag($parentId, $tagId)
    {

    }

    function addMedia($parentId, $newContent, $type, $description, $isPrintable)
    {

    }

    function deleteMedia($parentId, $mediaId)
    {

    }
}