# Rest API PHP SERVER Example 

## Requirements:
Mysql 5.6, php7+, RabbitMQ

## To install:
Update local .env file settings (MySQL and RabbitMQ credentials)

Create db schema and seed example database data:
`$ php dbseed.php`

Start local RabbitMQ Server
`$ rabbitmq-server`

Start the queue listner
`$ php QueueListener.php`

Verify access to MySql was enabled and Web server enabled.
This example assumes the webserver server was set at localhost:8000

##Working with the Tasks API
Job submitter can send a POST request.
The request is inserted into a message queue to enable the server to take many requests without having to get slowed down by database writes
```curl --location --request POST 'http://localhost:8000/task' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-raw '{
"command":"Say something I'\''m giving up on you",
"priority": 5,
"submitter_id":"cornelius-1"
}'
```
Response ID of the new job. The job hasnt been inserted into the database yet, the job ID was generated by the controller to bypass he need to await mysql response.
```{
    "id": "5e82d6c63d9f8"
}
```

Job submitters are able to check the status of a job using an id that was returned to them 
```curl 'http://localhost:8000/task/5e7f09df478d0'
```

 Response
```{"id":"5e7f09df478d0","submitter_id":"123","priority":"5","command":"Say something I'm giving up on you","status":"NEW","processor_id":null,"created":"2020-03-28 01:25:03","updated":"2020-03-28 01:25:03"}
```

GET to find the next available job with the highest priority:
```curl 'http://localhost:8000/task/'
```

response 
```{"id":"5e7f09df478d0","submitter_id":"123","priority":"5","command":"Say something I'm giving up on you","status":"NEW","processor_id":null,"created":"2020-03-28 01:25:03","updated":"2020-03-28 01:25:03"}
```

When a processor is ready to start processing a new task, it must send a PUT request to change the task status an await a 201 response.
If the response is a 401, then the processor cannot touch that task. it might have been picked up by another processor. 
A processor can only update a task when the processor_id field is null, or matching the last processor id. That way two processors cannot process the same job, nor alter the data of a task in or after process.
```curl --location --request PUT 'http://localhost:8000/task/5e7f09bea9aa1' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-raw '{
"status":"IN-PROGRESS",
"processor_id":"123"
}'
```

Get current average processing time:
```curl  http://localhost:8000/Utils/
```
Example response:
```{"avgProcessingTime":"150"}
```
