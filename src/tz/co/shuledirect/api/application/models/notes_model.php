<?php

class Notes_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
	$active_group = 'default';
	$this->load->database();
    }

    function getAugmentedNotes($subjectId)
    {
        //TODO handle errors correctly
        $this->db->query("START TRANSACTION WITH CONSISTENT SNAPSHOT ");
        
        // make sure the id exists in the notes table and is the subject level or deeper
        $idDepthQuery = $this->db->query("SELECT depth,(SELECT MAX(depth) FROM note_types) AS max_depth FROM notes JOIN note_types ON notes.note_type_id = note_types.id WHERE notes.id = ".$this->db->escape($subjectId)." AND depth >= (SELECT depth FROM note_types WHERE name = 'Subject');");
        $idFoundCount = $idDepthQuery->num_rows();
        if($idFoundCount == 0 ) {
            echo "id not found or is not at a level of Subject or lower";
            return;
        }
        else if($idFoundCount > 1) {
            echo "error data integrity violation";
            return;
        }
        
        $idDepthRow = $idDepthQuery->row();
        $idDepth = $idDepthRow->depth;
        $maxDepth = $idDepthRow->max_depth;

        // build a query to capture all the content.
        $contentQuery = "SELECT id AS parent_id_0,content,position AS position_0,note_type_id,parent_notes_id FROM notes WHERE id = ".$this->db->escape($subjectId)." GROUP BY notes.id";
        for($i=0;$i< ($maxDepth - $idDepth)  ;$i++) {
            $previousParentIds = "";
            for($j=0;$j<($i+1);$j++) {
                $previousParentIds = "$previousParentIds subquery$i.parent_id_$j,subquery$i.position_$j,";
            }
            $contentQuery = "SELECT $previousParentIds notes.id AS parent_id_".($i+1).",notes.position AS position_".($i+1).", notes.content,notes.note_type_id,notes.parent_notes_id FROM notes JOIN ( $contentQuery ) subquery$i ON subquery$i.parent_id_$i = notes.parent_notes_id OR subquery$i.parent_id_$i = notes.id GROUP BY notes.id";
        }
        $contentQuery .= ";";

        $contentResultSet = $this->db->query($contentQuery);        

        // convert to JSON to return.  do this by walking through the result
        // set and building a node at the lowest point in that row. Even if
        // they are not in perfect order, it is ok. We know we have the entire
        // tree built from the root, so we will eventually cover all the
        // nodes.
        $content = array();
        $parentIdCount = ($maxDepth - $idDepth)+1;
        
        // make sure we have results
        if($contentResultSet->num_rows() > 0 ) {
            foreach($contentResultSet->result_array() as $row) {
                // first start at the current id and move through the parent_id_i
                // columns until the id is not the $id or there are no columns
                // left
                $node = &$content;
                $parentIdIndex = 0;
                $nodeId = $subjectId;
                for(;$parentIdIndex<$parentIdCount;$parentIdIndex++) {

                    // found a starting point!
                    if($nodeId != $row["parent_id_$parentIdIndex"]) {
                        // navigate to the appropriate point in the content array tree  
                        // check if children already.
                        if(!array_key_exists('children',$node)) {
                            $node["children"] = array();
                        }
                        $nodeChild = &$node["children"];
                        // specifically find the array and the correct position
                        $position = $row["position_$parentIdIndex"];
                        // see if that position exists, otherwise fill in empty
                        // children until the position does.
                        if(count($nodeChild) <=  $position) {
                            for($i=count($nodeChild); $i < ($position+1);$i++) {
                                array_push($nodeChild,array());
                            }
                        }
                        $node = &$nodeChild[$position];
                        $nodeId = $row["parent_id_$parentIdIndex"];
                    }                
                }
                $node["id"] = $nodeId;
                $node["content"] = $row["content"];

                // add in tags (if any)
                $tagResultSet = $this->db->query("SELECT id,content FROM tags where notes_id = $nodeId;");
                // if any tags exist, create the key/array pair for "tags"
                if($tagResultSet->num_rows() > 0) {
                    $node["tags"] = array();
                    foreach($tagResultSet->result_array() as $tagRow) {
                        // add each tag's information with into the array
                        $tagInfo = array();
                        $tagInfo["id"] = $tagRow["id"];
                        $tagInfo["content"] = $tagRow["content"];
                        array_push($node["tags"],$tagInfo);
                    }
                }
                $tagResultSet->free_result();

                // add in media (if any)
                $mediaResultSet = $this->db->query("SELECT media_types.type,media.id,media.content,media.description,media_types.is_printable FROM media JOIN media_types ON media.media_type_id = media_types.id WHERE notes_id = $nodeId ORDER BY type ;");
                // if any media exist, create the key/array pair for "media"
                if($mediaResultSet->num_rows() > 0) {
                    $node["media"] = array();
                    // TODO ldoshi -- add isPrintable field somewhere
                    // add the information for each media entry into the array 
                    foreach($mediaResultSet->result_array() as $mediaRow) {
                        $mediaInfo = array();
                        $mediaInfo["id"] = $mediaRow["id"];
                        $mediaInfo["content"] = $mediaRow["content"];
                        // the description may be null
                        if(!is_null($mediaRow["description"])) {
                            $mediaInfo["description"] = $mediaRow["description"];
                        }
                        // check if the current type already exists as a key in the
                        // media, if not add it. Each type corresponds to an array of
                        // entries
                        $currentMedia = NULL;
                        if(!array_key_exists($mediaRow["type"],$node["media"])) {
                            $node["media"][$mediaRow["type"]] = array();
                        }
                        array_push($node["media"][$mediaRow["type"]],$mediaInfo);
                    }
                }
                $mediaResultSet->free_result();
            }
        }

        $this->db->query("ROLLBACK;");
        $contentResultSet->free_result();
        return json_encode($content);
    }
    
    function getId($form = NULL, $subject = NULL, $topic = NULL, $subtopic = NULL, $concept = NULL)
    {
        //TODO: The line below should NOT be here. Dunno why it is required.
        $this->db->query("START TRANSACTION WITH CONSISTENT SNAPSHOT ");
        $project = "ShuleDirect";

        // build up the WHERE and JOIN clauses depending on how many parameters are provided. The params provided also determine which id to return 
        $whereClause = "WHERE project.content = ".$this->db->escape($project)." AND project.parent_notes_id IS NULL";
        $joinClause = "";
        $returnId = "project.id"; // defaults to project, may be overwritten
        
        if($form != NULL) {
            $joinClause .= " JOIN notes form ON project.id = form.parent_notes_id";
            $whereClause .= " AND form.content = ".$this->db->escape($form)." ";

            if($subject != NULL) {
                $joinClause .= " JOIN notes subject ON form.id = subject.parent_notes_id";
                $whereClause .= " AND subject.content = ".$this->db->escape($subject)." ";

                if($topic != NULL) {
                    $joinClause .= " JOIN notes topic ON subject.id = topic.parent_notes_id";
                    $whereClause .= " AND topic.content = ".$this->db->escape($topic)." ";

                    if($subtopic != NULL) {
                        $joinClause .= " JOIN notes subtopic ON topic.id = subtopic.parent_notes_id";
                        $whereClause .= " AND subtopic.content = ".$this->db->escape($subtopic)." ";
                        
                        if($concept != NULL) {
                            $joinClause .= " JOIN notes concept ON subtopic.id = concept.parent_notes_id";
                            $whereClause .= " AND concept.content = ".$this->db->escape($concept)." ";
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
        $idQuery = $this->db->query($selectIdQuery);
        if($idQuery->num_rows() > 0) {
            return $idQuery->row()->id;
        } else {
            // TODO whats the correct 'not found' handling?
            return 0;
        }
    }
    
}

