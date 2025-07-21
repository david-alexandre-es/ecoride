<?php

namespace Src\Repository;

use Src\Entity\Database;
use Src\Entity\Utilisateur;
use Src\Entity\Avis;

class AvisRepository extends BaseRepository
{
    protected $db;
    protected string $table = 'avis';
    protected string $primaryKey = 'avis_id';

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function getAvisById(int $avis_id): ?array
    {
        $req = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :avis_id";
        return $this->db->fetchRow($req, ['avis_id' => $avis_id]);
    }

    public function getAvisEnrichisParUtilisateur(int $utilisateur_id): array
    {
        $req = "SELECT 
                a.*,
                u.utilisateur_id AS user_utilisateur_id,
                u.nom user_nom, u.prenom user_prenom, u.email user_email,
                auteur.utilisateur_id AS auteur_utilisateur_id,
                auteur.nom as auteur_nom, auteur.prenom as auteur_prenom, auteur.email as auteur_email,
                c.date_depart, c.heure_depart, c.date_arrivee, c.heure_arrivee,
                v_depart.ville as ville_depart,
                v_arrivee.ville as ville_arrivee
            FROM {$this->table} a
            LEFT JOIN utilisateur u ON a.utilisateur_id = u.utilisateur_id
            LEFT JOIN utilisateur auteur ON a.auteur_id = auteur.utilisateur_id
            LEFT JOIN covoiturage c ON a.covoiturage_id = c.covoiturage_id
            LEFT JOIN ville v_depart ON c.ville_depart_id = v_depart.ville_id
            LEFT JOIN ville v_arrivee ON c.ville_arrivee_id = v_arrivee.ville_id
            WHERE a.utilisateur_id = :utilisateur_id
            AND a.statut = 'valide'
            ORDER BY a.date_creation DESC";

        return $this->db->fetchAll($req, ['utilisateur_id' => $utilisateur_id]);
    }

    public function getAvisByUser(Utilisateur $utilisateur): array
    {
        $req = "SELECT * FROM {$this->table} WHERE utilisateur_id = :utilisateur_id";
        return $this->db->fetchAll($req, ['utilisateur_id' => $utilisateur->utilisateur_id]);
    }

    public function getAvisValidesByUser(int $utilisateur_id): array
    {
        $req = "SELECT * FROM {$this->table} 
                WHERE utilisateur_id = :utilisateur_id 
                AND statut = 'valide'
                ORDER BY date_creation DESC";
        return $this->db->fetchAll($req, ['utilisateur_id' => $utilisateur_id]);
    }

    public function getMoyennesPourUtilisateur(int $utilisateur_id): ?array
    {
        $req = "
            SELECT 
                ROUND(AVG(note_conduite), 2) AS moyenne_conduite,
                ROUND(AVG(note_ponctualite), 2) AS moyenne_ponctualite,
                ROUND(AVG(note_convivialite), 2) AS moyenne_convivialite,
                ROUND(AVG(note_global), 2) AS moyenne_globale
            FROM {$this->table}
            WHERE utilisateur_id = :utilisateur_id AND statut = 'valide'
        ";

        return $this->db->fetchRow($req, ['utilisateur_id' => $utilisateur_id]);
    }

    public function getNombreAvisValidesPourUtilisateur(int $utilisateur_id): int
    {
        $req = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE utilisateur_id = :utilisateur_id AND statut = 'valide'";
        $result = $this->db->fetchRow($req, ['utilisateur_id' => $utilisateur_id]);
        return $result ? (int)$result['total'] : 0;
    }

    public function getDernierAvisPourUtilisateur(int $utilisateur_id): ?array
    {
        $req = "SELECT * FROM {$this->table} 
                WHERE utilisateur_id = :utilisateur_id AND statut = 'valide' 
                ORDER BY date_creation DESC LIMIT 1";
        return $this->db->fetchRow($req, ['utilisateur_id' => $utilisateur_id]);
    }
}
