<?php
require 'vendor/autoload.php';
use Dotenv\Dotenv;
use Src\System\DatabaseConnector;
use Src\System\QueueWriter;
use Src\Domains\TaskDomain;
use voku\cache\Cache;

$dotenv = new DotEnv(__DIR__);
$dotenv->load();

$dbConnection = (new DatabaseConnector())->getConnection();
$queueWriter = new QueueWriter($dbConnection);
$cache = new Cache();


