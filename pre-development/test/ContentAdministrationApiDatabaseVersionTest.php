<?php

include_once('/home/ldoshi/Documents/ShuleTemp/shule-internal/pre-development/src/ContentAdministrationApiDatabaseVersion.php');
include_once('/home/ldoshi/Documents/ShuleTemp/shule-internal/pre-development/src/ContentReadOnlyApiDatabaseVersion.php');

// given a content tree and an id, finds the content associated with the id. This does a tree walk every time and is strictly for testing. 
function getContentFromId($contentTree,$id) {
    // ensure there is an id element to have a chance
    if(array_key_exists('id',$contentTree)) {
        // check if we already found the id. if so, return it's contents
        if($contentTree['id'] == $id) {
            return $contentTree['content'];
        } else {
            // otherwise recurse on every child (if any exist). otherwise we're done. 
            if(array_key_exists('children',$contentTree)) {
                foreach($contentTree['children'] as &$child) {
                    $content = getContentFromId($child,$id);
                    if($content != NULL){
                        // we're done
                        return $content;
                    }
                }                
            } else {
                return NULL;
            }
        }
    } else {
        // no id --> return NULL
        return NULL;
    }
}

$sdk = new ContentAdministrationSDKDatabaseVersion();
$readonlysdk = new ContentReadOnlySDKDatabaseVersion();

// basic setup of forms and a subject
$projectId = $readonlysdk->getId();
$sdk->addContent($projectId, "Form 1");
$sdk->addContent($projectId, "Form 2");
$sdk->addContent($projectId, "Form 3");
$sdk->addContent($projectId, "Form 4");
$form2Id = $readonlysdk->getId("Form 2");
$sdk->addContent($form2Id, "Physics");
$physicsId = $readonlysdk->getId("Form 2","Physics");

// load in physics notes
$samplePhysicsContent = file_get_contents("input/samplePhysicsNotesAsAllNewContent");
// put in the correct subject id
$samplePhysicsContentTempArray = json_decode($samplePhysicsContent,true); 
$samplePhysicsContentTempArray['id'] = $physicsId;
$samplePhysicsContent = json_encode($samplePhysicsContentTempArray);
unset($samplePhysicsContentTempArray);

$sdk->setAugmentedNotes($samplePhysicsContent);
$physicsNotes = $sdk->getAugmentedNotes($physicsId) . "\n";
echo $physicsNotes;

$physicsNotesArray = json_decode($physicsNotes,true);

//echo $readonlysdk->getId() . "\n"; // cannot verify
//echo $readonlysdk->getId("Form 2") . "\n"; // cannot verify
echo "Form 5 DNE: ".( $readonlysdk->getId("Form 5") == 0 ). "\n"; // should be 0 since it does not exist
echo "Form2::Physics Found: ".(getContentFromId($physicsNotesArray,$readonlysdk->getId("Form 2","Physics")) == "Physics") . "\n";
echo "Form3::Physics DNE: ".($readonlysdk->getId("Form 3","Physics")==0) . "\n"; // should be 0 since it does not exist
echo "Form2::Physics_Fake DNE: ".( $readonlysdk->getId("Form 2","Physics_Fake") == 0 ) . "\n"; // should be 0 since it does not exist
echo "Form2::Physics::Mechanics Found: ".(getContentFromId($physicsNotesArray,$readonlysdk->getId("Form 2","Physics","Mechanics"))=="Mechanics") . "\n";
echo "Form2::Physics::mechanics Found: ".(getContentFromId($physicsNotesArray,$readonlysdk->getId("Form 2","Physics","mechanics"))=="Mechanics") . "\n";
echo "Form 2::Physics::Electricity and Magnetism Found: ".(getContentFromId($physicsNotesArray,$readonlysdk->getId("Form 2","Physics","Electricity and Magnetism")) == "Electricity and Magnetism") . "\n";
echo "Form 4::Physics::Electricity and Magnetism DNE: ".($readonlysdk->getId("Form 4","Physics","Electricity and Magnetism") ==0) . "\n"; // should be 0 since it does not exist
echo "Form 2::Physics::Electricity and Magnetism and Fake DNE: ".( $readonlysdk->getId("Form 2","Physics","Electricity and Magnetism and Fake") == 0) . "\n"; // should be 0 since it does not exist
echo "Form 2::Physics::Mechanics::energy Found: ". (getContentFromId($physicsNotesArray,$readonlysdk->getId("Form 2","Physics","Mechanics","energy")) == "energy") . "\n";
echo "Form 2::Physics::Mechanics::Energy Found: ". (getContentFromId($physicsNotesArray,$readonlysdk->getId("Form 2","Physics","Mechanics","Energy")) == "energy") . "\n";
echo "Form 2::Physics::Mechanics::Energy_fake DNE: ".($readonlysdk->getId("Form 2","Physics","Mechanics","Energy_fake") == 0) . "\n"; // should be 0 since it does not exist
echo "Form 2::Physics::Mechanics::energy::potential Found: ". (getContentFromId($physicsNotesArray,$readonlysdk->getId("Form 2","Physics","Mechanics","energy","potential")) == "potential") . "\n";
echo "Form 2::Physics::Mechanics::energy::Potential_fake DNE: ".($readonlysdk->getId("Form 2","Physics","Mechanics","energy","Potential_fake") == 0). "\n"; // should be 0 since it does not exist 
echo "Form 2::Physics::Mechanics::energy::potential Found: ".(getContentFromId($physicsNotesArray,$readonlysdk->getId("Form 2","Physics","Mechanics","eNergy","potential","stuff")) == "potential") . "\n"; // php seems to ignore the extra args

// check editContent

/*$sdk->editContent($projectId+1,"Form 11");
$sdk->editContent($projectId+2,"Form 1");
$sdk->deleteContent($projectId+3);
$sdk->addTag(114,"hi");
$sdk->addTag(1,"hi");
$sdk->addTag(1,"bye");
$sdk->addTag(1,"last");
$sdk->addTag(2,"other");
$sdk->deleteTag(1,3);
$sdk->addMedia(1,"media1","image","media1_desc");
$sdk->addMedia(1,"media2","Image","media2_desc");
$sdk->addMedia(1,"media3","Image",null);
$sdk->addMedia(1,"media4","YouTube","media4_desc");
$sdk->addMedia(1,"media5","Youtube","media5_desc");
$sdk->deleteMedia(1,4);
$sdk->deleteMedia(2,5);*/



exit;

$physicsSubjectId = 1;
$physicsSubjectName = "Physics";


echo $sdk->getAugmentedNotes($physicsSubjectId);
echo "\n\n";

// let's add things from scratch
$sdk->addContent($physicsSubjectId,"Topic1");
$sdk->addContent($physicsSubjectId,"Topic2");
$sdk->addContent($physicsSubjectId,"Topic3");
$augmentedNotes = $sdk->getAugmentedNotes($physicsSubjectId);
echo $augmentedNotes;
echo "\n\n";

// now get the ids from the notes
$augmentedNotesDecoded = json_decode($augmentedNotes, true);
$i=0;
$j=3;
foreach ($augmentedNotesDecoded['children'] as $topic) {
    $sdk->addContent($topic['id'],"Subtopic$i");
    $sdk->addContent($topic['id'],"Subtopic$j");
    $sdk->addTag($topic['id'],"Tag$i");
    $sdk->addTag($topic['id'],"Tag$j");
    $sdk->addMedia($topic['id'],"Media$i","image","Description$i",true);
    $sdk->addMedia($topic['id'],"Media$i","youtube","Description$i",false);
    $sdk->addMedia($topic['id'],"Media$j","image","Description$j",true);
    $i++;
    $j++;
}
echo $sdk->getAugmentedNotes($physicsSubjectId);
echo "\n\n";

// now try a simple set and get
$samplePhysicsContent = file_get_contents("input/samplePhysicsNotes");
$sdk->setAugmentedNotes($physicsSubjectId,$samplePhysicsContent);
echo $sdk->getAugmentedNotes($physicsSubjectId);
echo "\n\n";

// set with new content. the output should provide ids to the new content only.
$physicsSubjectId = 1;
$samplePhysicsWithNewContent = file_get_contents("input/samplePhysicsNotesWithNewContent");
$sdk->setAugmentedNotes($physicsSubjectId,$samplePhysicsWithNewContent);
echo $sdk->getAugmentedNotes($physicsSubjectId);
echo "\n\n";

$sdk->editContent(2,"Edited!");
//echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->editContent(4,"Edited!");
//echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->editContent(11,"Edited!");
//echo $sdk->getAugmentedNotes($physicsSubjectId);

echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->deleteContent(6);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->deleteContent(9);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->addContent(5,"Radiation");
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->addContent(3,"Basic Circuits");
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->deleteContent(3);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->addContent(1,"News"); 
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

echo "addTag\n";
// no-op because 3 is deleted
$sdk->addTag(3,"Gauss");
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->addTag(2,"Test Tag");
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->addTag(8,"cross-sectional area");
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->addTag(7,"normal force");
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->deleteTag(7,1);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->deleteTag(2,2);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

// no-op
$sdk->deleteTag(2,2);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->deleteTag(3,2);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->addMedia(7,"http://NEWMEDIA.com/media.jpg","image","This is a test",false);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->addMedia(7,"http://youtube.com/moremedia","youtube","This is a new type test",true);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->addMedia(10,"http://kineticimage.com/moving.jpg","image","This is a kinetic image",false);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->deleteMedia(8,1);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->deleteMedia(8,3);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->deleteMedia(8,2);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

// no-op
$sdk->deleteMedia(8,1);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

$sdk->deleteMedia(2,1);
echo "\n\n";
echo $sdk->getAugmentedNotes($physicsSubjectId);

?>