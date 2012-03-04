<?php

$project = 'picoMapper';
$archive = $project.'.phar';

if (file_exists($archive)) {

    unlink($archive);
}

$phar = new Phar($archive);
$phar->setDefaultStub($project.'.php', $project.'.php');
$phar->buildFromDirectory('src');

