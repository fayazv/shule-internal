<?php

include_once('/home/ldoshi/Documents/ShuleTemp/shule-internal/pre-development /src/ContentAdministrationApi.php');

$physicsSubjectId = 1;
$sdk = new ContentAdministrationSDKImpl("/tmp/",$physicsSubjectId);

// simple set and get
$samplePhysicsContent = file_get_contents("input/samplePhysicsNotes");
$sdk->setAugmentedNotes($physicsSubjectId,$samplePhysicsContent);
echo $sdk->getAugmentedNotes($physicsSubjectId);

// set with new content. the output should provide ids to the new content only.
$physicsSubjectId = 1;
$samplePhysicsWithNewContent = file_get_contents("input/samplePhysicsNotesWithNewContent");
$sdk->setAugmentedNotes($physicsSubjectId,$samplePhysicsWithNewContent);
echo $sdk->getAugmentedNotes($physicsSubjectId);

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


?>