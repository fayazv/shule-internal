<?php
class Notes extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    function getAugmentedNotes()
    {
    	return "Ram is awesome";
    }

}

?>