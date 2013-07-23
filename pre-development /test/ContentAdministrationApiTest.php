<?php

include_once('/home/ldoshi/Documents/ShuleTemp/shule-internal/pre-development /src/ContentAdministrationApi.php');

$physicsSubjectId = 1;
$sdk = new ContentAdministrationSDKImpl("/tmp/",$physicsSubjectId);

// simple set and get
$samplePhysicsContent = file_get_contents("input/samplePhysicsNotes");
$sdk->setAugmentedNotes($physicsSubjectId,$samplePhysicsContent);
//echo $sdk->getAugmentedNotes($physicsSubjectId);

// set with new content. the output should provide ids to the new content only.
$physicsSubjectId = 1;
$samplePhysicsWithNewContent = file_get_contents("input/samplePhysicsNotesWithNewContent");
$sdk->setAugmentedNotes($physicsSubjectId,$samplePhysicsWithNewContent);
//echo $sdk->getAugmentedNotes($physicsSubjectId);

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
?>