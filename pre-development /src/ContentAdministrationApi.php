<?php

/**
 * The purpose of this SDK is to enable parallel development of the back-end
 * API and portions of the front-end.  Now that the API is fairly stable, this
 * seems reasonable for efficiency. The name of the class may change in the
 * future, but that is a simple refactoring change. Adding authentication
 * later will also add a few small changes. 
 *
 * Specifically, this API is for adding and editing notes/syllabus content,
 * which involves displaying the content on the editing pages as well.
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
    public function addContent($id,$newContent);
   
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
    public function addTags($parentId, $newTag);

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
    public function addMedia($parentId, $newContent, $type, $description, $isPrintable);

    /**
     * Sets all the media of the given types, as links (strings)
     * The key is the type, the value is the link string
     */
    public function deleteMedia($parentId, $mediaId);

}

class ContentAdministrationSDKImpl implements ContentAdministrationSDK
{
    private $baseDirectory;
    private $subjectId;

    function __construct($baseDirectory, $subjectId) {
        $this->baseDirectory = $baseDirectory;
        $this->subjectId = $subjectId;
    }

    private function save($subject_id, $json)
    {
        //save the serialized array version into a file
        $success = file_put_contents("{$this->baseDirectory}/{$subject_id}.txt", serialize($json));
      
        //if saving was not successful, throw an exception
        if( $success === false ) 
        {
            throw new Exception('Failed to save notes item');
        }
    }
    
    public function getAugmentedNotes($subjectId) {
        if( file_exists("{$this->baseDirectory}/{$subjectId}.txt") === false ) {
            throw new Exception('Subject ID is invalid');
        }
      
        $augmented_notes_serialized = file_get_contents("{$this->baseDirectory}/{$subjectId}.txt");
        $augmented_notes_json = unserialize($augmented_notes_serialized);
            
        return $augmented_notes_json;
    }
    
    // this modifies the provided notesElement to fill in missing ids recursively
    private function idAssigner(&$notesElement) {
        // check if this tier has a "content" key and no "id" key. If so, create a unique id key.
        if (array_key_exists('content',$notesElement) && !array_key_exists('id',$notesElement)) {
            $notesElement['id'] = intval(1000000*microtime(true)); // generate a unique id
        }
        //iterate keys and recurse on any that are arrays 
        foreach ($notesElement as $key=>&$value) {
            if(is_array($value)) {
                $this->idAssigner($value);
            }
        }
    }

    public function setAugmentedNotes($subjectId, $newContent) {
        // make sure all content has an id. Walk the entire tree. If a
        // "content" key exists but no "id" key exists, add an "id" key. 
        $notesArray = json_decode($newContent, true);
        if(!$notesArray)
        {
            throw new Exception('Could not decode JSON. Check to ensure it matches the JSON spec, including looking for missing/extra commas or unmatched quotation marks.');
        }
        
        foreach ($notesArray as &$notesElement) {
            $this->idAssigner($notesElement);
        }

        $updatedContent = json_encode($notesArray);
        $this->save($subjectId,$updatedContent);
    }

    public function addContent($id,$newContent) {

    }

    // this searches the provided notesElement recursively to find the id and update the content
    // returns true if the id was found and updated
    private function updateIdContent(&$notesElement,$id,$editedContent) {
        // check if we've found the id of interest
        if ($notesElement['id'] == $id) {
            $notesElement['content'] = $editedContent;
            return true;
        }
        // otherwise check the children, if any exist
        if(array_key_exists('children',$notesElement)) {
            foreach($notesElement['children'] as &$child) {
                $returnValue = $this->updateIdContent($child,$id,$editedContent);
                if ( $returnValue ) {
                    return true;
                }
            }
        }
        return false;
    }

    // returns true if the id was found and updated. otherwise returns false. 
    public function editContent($id,$editedContent) {
        // get the content, and the walk the array to look for the provided id. if it exists, replace the content
        $notesContent = $this->getAugmentedNotes($this->subjectId);

        $notesArray = json_decode($notesContent, true);
        if(!$notesArray)
        {
            throw new Exception('Could not decode JSON. Check to ensure it matches the JSON spec, including looking for missing/extra commas or unmatched quotation marks.');
        }
        
        $returnValue = false;
        foreach ($notesArray as &$notesElement) {
            if ( $this->updateIdContent($notesElement,$id,$editedContent) ) {
                echo "lol";
                $returnValue = true;
                break;
            }
        }

        $updatedContent = json_encode($notesArray);
        $this->save($this->subjectId,$updatedContent);
        return $returnValue;
    }

    public function deleteContent($id) {

    }

    public function addTags($parentId, $newTag) {

    }

    public function deleteTag($parentId, $tagId) {

    }

    public function addMedia($parentId, $newContent, $type, $description, $isPrintable) {

    }

    public function deleteMedia($parentId, $mediaId) {

    }

}

?>