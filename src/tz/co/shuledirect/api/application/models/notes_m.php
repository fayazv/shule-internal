<?php

class Notes_m extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    function getAugmentedNotes($subjectId)
    {
    	return "TODO: implement this method";
    }
    

    function setAugmentedNotes($subjectId, $newContent)
    {
		return "TODO: implement this method";
    }


    function addContent($parentId,$newContent)
    {
    	return "TODO: implement this method";
    }
   

    function editContent($id,$editedContent)
    {
    	return "TODO: implement this method";
    }


    function deleteContent($id)
    {
    	return "TODO: implement this method";
    }


    function addTag($parentId, $newTag)
    {
    	return "TODO: implement this method";
    }


    function deleteTag($parentId, $tagId)
    {
    	return "TODO: implement this method";
    }


    function addMedia($parentId, $newContent, $type, $description, $isPrintable)
    {
    	return "TODO: implement this method";
    }


    function deleteMedia($parentId, $mediaId)
    {
    	return "TODO: implement this method";
    }
}

