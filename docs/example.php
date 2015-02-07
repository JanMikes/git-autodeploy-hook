<?php

// This file should be located in your document
// Usage: just call http://localhost/example.php by POST from allowed ip addresses with allowed user agent

require __DIR__ . '/../vendor/autoload.php';

$allowedIps = ["131.103.20.165", "131.103.20.166"];
$allowedMethods = ["POST"];
$allowedUserAgents = ["Bitbucket.org"];
$logDirectory = __DIR__ . "/../log";

$hook = new Lexinek\GitAutodeployHook\Hook($allowedIps, $allowedMethods, $allowedUserAgents, $logDirectory);

$hook->execPull();

$hook->execFunc(function() {
	shell_exec("sudo git fetch 2>&1");
	shell_exec("sudo git pull 2>&1");
});
