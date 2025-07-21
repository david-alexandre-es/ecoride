<?php

namespace Src\Repository;

use Src\Entity\Database;

class UtilisateurRepository extends BaseRepository
{
    protected $db;    
    protected string $table = 'utilisateur';
    protected string $primaryKey = 'utilisateur_id';

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }


    public function findById(int $utilisateur_id): array
    {
        $req = "SELECT u.* from utilisateur as u WHERE utilisateur_id = :utilisateur_id";
        $param = ['utilisateur_id' => $utilisateur_id];

        $rs =  $this->db->fetchRow($req , $param);
        return $rs ?? [];
    }


    public function findByLogin(string $pseudo):array {

        $req = "SELECT u.* FROM utilisateur as u  WHERE pseudo=:pseudo" ;
        $param = ["pseudo"=>$pseudo];
        $rs = $this->db->fetchRow($req , $param);
        if ($rs) {
            return $rs;
        }
        return [];
    }

    public function findByEmail(string $email): array
    {
        $req = "SELECT u.* FROM utilisateur as u WHERE email = :email";
        $param = ["email" => $email];
        $rs = $this->db->fetchRow($req, $param);
        return $rs ?? [];
    }

    public function findVoitureById(int $utilisateur_id): array
    {
        $req = "SELECT v.*, m.nom as marque FROM voiture as v LEFT JOIN marque as m ON v.marque_id = m.marque_id WHERE v.utilisateur_id = :utilisateur_id";
        $param = ['utilisateur_id' => $utilisateur_id];

        $rs =  $this->db->fetchRow($req , $param);
        return $rs ?? [];
    }

}
