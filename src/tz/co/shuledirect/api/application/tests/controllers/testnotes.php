<?php

include_once dirname(__FILE__).'/../CIUnit.php';

class testNotes extends CIUnit_TestCase{

	$EC2_URL = "ec2-54-200-63-121.us-west-2.compute.amazonaws.com"; //UPDATE THIS VARIABLE EVERYTIME!!!

    function setUp(){
        $this->CI = set_controller('notes');
    }

    public function testgetAugmentedNotes() {
    	
    	
    }

}