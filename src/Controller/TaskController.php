<?php
namespace Src\Controller;

use Src\Domains\TaskDomain;

class TaskController {
    private $db;
    private $requestMethod;
    private $id;
    private $queueWriter;

    private $taskDomain;

    public function __construct($db, $requestMethod, $id, $queueWriter, $cache)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->id = $id;
        $this->queueWriter = $queueWriter;
        $this->cache = $cache;

        $this->taskDomain = new TaskDomain($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->id) {
                    $response = $this->getTask($this->id);
                } else {
                    $response = $this->getNextAvailable();
                };
                break;
            case 'POST':
                $response = $this->create();
                break;
            case 'PUT':
                $response = $this->update($this->id);
                break;
            case 'DELETE':
                $response = $this->delete($this->id);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getTask($id)
    {
        $result = $this->taskDomain->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }


    private function getNextAvailable()
    {
        $result = $this->taskDomain->findNextAvailable();
        if (! $result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function create()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        //todo: add schema validation
        $input['id'] = $id = uniqid();
        //to enable the server to accept a high volume of input, the data is put in a message queue, and it will be inserted into the database by another process or server.
        $this->queueWriter->insert($input);

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(["id"=>$id]);
        return $response;
    }

    /*
     * A processor can only upate a task which hasnlt already been clained by another processor. thus enabling two processors from working on the same task 
    */
    private function update($id)
    {   
        $response['status_code_header'] = 'HTTP/1.1 201';
        $result = $this->taskDomain->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $response['body'] = json_encode($result);

        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateTask($input)) {
            return $this->unprocessableEntityResponse();
        }
        $updateResult = $this->taskDomain->update($id, $input);
        if($updateResult){
            $response['status_code_header'] = 'HTTP/1.1 202 OK';
        }else{
            $response['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
        }
        $response['body'] = null;
        return $response;
    }

    /* input data must be validated before being put in a queue. Database insert will only happen later */
    private function validateTask($input)
    {
        //todo: implement before moving to production
        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }

    private function delete($id){
        //todo: implement before going to production
        $response['status_code_header'] = 'HTTP/1.1 202 Accepted';
        $response['body'] = null;
        return $response;
    }

}
