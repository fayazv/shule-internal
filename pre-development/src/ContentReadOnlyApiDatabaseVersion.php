<?php

/**
 * The purpose of this implementation of the API is to ensure all the database
 * interactions are correct. This version does not make the actual web api
 * calls, but simulates them to skip to how the model would function. 
 *
 * Specifically, this API is for access notes and syllabus content.
 *
 * Usage: 
 *     $sdk = new ContentReadOnlyApiDatabaseVersion();
 */

interface ContentReadOnlySDK
{
    /*
      these methods translate from 
      form [, subject, topic, subtopic, concept] 
      into a single unique id
      all forms, subjects, topics, subtopics, concepts and paragraphs use the 
      same id space -- that is, a single id is uniquely identifiable as exactly
      one of those.
      tags have their own ids so tags are uniquely identified using 
      a (parentId,tagId) pair
    */

    // NOTE in PHP we can only implement this as one function with my default args so all the intermediate functions are commented out and just for reading along with the API spec

    /**
     * @return an id representing the ShuleDirect project, the root of the content tree. Returns 0 if no Project is defined. 
     */
//    public function getId();

    /**
     * @return an id representing the form based on the string representation
     * of the form name if found, else returns 0
     */
//    public function getId($formName);

    /**
     * @return an id representing the subject based on the string representation
     * of the form and subject names if found, else returns 0
     */
//    public function getId($formName, $subjectName);

    /**
     * @return an id representing the subject based on the string representation
     * of the form, subject, and topic names if found, else returns 0
     */
//    public function getId($formName, $subjectName, $topicName);

    /**
     * @return an id representing the subject based on the string representation
     * of the form, subject, topic, and subtopic names if found, else returns 0 
     */
//    public function getId($formName, $subjectName, $topicName, $subtopicName);

    /**
     * @return an id representing the subject based on the string representation
     * of the form, subject, topic, subtopic, and concept names if found, else returns 0
     */
    public function getId($formName = NULL, $subjectName = NULL, $topicName = NULL, $subtopicName = NULL, $conceptName = NULL);

    /**
     * Returns all the ids and names for the next level down from the id provided
     * For example, calling getChildren(topicId) returns the names and ids for 
     * the subtopics under that topic.
     * 
     * If a concept is provided, each string is a paragraph of actual content, 
     * while the id references that paragraph to access media for that 
     * paragraph
     * 
     * Expected ids: project, form, subject, topic, subtopic, concept
     * Unrecognized: return an empty list.
     * 
     */
//    public function getChildren($id);
        
    /** 
     * Returns the tags for the id provided. 
     * For example, calling getChildren(topicId) returns the names and ids for 
     * the subtopics under that topic.
     * 
     * Expected ids: topic, subtopic, concept
     * Unrecognized: return an empty list
     */
//    public function getTags($id);

/**
 * Returns all the types of media available. They can be 
 * used as arguments to getMedia()
 * 
 * Expected ids: subtopic, concept, paragraph
 * Unrecognized: return an empty list
 */
//List<String> getMediaTypes(int id);

/**
 * Returns all the media of all types as links (strings)
 * 
 * Expected ids: subtopic, concept, paragraph
 * Unrecognized: return an empty list
 */
//List<IdContentHolder> getMedia(int id);

/**
 * Returns all the media of the given type, as links (strings)
 * 
 * Expected ids: subtopic, concept, paragraph
 * Unrecognized: return an empty list
 */
//List<IdContentHolder> getMedia(int id, String type);

/**
 * Returns all the media of the given types, as links (strings)
 * The key String is the media type
 * The value contains the mediaId and link string 
 * 
 * Expected ids: subtopic, concept, paragraph
 * Unrecognized: return an empty list
 */
//List<String,IdContentHolder> getMedia(int id, List<String> types);

/**
 * Returns what type the id represents
 * 
 * @return "Form", "Subject", "Topic", "Subtopic", "Concept", "Paragraph"
 */
//String getType(int id);

/**
 * Returns of a JSON object of the entire syllabus of the section level 
 * uniquely described by the id
 *
 * Expected ids: subject, topic, subtopic
 * Unrecognized: return an empty object
 */
//public String getSyllabus(int id);

/**
 * Returns of a JSON object of the entire syllabus of the section level 
 * uniquely described by the id. Includes tags and media. 
 *
 * Expected ids: subject, topic, subtopic
 * Unrecognized: return an empty object
 */
//public String getAugmentedSyllabus(int id);

/**
 * Returns of a JSON object of all the notes of the section level 
 * uniquely described by the id. The syntax is described in the 
 * JSON Format page. This method is intended to simplify retrieving and  
 * showing notes so the accepted id types are limited to those that would 
 * produce reasonable displayable chunks. 
 *
 * Media and tags are not included. 
 *
 * Expected ids: subtopic, concept
 * Unrecognized: return an empty object
 */
//public String getNotes(int id);

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
 * NOTE: current implementation accepts any notes id and uses that as the tree root as long as it is Subject or lower. We should discuss if this is appropriate. 
 * Unrecognized: return an empty object
 */
//public String getAugmentedNotes(int id);


}


class ContentReadOnlySDKDatabaseVersion implements ContentReadOnlySDK
{
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

    public function getId($formName = NULL, $subjectName = NULL, $topicName = NULL, $subtopicName = NULL, $conceptName = NULL) {
        $db = $this->getConnection();
        $projectName = "ShuleDirect";

        // build up the WHERE and JOIN clauses depending on how many parameters are provided. The params provided also determine which id to return 
        $whereClause = "WHERE project.content = '$projectName' AND project.parent_notes_id IS NULL";
        $joinClause = "";
        $returnId = "project.id"; // defaults to project, may be overwritten
        
        if($formName != NULL) {
            $joinClause .= " JOIN notes form ON project.id = form.parent_notes_id";
            $whereClause .= " AND form.content = '$formName'";

            if($subjectName != NULL) {
                $joinClause .= " JOIN notes subject ON form.id = subject.parent_notes_id";
                $whereClause .= " AND subject.content = '$subjectName'";

                if($topicName != NULL) {
                    $joinClause .= " JOIN notes topic ON subject.id = topic.parent_notes_id";
                    $whereClause .= " AND topic.content = '$topicName'";

                    if($subtopicName != NULL) {
                        $joinClause .= " JOIN notes subtopic ON topic.id = subtopic.parent_notes_id";
                        $whereClause .= " AND subtopic.content = '$subtopicName'";
                        
                        if($conceptName != NULL) {
                            $joinClause .= " JOIN notes concept ON subtopic.id = concept.parent_notes_id";
                            $whereClause .= " AND concept.content = '$conceptName'";
                            $returnId = "concept.id";
                        } else {
                            $returnId = "subtopic.id";
                            // and we're done with the params
                        }
                    } else {
                        $returnId = "topic.id";
                        // and we're done with the params
                    }                    
                } else {
                    $returnId = "subject.id";
                    // and we're done with the params
                }
            } else {
                $returnId = "form.id";
                // and we're done with the params
            }
        } 

        $selectIdQuery = "SELECT $returnId FROM notes AS project $joinClause $whereClause;";
        $idResultSet = $db->query($selectIdQuery);
        if($foundId = $idResultSet->fetch()) {
            return $foundId['id'];
        } else {
            return 0;
        }
    }
  
}

?>