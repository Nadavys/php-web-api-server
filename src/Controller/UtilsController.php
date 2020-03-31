<?php
namespace Src\Controller;
use Src\Domains\UtilsDomain;

class UtilsController {
    private $db;
    private $requestMethod;
    private $id;
    private $cache;

    private $utilsDomain;

    public function __construct($db, $requestMethod, $id, $QueueWriter, $cache)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->id = $id;
        $this->cache = $cache;

        $this->utilsDomain = new UtilsDomain($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                $response = $this->getTotals();
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

    private function getTotals()
    {
        // Use caching to enable faster fetch data when realtime information is not essential.
        // Cache needs to be setup to work, but this exemplifies how a cache would be integrated int the code
        if ($this->cache->getCacheIsReady() === true && $this->cache->existsItem('totals')) {
            $result = $this->cache->getItem('totals');
          } else {
            $result = $this->utilsDomain->total();
            $this->cache->setItem('totals', $result, 3600);
            
          }
        
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }

}
