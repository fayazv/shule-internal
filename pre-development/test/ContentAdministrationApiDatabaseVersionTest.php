<?php

include_once('/home/ldoshi/Documents/ShuleTemp/shule-internal/pre-development/src/ContentAdministrationApiDatabaseVersion.php');

$sdk = new ContentAdministrationSDKDatabaseVersion();
$projectId = 1;
/*$sdk->addContent($projectId, "Form 1");
$sdk->addContent($projectId, "Form 2");
$sdk->addContent($projectId, "Form 3");
$sdk->addContent($projectId, "Form 4");
$sdk->addContent(2, "Physics");
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

$samplePhysicsContent = file_get_contents("input/samplePhysicsNotesAsAllNewContent");
$sdk->setAugmentedNotes($samplePhysicsContent);
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