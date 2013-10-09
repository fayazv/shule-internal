<?php


class Notes_admin_model extends CI_Model {

    // TODO : should we build this into all the queries as a param?
    // do we want multi-language support for both notes content and quiz questions? 

    private $englishLanguageId = 1;


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
        // TODO handle database errors
        // TODO should all the queries need to check if numRow() > 0?
        // error also if try add a child to a paragraph tier which does not support children. 
        $this->db->query("START TRANSACTION WITH CONSISTENT SNAPSHOT ");
        $noteTypeIdQuery = $this->db->query("select id from note_types AS note_types_one where note_types_one.depth = (select (note_types_two.depth+1) as new_depth from notes JOIN note_types AS note_types_two ON notes.note_type_id = note_types_two.id where notes.id = $parentId);");
        $noteTypeIdRow = $noteTypeIdQuery->row();
        $noteTypeId = $noteTypeIdRow->id; 

        $positionQuery = $this->db->query("select CASE WHEN max(position)+1 IS NOT NULL THEN (max(position)+1) ELSE 0 END as position from notes where parent_notes_id = $parentId;");
        $positionRow = $positionQuery->row();

        $position = $positionRow->position;
        $insert = $this->db->query("INSERT INTO notes (content,position,note_type_id,parent_notes_id,language_id) VALUES (".$this->db->escape($newContent).",$position,$noteTypeId,$parentId,$this->englishLanguageId);");
        $this->db->query("COMMIT;");
        return true;
    }
   

    function editContent($id,$editedContent)
    {
        // TODO error handling
        $this->db->query("UPDATE notes SET content=".$this->db->escape($editedContent)." WHERE id = $id;");
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

