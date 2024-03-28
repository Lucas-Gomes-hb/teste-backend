<?php

namespace Contatoseguro\TesteBackend\Service;

use Contatoseguro\TesteBackend\Config\DB;

class AdminService
{
    private \PDO $pdo;
    public function __construct()
    {
        $this->pdo = DB::connect();
    }

    public function getAll($adminUserId)
    {
        $query = "
            SELECT * 
            FROM admin_user
        ";

        $stm = $this->pdo->prepare($query);

        $stm->execute();

        return $stm;
    }

    public function getOne($id)
    {
        $stm = $this->pdo->prepare("
            SELECT au.* 
            FROM admin_user au
            WHERE id = {$id}
        ");
        $stm->execute();

        return $stm;
    }

    public function getNameById($id)
    {
        $stm = $this->pdo->prepare("
            SELECT name 
            FROM admin_user 
            WHERE
            id = {$id}
        ");
        $stm->execute();

        return $stm;
    }

    public function insertOne($body, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            INSERT INTO admin_user (
                company_id,
                email,
                name,
            ) VALUES (
                {$body['company_id']},
                '{$body['email']}',
                {$body['name']},
            )
        ");

        return $stm->execute();
    }

    public function updateOne($id, $body, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            UPDATE admin_user
            SET company_id = {$body['company_id']},
                email = '{$body['email']}',
                name = {$body['name']},
            WHERE id = {$id}
        ");

        return $stm->execute();
    }

    public function deleteOne($id, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            DELETE FROM admin_user WHERE id = {$id}
        ");

        return $stm->execute();
    }
}
