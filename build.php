<?php

if (! isset($argv[1])) die('project name missing'.PHP_EOL); 

$project = $argv[1];
$archive = $project.'.phar';

if (file_exists($archive)) {

    unlink($archive);
}

$phar = new Phar($archive);
$phar->setDefaultStub($project.'.php', $project.'.php');
$phar->buildFromDirectory('src');

