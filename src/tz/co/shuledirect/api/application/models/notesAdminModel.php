<?php


class NotesAdminModel extends CI_Model {

    // TODO : should we build this into all the queries as a param?
    // do we want multi-language support for both notes content and quiz questions? 

    private $englistLanguageId = 1;


    function __construct()
    {
        parent::__construct();
    	$active_group = 'default';
    	$this->load->database();
    }

    function setAugmentedNotes($subjectId, $newContent)
    {
        //TODO: implement this method
		return $newContent;
    }


    function addContent($parentId,$newContent)
    {
    	return "$parentId . $newContent";
	 // TODO handle database errors
        // error also if try add a child to a paragraph tier which does not support children. 
        $this->db->query("START TRANSACTION WITH CONSISTENT SNAPSHOT ");
        $note_type_id_query = $this->db->query("select id from note_types AS note_types_one where note_types_one.depth = (select (note_types_two.depth+1) as new_depth from notes JOIN note_types AS note_types_two ON notes.note_type_id = note_types_two.id where notes.id = $parentId);");
        $note_type_id_result_set = $note_type_id_query->fetch();
        $note_type_id = $note_type_id_result_set['id'];
        $position_query = $this->db->query("select CASE WHEN max(position)+1 IS NOT NULL THEN (max(position)+1) ELSE 0 END as position from notes where parent_notes_id = $parentId;");
        $position_result_set = $position_query->fetch();
        $position = $position_result_set['position'];
        $insert = $this->db->query("INSERT INTO notes (content,position,note_type_id,parent_notes_id,language_id) VALUES ('$newContent',$position,$note_type_id,$parentId,$this->englishLanguageId);");
        $this->db->query("COMMIT;");
        return true;

    }
   

    function editContent($id,$editedContent)
    {
        // TODO error handling
        $this->db->query("UPDATE notes SET content='$editedContent' WHERE id = $id;");
        return true;
    }


    function deleteContent($id)
    {
        // TODO error handling and safety restrictions?
        $this->db->query("DELETE FROM notes WHERE id = $id;");
        return true;

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

