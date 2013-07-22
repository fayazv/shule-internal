<?php

include_once('/home/ldoshi/Documents/ShuleTemp/shule-internal/pre-development /src/ContentAdministrationApi.php');

$sdk = new ContentAdministrationSDKImpl("/tmp/");

// simple set and get
$physicsSubjectId = 1;
$samplePhysicsContent = file_get_contents("input/samplePhysicsNotes");
$sdk->setAugmentedNotes($physicsSubjectId,$samplePhysicsContent);
echo $sdk->getAugmentedNotes($physicsSubjectId);

// set with new content. the output should provide ids to the new content only.
$physicsSubjectId = 1;
$samplePhysicsWithNewContent = file_get_contents("input/samplePhysicsNotesWithNewContent");
$sdk->setAugmentedNotes($physicsSubjectId,$samplePhysicsWithNewContent);
echo $sdk->getAugmentedNotes($physicsSubjectId);


?>