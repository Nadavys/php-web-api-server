<?php
namespace Src\Domains;

/*
* class abstracting all database reads and writes for Tasks domain.
*/
class TaskDomain {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findNextAvailable()
    {
        $statement = "
        SELECT 
        id, submitter_id, priority, command, status, processor_id, created, updated
        FROM tasks
        WHERE 1
        AND status = 'NEW'
        ORDER BY priority desc, created asc
        LIMIT 1;
        ";

        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function find($id)
    {
        $statement = "
            SELECT 
                id, submitter_id, priority, command, status, processor_id, created, updated
            FROM
                tasks
            WHERE id = ?;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function insert(Array $input)
    {
        $statement = "
            INSERT INTO tasks 
                (id, submitter_id, command, priority, status, created, updated)
            VALUES
                (:id, :submitter_id, :command, :priority, :status, NOW(), NOW());
        ";
        
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'id' => $input['id'],
                'submitter_id'  => $input['submitter_id'],
                'command'  => $input['command'],
                'priority' => $input['priority'] ?? 1,
                'status' => 'NEW'
      
            ));
            
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function update($id, Array $input)
    {
        $statement = "
        UPDATE tasks
        SET 
            status = :status,
            processor_id = :processor_id,
            totaltime = :totaltime,
            updated = NOW()
        WHERE id = :id AND (status='NEW' OR processor_id=:processor_id);
        ";
        try {
            $statement = $this->db->prepare($statement);
            $executeResult = $statement->execute(array(
                'id' => $id,
                'status' => $input['status'] ?? null,
                'processor_id'  => $input['processor_id'] ?? null,
                'totaltime' => $input['totaltime'] ?? null
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }
}