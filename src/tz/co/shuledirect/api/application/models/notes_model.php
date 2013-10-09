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
        //TODO: implement this method
    	$query = $this->db->query("select id from notes limit 1");
    	$row = $query->row();
    	return $row->id;
    }
    
    public function getId($form = NULL, $subject = NULL, $topic = NULL, $subtopic = NULL, $concept = NULL)
    {
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
                    $whereClause .= " AND topic.content = ".$this->db->escape($subject)." ";

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

