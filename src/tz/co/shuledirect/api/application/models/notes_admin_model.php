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

    private function deleteAllTags($parentId) {
        $this->db->query("DELETE FROM tags WHERE notes_id = $parentId;");
        return true;
    }

    private function deleteAllMedia($parent) {
        $this->db->query("DELETE FROM media WHERE notes_id = $parentId;");
        return true;
    }

    private function setAugmentedNotesHelper(&$notesChildren, &$depthToNoteTypeMapping, &$idsToRemove, $depth, $parentId, $languageId) {
        // iterate the children. insert each one incrementing the position each time. also recurse to their children
        $position = 0;
        foreach($notesChildren as &$child) {
            // check if there is an id, ie is this new or does it already exist?
            $nextParentId = 0;         
            if(!array_key_exists('content',$child)) {
                // TODO ldoshi -- throw an error. this is malformed input. 

                // TODO ldoshi if any insert or update statement fails below, error up and out to abort the operation
            }

            $content = $child['content'];
            $idExists = array_key_exists('id',$child);

            if($idExists) {
                // run an update statement
                $childId = $child['id'];
                $update = "UPDATE notes SET content='$content',position=$position,note_type_id=$depthToNoteTypeMapping[$depth],parent_notes_id=$parentId,language_id=$languageId WHERE id = $childId;\n";
                $this->db->query($update);

                // ensure that all childIds appear in the subtree for the
                // subject. The new content with ids provided may reshuffle
                // existing content within the same subject tree, but may not
                // include content from other subjects. We do this by ensuring
                // the id appears in idsToRemove
                if(array_key_exists($childId,$idsToRemove)) {
                    unset($idsToRemove[$childId]);
                } else {
                    throw new InvalidArgumentException("The id '$childId' was not found under this subject");
                }
                $nextParentId = $child['id'];
            } else {
                // run an insert statement
                $insert = "INSERT INTO notes(content,position,note_type_id,parent_notes_id,language_id) VALUES ('$content',$position,$depthToNoteTypeMapping[$depth],$parentId,$languageId);\n";
                $this->db->query($insert);
                
                // last insert id from the database as this new content is now a parent        
                $nextParentId = $this->db->insert_id();
            }
            
            // handle tags separately for clarity
            // 1. delete all existing tags (only happens if idExists)
            // 2. add in all the new tags 
            
            if($idExists) {
                $this->deleteAllTags($nextParentId);
            }
            if(array_key_exists('tags',$child))
            {
                foreach($child['tags'] as $tag) {
                    // skip tag if the content field is missing 
                    if(array_key_exists('content',$tag)) {
                        $this->addTag($nextParentId,$tag['content']);
                    }
                }
            }

            // handle media separately for clarity. similar to tags. 
            // 1. delete all existing media (only happens if idExists)
            // 2. add in all the new media
            if($idExists) {
                $this->deleteAllMedia($nextParentId);
            }
            if(array_key_exists('media',$child)) { 
                // media is further subdivided by type
                foreach($child['media'] as $mediaType=>$mediaTypeArray) {
                    foreach($mediaTypeArray as $mediaEntry) {
                        // skip if the content field is missing
                        if(array_key_exists('content',$mediaEntry)) {
                            $description = NULL;
                            // check if there is a description (optional)
                            if(array_key_exists('description',$mediaEntry)) {
                                $description = $mediaEntry['description'];
                            }
                            $this->addMedia($nextParentId,$mediaEntry['content'],$mediaType,$description);
                        }
                    }
                }
            }

            // make sure the children are also handled  
            if(array_key_exists('children',$child)) {
                $this->setAugmentedNotesHelper($child['children'],$depthToNoteTypeMapping,$idsToRemove,$depth+1,$nextParentId,$languageId);
            } 
            $position++;
        }
    }

    function setAugmentedNotes($notesArray)
    {
        // TODO ldoshi better error handling
        if(!array_key_exists('id',$notesArray)) {
            echo "no id provided at top level of content";
            return;
        }
        $subjectId = $notesArray['id'];

        $this->db->query("START TRANSACTION WITH CONSISTENT SNAPSHOT ");

        // confirm the top-level is a subject id
        $subject_found_query = $this->db->query("SELECT depth FROM notes JOIN note_types ON notes.note_type_id = note_types.id WHERE notes.id = $subjectId AND note_types.name = 'Subject';");
        $subject_found = $subject_found_query->num_rows();

        // TODO ldoshi better error handling    
        if($subject_found == 0 )
        {
            echo "error subject not found";
            return;
        }
        else if($subject_found > 1)
        {
            echo "error data integrity violation";
            return;
        }
        $subjectDepth = $subject_found_query->row()->depth;

        // build a map of depth to note_type id
        $result = $this->db->query("SELECT depth,id from note_types");
        $maxDepth = 0;
        $depthToNoteTypeMapping = array();
        
        foreach($result->result_array() as $depthRow) {
            $currentDepth= $depthRow['depth'];
            $currentId= $depthRow['id'];
            $depthToNoteTypeMapping[$currentDepth] = $currentId;
            if($currentDepth > $maxDepth){
                $maxDepth = $currentDepth;
            }
        }

        // build a list of ids that are there now so that we can update the ones still found here and delete the rest. 
        // just walk the map and do the updates/inserts. eg, need last_insert_id for cases when content is new.
        
        // build list of ids from the subject using the $subjectDepth and $maxDepth to know how many self joins are required.
        $idQuery = "SELECT DISTINCT notes.id FROM notes WHERE parent_notes_id = $subjectId OR id = $subjectId";
        for($i=0;$i< ($maxDepth - $subjectDepth) - 1;$i++)
        {
            $idQuery = "SELECT DISTINCT notes.id FROM notes JOIN ( $idQuery ) subquery$i on subquery$i.id = notes.parent_notes_id OR subquery$i.id = notes.id";
        }

        $idQuery .= ";";

        // build an array for the ids. any ids left in the array when we're
        // done will be deleted.
        $idsToRemove = array();
        $idQueryResultSet = $this->db->query($idQuery);
        foreach($idQueryResultSet->result_array() as $value){
            $idsToRemove[$value['id']] = 1;
        }
 
        // run updates for the top level subject
        // update the content for the subject if necessary
        if(array_key_exists('content',$notesArray)) {
            $this->editContent($subjectId,$notesArray['content']);        
        }
        // remove from the array of ids
        // we already checked the id under the same transaction so no need to
        // verify that the subjectId existed in idsToRemove
        unset($idsToRemove[$subjectId]);

        // if there are children, do this too. 
        if(array_key_exists('children',$notesArray)) {
            try {
                $this->setAugmentedNotesHelper($notesArray['children'],$depthToNoteTypeMapping, $idsToRemove, $subjectDepth+1, $subjectId, $this->englishLanguageId);
            } catch (InvalidArgumentException $e) {
                $this->db->query("ROLLBACK;");
                // TODO better error handling
                echo "Caught Exception: ".$e->getMessage()."\n";
                return false;
            }
        }

        // delete all ids that remain in idsToRemove (only if any are left)
        if(count($idsToRemove) > 0) {
            $deleteList = "";
            foreach($idsToRemove as $key=>$value) {
                $deleteList .= "$key,";
            }
            // chop final comma
            $deleteList = substr($deleteList, 0, -1);
            $deleteQuery = "DELETE FROM notes WHERE id IN ( $deleteList );";
            $this->db->query($deleteQuery);
        }
        $this->db->query("COMMIT;");

        // TODO ldoshi -- ADD IN REDUNDANCY CHECK TO ROLLBACK ON REPEATED data
        
        // TODO ldoshi -- figure out the right way to handle return values/return codes/errors
        return true;
    }


    function addContent($parentId,$newContent)
    {
        // TODO handle database errors
        // TODO should all the queries need to check if numRow() > 0?
        // error also if try add a child to a paragraph tier which does not support children. 
        $this->db->query("START TRANSACTION WITH CONSISTENT SNAPSHOT ");
        $noteTypeIdQuery = $this->db->query("select id from note_types AS note_types_one where note_types_one.depth = (select (note_types_two.depth+1) as new_depth from notes JOIN note_types AS note_types_two ON notes.note_type_id = note_types_two.id where notes.id = ".$this->db->escape($parentId).");");
        $noteTypeIdRow = $noteTypeIdQuery->row();
        $noteTypeId = $noteTypeIdRow->id; 

        $positionQuery = $this->db->query("select CASE WHEN max(position)+1 IS NOT NULL THEN (max(position)+1) ELSE 0 END as position from notes where parent_notes_id = ".$this->db->escape($parentId).";");
        $positionRow = $positionQuery->row();

        $position = $positionRow->position;
        $insert = $this->db->query("INSERT INTO notes (content,position,note_type_id,parent_notes_id,language_id) VALUES (".$this->db->escape($newContent).",$position,$noteTypeId,".$this->db->escape($parentId).",$this->englishLanguageId);");

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


    function addTag($parentId, $newContent)
    {
        // TODO error handling and safety restrictions?
        $this->db->query("INSERT INTO tags(notes_id,content) VALUES (".$this->db->escape($parentId).",".$this->db->escape($newContent).");");
        return true;
    }


    function deleteTag($id)
    {
        // TODO error handling and safety restrictions?
        $this->db->query("DELETE FROM tags WHERE id = ".$this->db->escape($id).";");
        return true;
    }


    function addMedia($parentId, $newContent, $type, $description)
    {
        // TODO error handling and safety restrictions?
        if($description == NULL) {
            $this->db->query("INSERT INTO media (notes_id,content,description,media_type_id) VALUES (".$this->db->escape($parentId).",".$this->db->escape($newContent).", NULL, (SELECT id FROM media_types WHERE type = ".$this->db->escape($type)."));");
        } else {
            $this->db->query("INSERT INTO media (notes_id,content,description,media_type_id) VALUES (".$this->db->escape($parentId).",".$this->db->escape($newContent).", ".$this->db->escape($description).", (SELECT id FROM media_types WHERE type = ".$this->db->escape($type)."));");
        }
    	return true;
    }


    function deleteMedia($id)
    {
        // TODO error handling and safety restrictions?
        $this->db->query("DELETE FROM media WHERE id = ".$this->db->escape($id).";");
    	return true;
    }
}

