<?php

namespace Src\Repository;

use Src\Entity\Database;

abstract class BaseRepository
{
    protected $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function insert(array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        $columns = [];
        $placeholders = [];
        $params = [];

        foreach ($data as $key => $value) {
            $columns[] = $key;
            $placeholders[] = ":" . $key;
            $params[$key] = $value;
        }

        $columnsStr = implode(', ', $columns);
        $placeholdersStr = implode(', ', $placeholders);

        $req = "INSERT INTO ".$this->table." ($columnsStr) VALUES ($placeholdersStr)";

        return $this->db->insert($req, $params);
    }

    public function update(array $data, int $id)
    {
        $param = [$this->primaryKey => $id];
        $req = "UPDATE ".$this->table." SET ";

        foreach ($data as $key => $value) {
            $req .= "$key = :$key, ";
            $param[$key] = $value;
        }

        $req = rtrim($req, ", ");
        $req .= " WHERE ".$this->primaryKey." = :{$this->primaryKey}";

        $this->db->execute($req, $param);
    }

    public function delete(int $id)
    {
        $req = "DELETE FROM ".$this->table." WHERE ".$this->primaryKey." = :".$this->primaryKey;
        $param = [$this->primaryKey => $id];

        $this->db->execute($req, $param);
    }
}
