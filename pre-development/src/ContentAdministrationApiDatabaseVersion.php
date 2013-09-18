<?php

/**
 * The purpose of this implementation of the API is to ensure all the database
 * interactions are correct. This version does not make the actual web api
 * calls, but simulates them to skip to how the model would function. 
 *
 * Specifically, this API is for adding and editing notes/syllabus content,
 * which involves displaying the content on the editing pages as well.
 *
 * Usage: 
 *     $sdk = new ContentAdministrationSDKDatabaseVersion(<dataDir>,<subjectId>);
 *       -- <dataDir> is a path that is writeable by this class. This class
 *          emulates the API interaction by backing up the data in a
 *          flatfile. The files are placed in the dataDir and named after the
 *          subjectId
 *       -- <subjectId> is the id for the subject whose notes and syllabus are
 *          being operated on. This is a crutch for this sdk implementation
 *          and will not be necessary for the real one since all notes ids
 *          will be globally unique. A specific instance of
 *          ContentAdministrationSDKImpl can only operate on a single
 *          subjectId
 */

interface ContentAdministrationSDK
{
    
    /**
     * Returns of a JSON object of all the notes of the section level 
     * uniquely described by the id. The syntax is described in the 
     * JSON Format page. This method is intended to simplify retrieving and  
     * showing notes so the accepted id types are limited to those that would 
     * produce reasonable displayable chunks. 
     *
     * Media and tags are included. 
     *
     * Expected ids: subtopic, concept 
     * TODO ldoshi: this will need to accept subject in admin mode only. 
     * Unrecognized: return an empty object
     */
    public function getAugmentedNotes($subjectId);
    
    /**
     * Provide a new syllabus and notes content in JSON format, including media and tags. 
     * This is meant to go with a rich user-interface for editing and generating content.
     *
     * Expected ids: subjectId
     * Unrecognized: no-op
     */
    public function setAugmentedNotes($subjectId, $newContent);

    /**
     * Add the new content under the id provided. 
     *
     * Expected ids: project, form, subject, topic, subtopic, concept 
     * Unrecognized: no-op
     */
    public function addContent($parentId,$newContent);
   
    /**
     * Sets the value of the provided id to be editedContent
     * 
     * Expected ids: form, subject, topic, subtopic, concept, paragraph
     * Unrecognized: no-op 
     */
    public function editContent($id,$editedContent);

    /**
     * Removes the value at the provided id and cascades to *all* children. Use 
     * with care. Eg if a subtopic is remove, so are all of its concepts, their 
     * paragraphs, all tags, media etc under that subtopic .
     * 
     * Expected ids: form, subject, topic, subtopic, concept, paragraph
     * Unrecognized: no-op 
     */
    public function deleteContent($id);

    /**
     * Add a new tag 
     * 
     * Expected parentIds: topic, subtopic, concept
     * Unrecognized: no-op
     */
    public function addTag($parentId, $newTag);

    /**
     * Delete an existing tag
     * 
     * Expected parentIds: topic, subtopic, concept
     * Unrecognized: no-op
     */
    public function deleteTag($parentId, $tagId);

    /**
     * Add new media under the parentId. 
     * 
     * Expected parentId: subtopic, concept, paragraph
     * Unrecognized: no-op
     */
    public function addMedia($parentId, $newContent, $type, $description);

    /**
     * Sets all the media of the given types, as links (strings)
     * The key is the type, the value is the link string
     */
    public function deleteMedia($parentId, $mediaId);

}


class ContentAdministrationSDKDatabaseVersion implements ContentAdministrationSDK
{
    private $baseDirectory;
    private $subjectId;

    private $dsn = 'mysql:dbname=shuledirect;host=127.0.0.1';
    private $user = 'root';
    private $englishLanguageId = 1;

    private function getConnection() {
        try {
            $db = new PDO($this->dsn, $this->user);
            return $db;
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }

    }

    public function getAugmentedNotes($subjectId) {
        // verify subjectId



    }

    private function setAugmentedNotesHelper(&$db,&$notesChildren, &$depthToNoteTypeMapping, &$idArray, $depth, $parentId, $languageId) {
        // iterate the children. insert each one incrementing the position each time. also recurse to their children
        $position = 0;
        foreach($notesChildren as &$child) {
            // check if there is an id, ie is this new or does it already exist?
            $nextParentId = 0;         
            if(!array_key_exists('content',$child)) {
                // TODO ldoshi -- throw an error. this is malformed input. 

                // TODO ldoshi if any insert fails, error up and out to abort the operation
            }

            // TODO ldoshi -- SUPPORT FOR Tags and Media!!
            $content = $child['content'];
            
            $idExists = array_key_exists('id',$child);
            if($idExists) {
                // run an update statement
                $childId = $child['id'];
                $update = "UPDATE notes SET content='$content',position=$position,note_type_id=$depthToNoteTypeMapping[$depth],parent_notes_id=$parentId,language_id=$languageId WHERE id = $childId;\n";
                $db->query($update);
                unset($idArray[$childId]);
                $nextParentId = $child['id'];
            } else {
                // run an insert statement
                $insert = "INSERT INTO notes(content,position,note_type_id,parent_notes_id,language_id) VALUES ('$content',$position,$depthToNoteTypeMapping[$depth],$parentId,$languageId);\n";
                $db->query($insert);
                
                $lastInsertResultSet = $db->query("select last_insert_id() as last_insert_id")->fetch();
                // last insert id from the database as this new content is now a parent        
                $nextParentId = $lastInsertResultSet['last_insert_id'];
            }
            
            // handle tags separately for clarity
            // 1. delete all existing tags (only happens if idExists)
            // 2. add in all the new tags 
            
            if($idExists) {
                $this->deleteAllTags($nextParentId, $db);
            }
            if(array_key_exists('tags',$child))
            {
                foreach($child['tags'] as $tag) {
                    // skip tag if the content field is missing 
                    if(array_key_exists('content',$tag)) {
                        $this->addTagInternal($nextParentId,$tag['content'],$db);
                    }
                }
            }

            // handle media separately for clarity. similar to tags. 
            // 1. delete all existing media (only happens if idExists)
            // 2. add in all the new media
            if($idExists) {
                $this->deleteAllMedia($nextParentId,$db);
            }
            if(array_key_exists('media',$child)) { 
                // media is further subdivided by type
                foreach($child['media'] as $mediaType=>$mediaTypeArray) {
                    foreach($mediaTypeArray as $mediaEntry) {
                        // skip if the content field is missing
                        if(array_key_exists('content',$mediaEntry)) {
                            $description = null;
                            // check if there is a description (optional)
                            if(array_key_exists('description',$mediaEntry)) {
                                $description = $mediaEntry['description'];
                            }
                            $this->addMediaInternal($nextParentId,$mediaEntry['content'],$mediaType,$description,$db);
                        }
                    }
                }
            }

            // make sure the children are also handled  
            if(array_key_exists('children',$child)) {
                $this->setAugmentedNotesHelper($db, $child['children'],$depthToNoteTypeMapping,$idArray,$depth+1,$nextParentId,$languageId);
            } 
            $position++;
        }
    }

    public function setAugmentedNotes($subjectId, $newContent) {
        $data = json_decode($newContent);

        $db = $this->getConnection();
        $db->query("START TRANSACTION WITH CONSISTENT SNAPSHOT ");
        
        // confirm the top-level is a subject id
        $subject_found_query = $db->query("SELECT depth FROM notes JOIN note_types ON notes.note_type_id = note_types.id WHERE notes.id = $subjectId AND note_types.name = 'Subject';");
        $subject_found = $subject_found_query->rowCount();
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
        $subjectDepthResultSet = $subject_found_query->fetch();
        $subjectDepth = $subjectDepthResultSet['depth'];
        
        // build a map of depth to note_type id
        $result = $db->query("SELECT depth,id from note_types");
        $maxDepth = 0;
        $depthToNoteTypeMapping = array();
        foreach($result as $value) {
            $currentDepth= $value['depth'];
            $currentId= $value['id'];
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
        
        // build an array for the ids
        $idArray = array();
        $idQueryResultSet = $db->query($idQuery);
        foreach($idQueryResultSet as $value){
            $idArray[$value['id']] = 1;
        }
        
        $notesArray = json_decode($newContent, true);
       
        // run updates for the top level subject
        unset($idArray[$subjectId]);

        // TODO ldoshi -- update the subject level 

        // if there are children, do this too. 
        if(array_key_exists('children',$notesArray)) {
            $this->setAugmentedNotesHelper($db,$notesArray['children'],$depthToNoteTypeMapping, $idArray, $subjectDepth+1, $subjectId, $this->englishLanguageId);
        }

        // delete all ids that remain in idArray
        $deleteList = "";
        foreach($idArray as $key=>$value) {
            $deleteList .= "$key,";
        }
        // chop final comma
        $deleteList = substr($deleteList, 0, -1);
        $deleteQuery = "DELETE FROM notes WHERE id IN ( $deleteList );";
        $db->query($deleteQuery);
        
        $db->query("COMMIT;");
        return true;
    }

    // returns true if the id was found and the newContent was added as a child
    public function addContent($parentId,$newContent) {
        $db = $this->getConnection();
        $db->query("START TRANSACTION WITH CONSISTENT SNAPSHOT ");
        $note_type_id_query = $db->query("select id from note_types AS note_types_one where note_types_one.depth = (select (note_types_two.depth+1) as new_depth from notes JOIN note_types AS note_types_two ON notes.note_type_id = note_types_two.id where notes.id = $parentId);");
        $note_type_id_result_set = $note_type_id_query->fetch();
        $note_type_id = $note_type_id_result_set['id'];
        $position_query = $db->query("select CASE WHEN max(position)+1 IS NOT NULL THEN (max(position)+1) ELSE 0 END as position from notes where parent_notes_id = $parentId;");
        $position_result_set = $position_query->fetch();
        $position = $position_result_set['position'];
        $insert = $db->query("INSERT INTO notes (content,position,note_type_id,parent_notes_id,language_id) VALUES ('$newContent',$position,$note_type_id,$parentId,$this->englishLanguageId);");

        $db->query("COMMIT;");
        return true;
    }

    // returns true if the id was found and updated. otherwise returns false. 
    public function editContent($id,$editedContent) {
        $db = $this->getConnection();
        $db->query("UPDATE notes SET content='$editedContent' WHERE id = $id;");
        // need error handling.
        return true;
    }

    // returns true if the id was found and deleted. otherwise returns false. 
    // NOTE: delete content on the subjectId is not supported in this test implementation
    public function deleteContent($id) {
        $db = $this->getConnection();
        $db->query("DELETE FROM notes WHERE id = $id;");
        // need error handling.
        return true;
    }

    // this should be temporary to help manage db connection stuff. code
    // igniter version should not need this.
    private function addTagInternal($parentId, $newTag, $db) {
        $db->query("INSERT INTO tags(notes_id,content) VALUES ($parentId,'$newTag');");
    }

    // returns true if the id was found and the newTag was added 
    public function addTag($parentId, $newTag) {
        $db = $this->getConnection(); 
        $this->addTagInternal($parentId,$newTag,$db);
        return true;
    }

    // returns true if the id was found and deleted. otherwise returns false.
    public function deleteTag($parentId, $tagId) {
        $db = $this->getConnection();
        $db->query("DELETE FROM tags WHERE notes_id = $parentId and id = $tagId;");
        return true;
    }

    // the db arg is temporary until we integrate with Code Igniter
    private function deleteAllTags($parentId, $db) {
        $db->query("DELETE FROM tags WHERE notes_id = $parentId;");
        return true;
    }

    // this should be temporary to help manage db connection stuff. code
    // igniter version should not need this.
    private function addMediaInternal($parentId, $newContent, $type, $description, $db) {
        if($description == null) {
            $db->query("INSERT INTO media (notes_id,content,description,media_type_id) VALUES ($parentId,'$newContent', null, (SELECT id FROM media_types WHERE type = '$type'));");
        } else {
            $db->query("INSERT INTO media (notes_id,content,description,media_type_id) VALUES ($parentId,'$newContent', '$description', (SELECT id FROM media_types WHERE type = '$type'));");
        }
    }

    // returns true if the id was found and the new media was added 
    public function addMedia($parentId, $newContent, $type, $description) {
        $db = $this->getConnection();
        $this->addMediaInternal($parentId,$newContent,$type,$description,$db);
        return true;
    }

    // returns true if the id was found and deleted. otherwise returns false.
    public function deleteMedia($parentId, $mediaId) {
        $db = $this->getConnection();
        $db->query("DELETE FROM media WHERE notes_id = $parentId and id = $mediaId;");
        return true;
    }

    // the db arg is temporary until we integrate with code igniter
    private function deleteAllMedia($parent,$db) {
        $db->query("DELETE FROM media WHERE notes_id = $parentId;");
        return true;
    }

}

?>