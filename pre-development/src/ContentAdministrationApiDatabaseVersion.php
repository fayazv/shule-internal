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
        if( file_exists("{$this->baseDirectory}/{$subjectId}") === false ) {
            throw new Exception('Subject ID is invalid');
        }
        $augmented_notes_serialized = file_get_contents("{$this->baseDirectory}/{$subjectId}");
        $augmented_notes_json = unserialize($augmented_notes_serialized);
            
        return $augmented_notes_json;
    }

    public function setAugmentedNotes($subjectId, $newContent) {
        $data = json_decode($newContent);

        $db = $this->getConnection();
        $db->query("START TRANSACTION WITH CONSISTENT SNAPSHOT ");
        
        // confirm the top-level is a subject id
        $subject_found_query = $db->query("SELECT * FROM notes JOIN note_types ON notes.note_type_id = note_types.id WHERE notes.id = $subjectId AND note_types.name = 'Subject';");
        $subject_found = $subject_found_query->rowCount();
        echo "hi: $subject_found";
        

        // set whole tree to have position of -1
            
        // update everything from input. if any unknown id is introduced, rollback

        // delete everything at position -1


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

//    todo ldoshi: do edit, delete, add tag, add media... now fk should be enforced so dont need to check for ids existing.

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

    // returns true if the id was found and the newTag was added 
    public function addTag($parentId, $newTag) {
        $db = $this->getConnection();
        $db->query("INSERT INTO tags(notes_id,content) VALUES ($parentId,'$newTag');");
        return true;
    }

    // returns true if the id was found and deleted. otherwise returns false.
    public function deleteTag($parentId, $tagId) {
        $db = $this->getConnection();
        $db->query("DELETE FROM tags WHERE notes_id = $parentId and id = $tagId;");
        return true;
    }

    // returns true if the id was found and the new media was added 
    public function addMedia($parentId, $newContent, $type, $description) {
        $db = $this->getConnection();
        if($description == null) {
            $db->query("INSERT INTO media (notes_id,content,description,media_type_id) VALUES ($parentId,'$newContent', null, (SELECT id FROM media_types WHERE type = '$type'));");
        } else {
            $db->query("INSERT INTO media (notes_id,content,description,media_type_id) VALUES ($parentId,'$newContent', '$description', (SELECT id FROM media_types WHERE type = '$type'));");
        }
        return true;
    }

    // returns true if the id was found and deleted. otherwise returns false.
    public function deleteMedia($parentId, $mediaId) {
        $db = $this->getConnection();
        $db->query("DELETE FROM media WHERE notes_id = $parentId and id = $mediaId;");
        return true;
    }

}

?>