<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Notes extends REST_Controller
{
    function getAugmentedNotes_get($subjectId)
    {
    	$this->load->model('notes');
    	$test = $this->notes->getAugmentedNotes();
        $array = array(
	    "foo" => $test,
 	    "bar" => "foo",
	 );
	 $this->response($array);	
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