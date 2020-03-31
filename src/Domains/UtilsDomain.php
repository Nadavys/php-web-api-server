<?php
namespace Src\Domains;

class UtilsDomain {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /*
     * Get  current average processing time
    */
    public function total()
    {
        $statement = "
        SELECT ROUND(AVG(totaltime)) as avgProcessingTime
        FROM tasks
        WHERE 1
        AND status = 'COMPLETED'
        ";

        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

}