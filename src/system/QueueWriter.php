<?php
namespace Src\System;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Src\Domains\TaskDomain;

/*
*  Using RabbitMQ to enable the API to take in a high volume of incoming tasks and be able to insert them into the database without having to wait on database inserts. 
* This is implemented on inserts, but can be implemnted on updates as well when realtime in less essential.
*
*/
class QueueWriter{
    private $channel;
    private $connection;
    private $queueName;
    private $taskDomain;

    function __construct($db) {
        $host = getenv('MQ_HOST');
        $port = getenv('MQ_PORT');
        $user = getenv('MQ_USERNAME');
        $pass = getenv('MQ_PASSWORD');        
        
        $this->connection = new AMQPStreamConnection($host, $port, $user, $pass);
        $this->channel = $this->connection->channel();
        $this->queueName = getenv('MQ_NAME');
        $this->taskDomain = new TaskDomain($db);
    }

    public function insert($input){
        $this->channel->queue_declare($this->queueName, false, false, false, false);
        $rawmsq = serialize($input);

        $msg = new AMQPMessage($rawmsq);
        $this->channel->basic_publish($msg, '', $this->queueName);
    }

    public function consume(){
        $callback = function ($msg) {
            $rawmsg = $msg->body;
            $input = unserialize($rawmsg);
            $this->taskDomain->insert($input);
            echo ' [x] Received ', $rawmsg, "\n";
          };
          
          $this->channel->basic_consume($this->queueName, '', false, true, false, false, $callback);
          
          while ($this->channel->is_consuming()) {
              $this->channel->wait();
          }
    }

    function __destruct(){
        $this->channel->close();
        $this->connection->close();
    }
}

