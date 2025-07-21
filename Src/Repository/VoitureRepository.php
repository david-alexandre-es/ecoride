<?php

namespace Src\Repository;

use Src\Entity\Database;
use Src\Entity\Voiture;

class VoitureRepository extends BaseRepository
{
    protected $db;
    protected string $table = 'voiture';
    protected string $primaryKey = 'voiture_id';

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function getMarques(): array
    {
        $req = "SELECT * FROM marque ORDER BY nom";
        $rs = $this->db->fetchAll($req);
        return $rs ?? [];
    }

}
