#!/usr/bin/env php
<?php
// jira.php

require_once __DIR__ . '/vendor/autoload.php';

use Jira\Api\JiraApiClient;
use Jira\Console\Application;

$application = new Application();
$application->run();
