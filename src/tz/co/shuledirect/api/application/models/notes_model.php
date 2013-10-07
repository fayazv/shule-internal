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
        //Implement this!
        $form = NULL;
        return true;
    }
    
}

