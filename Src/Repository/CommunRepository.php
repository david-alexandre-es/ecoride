<?php

namespace Src\Repository;

use Src\Entity\Database;

class CommunRepository
{
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAllVille(): array
    {
        $req = "SELECT * FROM ville ORDER BY ville ASC";
        $rs = $this->db->fetchAll($req);
        return $rs ?? [];
    }

    public function getSearchlVille(string $text): array
    {
        $req = "SELECT * FROM ville WHERE ville LIKE :text ORDER BY ville ASC";

        $text = trim($text);
        // Remplace tous les espaces par un wildcard SQL : '%'
        $search = str_replace(' ', '%', $text) . '%';
        $param = ['text' => $search];
        $rs = $this->db->fetchAll($req, $param);
        return $rs ?? [];
    }
    public function getVilleById(int $id): array
    {
        $req = "SELECT * FROM ville WHERE ville_id = :id";
        $param = ['id' => $id];
        $rs = $this->db->fetchRow($req, $param);
        return $rs ?? [];
    }
}
