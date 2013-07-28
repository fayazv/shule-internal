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
 *
 * Usage: 
 *     $sdk = new ContentAdministrationSDKImpl(<dataDir>,<subjectId>);
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

    function __construct($baseDirectory, $subjectId, $subjectName) {
        $this->baseDirectory = $baseDirectory;
        $this->subjectId = $subjectId;
        $this->setAugmentedNotes($subjectId,'{"id":'.$subjectId.',"content":"'.$subjectId.'"}');
    }

    private function generateUniqueId() {
        return intval(1000000*microtime(true)); 
    }

    private function loadNotesArray() {
        $notesContent = $this->getAugmentedNotes($this->subjectId);

        $notesArray = json_decode($notesContent, true);
        if(!$notesArray)
        {
            throw new Exception('Could not decode JSON. Check to ensure it matches the JSON spec, including looking for missing/extra commas or unmatched quotation marks.');
        }
        return $notesArray ;
    }
    
    private function save($subject_id, $json)
    {
        //save the serialized array version into a file
        $success = file_put_contents("{$this->baseDirectory}/{$subject_id}", serialize($json));
      
        //if saving was not successful, throw an exception
        if( $success === false ) 
        {
            throw new Exception('Failed to save notes item');
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
    
    // this modifies the provided notesElement to fill in missing ids recursively
    private function idAssigner(&$notesElement) {
        // check if this tier has a "content" key and no "id" key. If so, create a unique id key.
        if (array_key_exists('content',$notesElement) && !array_key_exists('id',$notesElement)) {
            $notesElement['id'] = $this->generateUniqueId();
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
        
        $this->idAssigner($notesArray);

        $updatedContent = json_encode($notesArray);
        $this->save($subjectId,$updatedContent);
    }

    // this searches the provided notesElement recursively to find the parentId. Then add a new child to the end. Give it a new random id and assign the newContent
    // returns true if the parentId was found and the child was added
    private function addChild(&$notesElement,$parentId,$newContent) {
        // check if we've found the id of interest
        if ($notesElement['id'] == $parentId) {
            // get the current child count
            $newChildIndex = 0;
            if(array_key_exists('children',$notesElement) && count($notesElement['children']) > 0) {
                $newChildIndex = max(array_keys($notesElement['children']))+1;
            }
            
            $notesElement['children'][$newChildIndex]['id'] = $this->generateUniqueId();
            $notesElement['children'][$newChildIndex]['content'] = $newContent;
            return true;
        }
        // otherwise check the children, if any exist
        if(array_key_exists('children',$notesElement)) {
            foreach($notesElement['children'] as &$child) {
                $returnValue = $this->addChild($child,$parentId,$newContent);
                if ( $returnValue ) {
                    return true;
                }
            }
        }
        return false;
    }

    // returns true if the id was found and the newContent was added as a child
    public function addContent($parentId,$newContent) {
        // get the content. and then walk the array to look for the provided parentId. If it exists, add the newContent as the last child. Give it a new id.
        $notesArray = $this->loadNotesArray();        

        $returnValue = false;
        if ( $this->addChild($notesArray,$parentId,$newContent) ) {
            $returnValue = true;
        }

        $updatedContent = json_encode($notesArray);
        $this->save($this->subjectId,$updatedContent);
        return $returnValue;
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
        // get the content, and then walk the array to look for the provided id. if it exists, replace the content
        $notesArray = $this->loadNotesArray();
        
        $returnValue = false;
        if ( $this->updateIdContent($notesArray,$id,$editedContent) ) {
            $returnValue = true;
        }

        $updatedContent = json_encode($notesArray);
        $this->save($this->subjectId,$updatedContent);
        return $returnValue;
    }

    // input: array with integer keys
    // output: re-key the array so that it's keys are 0 to len(arry)-1
    private function renumberArrayKeys(&$array) {
        $i = 0;
        foreach(array_keys($array) as $key) {
            if(!array_key_exists($i,$array))
            {
                $array[$i] = $array[$key];
                unset($array[$key]);
            }
            $i++;
        }
    }

    // this searches the provided notesElement recursively to find the id and delete the subtree
    // returns true if the id was found and deleted
    private function deleteIdContent(&$parent, $key, &$notesElement,$id) {
        // check if we've found the id of interest
        if ($notesElement['id'] == $id) {
            unset($parent[$key]);
            $this->renumberArrayKeys($parent);
            return true;
        }
        // otherwise check the children, if any exist
        if(array_key_exists('children',$notesElement)) {
            foreach($notesElement['children'] as $childKey=>&$child) {
                $returnValue = $this->deleteIdContent($notesElement['children'], $childKey, $child,$id);
                if ( $returnValue ) {
                    return true;
                }
            }
        }
        return false;
    }

    // returns true if the id was found and deleted. otherwise returns false. 
    // NOTE: delete content on the subjectId is not supported in this test implementation
    public function deleteContent($id) {
        // get the content, and the walk the array to look for the provided id. if it exists, delete it and its subtree
        $notesArray = $this->loadNotesArray();
        
        $returnValue = false;
        // parent and key not used at this tier
        if ( $this->deleteIdContent($notesArray, 0, $notesArray,$id) ) {
            $returnValue = true;
        }

        $updatedContent = json_encode($notesArray);
        $this->save($this->subjectId,$updatedContent);
        return $returnValue;
    }

    // this searches the provided notesElement recursively to find the parentId. Then add a new tag to the end. Give it a new random id and assign the newTag
    // returns true if the parentId was found and the newTag was added
    private function addTagInternal(&$notesElement,$parentId,$newTag) {
        // check if we've found the id of interest
        if ($notesElement['id'] == $parentId) {
            // get the current tag count
            $newTagIndex = 0;
            if(array_key_exists('tags',$notesElement) && count($notesElement['tags']) > 0) {
                $newTagIndex = max(array_keys($notesElement['tags']))+1;
            }
            $notesElement['tags'][$newTagIndex]['id'] = $this->generateUniqueId();
            $notesElement['tags'][$newTagIndex]['content'] = $newTag;
            return true;
        }
        // otherwise check the children, if any exist
        if(array_key_exists('children',$notesElement)) {
            foreach($notesElement['children'] as &$child) {
                $returnValue = $this->addTagInternal($child,$parentId,$newTag);
                if ( $returnValue ) {
                    return true;
                }
            }
        }
        return false;
    }

    // returns true if the id was found and the newTag was added 
    public function addTag($parentId, $newTag) {
        // get the content. and then walk the array to look for the provided parentId. If it exists, add the newTag as the last tag. Give it a new id.
        $notesArray = $this->loadNotesArray();        

        $returnValue = false;
        if ( $this->addTagInternal($notesArray,$parentId,$newTag) ) {
                $returnValue = true;
        }

        $updatedContent = json_encode($notesArray);
        $this->save($this->subjectId,$updatedContent);
        return $returnValue; 
    }

    // this searches the provided notesElement recursively to find the id and delete it
    // returns true if the id was found and deleted
    private function deleteTagInternal(&$notesElement,$parentId,$tagId) {
        // check if we've found the id of interest
        if ($notesElement['id'] == $parentId) {
            if(array_key_exists('tags',$notesElement)) {
                // iterate the array and check all the ids
                foreach($notesElement['tags'] as $key=>$tagHolder) {
                    if($tagHolder['id'] == $tagId) {
                        unset($notesElement['tags'][$key]);
                        $this->renumberArrayKeys($notesElement['tags']);
                        return true;
                    }
                }
            } 
            return false;
        }
        // otherwise check the children, if any exist
        if(array_key_exists('children',$notesElement)) {
            foreach($notesElement['children'] as $childKey=>&$child) {
                $returnValue = $this->deleteTagInternal($child, $parentId, $tagId);
                if ( $returnValue ) {
                    return true;
                }
            }
        }
        return false;
    }

    // returns true if the id was found and deleted. otherwise returns false.
    public function deleteTag($parentId, $tagId) {
        // get the content, and the walk the array to look for the provided id. if it exists, delete it 
        $notesArray = $this->loadNotesArray();
        
        $returnValue = false;
        if ( $this->deleteTagInternal($notesArray,$parentId, $tagId) ) {
            $returnValue = true;
        }

        $updatedContent = json_encode($notesArray);
        $this->save($this->subjectId,$updatedContent);
        return $returnValue;
    }

    // this searches the provided notesElement recursively to find the parentId. Then add a new media element to the end. Give it a new random id and assign the new media info
    // returns true if the parentId was found and the new media was added
    private function addMediaInternal(&$notesElement,$parentId,$newContent, $type, $description, $isPrintable) {
        // check if we've found the id of interest
        if ($notesElement['id'] == $parentId) {
            // get the current media count. include the type in the search. 
            $newMediaIndex = 0;
            if(array_key_exists('media',$notesElement) && count($notesElement['media']) > 0 && array_key_exists($type,$notesElement['media']) && count($notesElement['media'][$type]) > 0) {
                $newMediaIndex = max(array_keys($notesElement['media'][$type]))+1;
            }
            $notesElement['media'][$type][$newMediaIndex]['id'] = $this->generateUniqueId();
            $notesElement['media'][$type][$newMediaIndex]['content'] = $newContent;
            $notesElement['media'][$type][$newMediaIndex]['description'] = $description;
            $notesElement['media'][$type][$newMediaIndex]['isPrintable'] = $isPrintable;
            return true;
        }
        // otherwise check the children, if any exist
        if(array_key_exists('children',$notesElement)) {
            foreach($notesElement['children'] as &$child) {
                $returnValue = $this->addMediaInternal($child,$parentId,$newContent, $type, $description, $isPrintable);
                if ( $returnValue ) {
                    return true;
                }
            }
        }
        return false;
    }

    // returns true if the id was found and the new media was added 
    public function addMedia($parentId, $newContent, $type, $description, $isPrintable) {
        // get the content. and then walk the array to look for the provided parentId. If it exists, add the new media as the last media entry. Give it a new id.
        $notesArray = $this->loadNotesArray();        

        $returnValue = false;
        if ( $this->addMediaInternal($notesArray,$parentId,$newContent, $type, $description, $isPrintable) ) {
            $returnValue = true;
        }

        $updatedContent = json_encode($notesArray);
        $this->save($this->subjectId,$updatedContent);
        return $returnValue; 
    }

    // this searches the provided notesElement recursively to find the id and delete it
    // returns true if the id was found and deleted
    private function deleteMediaInternal(&$notesElement,$parentId,$mediaId) {
        // check if we've found the id of interest
        if ($notesElement['id'] == $parentId) {
            if(array_key_exists('media',$notesElement)) {
                // iterate the array and check all the ids
                foreach($notesElement['media'] as $mediaTypeKey=>&$mediaTypeValues) {
                    foreach($mediaTypeValues as $key=>$mediaHolder) {
                        if($mediaHolder['id'] == $mediaId) {
                            unset($mediaTypeValues[$key]);
                            $this->renumberArrayKeys($mediaTypeValues);
                            // check if the media type is now empty
                            if( count($mediaTypeValues) == 0) {
                                unset($notesElement['media'][$mediaTypeKey]);
                            }
                            return true;
                        }
                    }
                }
            } 
            return false;
        }
        // otherwise check the children, if any exist
        if(array_key_exists('children',$notesElement)) {
            foreach($notesElement['children'] as $childKey=>&$child) {
                $returnValue = $this->deleteMediaInternal($child, $parentId, $mediaId);
                if ( $returnValue ) {
                    return true;
                }
            }
        }
        return false;
    }

    // returns true if the id was found and deleted. otherwise returns false.
    public function deleteMedia($parentId, $mediaId) {
        // get the content, and the walk the array to look for the provided id. if it exists, delete it 
        $notesArray = $this->loadNotesArray();
        
        $returnValue = false;
        if ( $this->deleteMediaInternal($notesArray,$parentId, $mediaId) ) {
                $returnValue = true;
        }

        $updatedContent = json_encode($notesArray);
        $this->save($this->subjectId,$updatedContent);
        return $returnValue;
    }

}

?>