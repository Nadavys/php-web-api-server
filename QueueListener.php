<?php
require 'bootstrap.php';
/**
 * This job needs to run to insert the tasks from the Message Broker into the database. This job doenst have to be on the same machine as the web server, thus enabling proper horizontal scaling. 
 */
$queueWriter->consume();