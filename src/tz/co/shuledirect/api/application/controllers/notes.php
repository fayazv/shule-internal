<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

// So the controller will always recieve a string that it will parse and send contents to the model. 
// The model will then take these values and make appropriate db calls. 

class Notes extends REST_Controller
{
    public function __construct()
    {
        parent::__construct(); 
        $this->load->model('Notes_model');
    }

    function getId_post() 
    {
        $object = json_decode($this->input->post("inputJson"), true);
        if ($object == null) 
        {
            $id = $this->Notes_model->getId();
        } 
        else 
        {
            if (array_key_exists("form", $object))
            {
                $form = $object["form"];
                if (array_key_exists("subject", $object))
                {
                    $subject = $object["subject"];
                    if(array_key_exists("topic", $object))
                    {
                        $topic = $object["topic"];
                        if(array_key_exists("subtopic", $object))
                        {
                            $subtopic = $object["subtopic"];
                            if(array_key_exists("concept", $object))
                            {
                                $concept = $object["concept"];
                                $id = $this->Notes_model->getId($form, $subject, $topic, $subtopic, $concept);
                            }
                            else
                            {
                                $id = $this->Notes_model->getId($form, $subject, $topic, $subtopic);
                            }
                        }
                        else
                        {
                            $id = $this->Notes_model->getId($form, $subject, $topic);
                        }
                    }
                    else
                    {
                        $id = $this->Notes_model->getId($form, $subject);
                    }
                }
                else
                {
                    $id = $this->Notes_model->getId($form);
                }
            } 
            else
            {
                //no content so query empty...
                $id = $this->Notes_model->getId();
            }
        }
        $responseJson = '{"id":' + $id + '}';
        $this->response($responseJson); 
    }

    function getAugmentedNotes_post()
    {
        $object = json_decode($this->input->post("inputJson"), true);
	
        if (array_key_exists("id", $object))
        {
            $subjectId = $object["id"];
            $augmentedNotesObject = $this->Notes_model->getAugmentedNotes($subjectId);
            $this->response($augmentedNotesObject);   
        } 
        else 
        {
            $this->response("Please include an id in your query");
        }	
    }
   
}


