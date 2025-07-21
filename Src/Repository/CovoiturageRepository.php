<?php

namespace Src\Repository;

use Src\Entity\Database;
use Src\Entity\Utilisateur;

class CovoiturageRepository extends BaseRepository
{
    protected $db;
    protected string $table = 'covoiturage';
    protected string $primaryKey = 'covoiturage_id';

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }


    public function findById(int $covoiturage_id): array
    {
        $req = "SELECT c.*, 
                u.prenom user_prenom,
                u.nom user_nom,u.utilisateur_id AS user_utilisateur_id, 
                u.telephone AS user_telephone,
                u.email AS user_email,
                v.voiture_id AS voiture_voiture_id,
                v.modele AS voiture_modele,
                m.nom AS voiture_marque, 
                v.immatriculation AS voiture_immatriculation,
                v.energie AS voiture_energie,
                v.couleur AS voiture_couleur,

                vd.ville AS ville_depart,va.ville AS ville_arrivee,vd.cp AS cp_depart, va.cp AS cp_arrivee, COALESCE(SUM(r.nb_place), 0) nb_place_reserve, (c.nb_place - COALESCE(SUM(r.nb_place), 0)) nb_place_reste
                FROM covoiturage c
                JOIN ville vd ON vd.ville_id = c.ville_depart_id
                JOIN ville va ON va.ville_id = c.ville_arrivee_id
                JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
                JOIN voiture v ON v.voiture_id = c.voiture_id
                LEFT JOIN marque m ON m.marque_id = v.marque_id
                left JOIN resa r ON r.covoiturage_id = c.covoiturage_id AND r.statut='confirmed'
 WHERE c.covoiturage_id = :covoiturage_id  
                GROUP BY c.covoiturage_id
                ORDER BY c.date_depart, c.heure_depart";
        $param = ['covoiturage_id' => $covoiturage_id];

        $rs =  $this->db->fetchRow($req, $param);
        return $rs ?? [];
    }

    public function findAllByUserId(int $utilisateur_id): array
    {
        $req = "
        SELECT c.*, 
                u.prenom user_prenom,
                u.nom user_nom,u.utilisateur_id AS user_utilisateur_id, 
                u.telephone AS user_telephone,
                u.email AS user_email,
                v.voiture_id AS voiture_voiture_id,
                v.modele AS voiture_modele,
                m.nom AS voiture_marque, 
                v.immatriculation AS voiture_immatriculation,
                v.energie AS voiture_energie,
                v.couleur AS voiture_couleur,

                vd.ville AS ville_depart,va.ville AS ville_arrivee,vd.cp AS cp_depart, va.cp AS cp_arrivee, COALESCE(SUM(r.nb_place), 0) nb_place_reserve, (c.nb_place - COALESCE(SUM(r.nb_place), 0)) nb_place_reste
                FROM covoiturage c
                JOIN ville vd ON vd.ville_id = c.ville_depart_id
                JOIN ville va ON va.ville_id = c.ville_arrivee_id
                JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
                JOIN voiture v ON v.voiture_id = c.voiture_id
                LEFT JOIN marque m ON m.marque_id = v.marque_id
                left JOIN resa r ON r.covoiturage_id = c.covoiturage_id AND r.statut='confirmed'
 WHERE c.utilisateur_id = :utilisateur_id group by c.covoiturage_id
                ORDER BY c.date_depart, c.heure_depart";
        $param = ['utilisateur_id' => $utilisateur_id];

        $rs = $this->db->fetchAll($req, $param);
        return $rs ?? [];
    }
    public function findAllByPassagerId(int $utilisateur_id): array
    {
        $req = "SELECT c.*, 
                u.prenom user_prenom,
                u.nom user_nom,u.utilisateur_id AS user_utilisateur_id, 
                u.telephone AS user_telephone,
                u.email AS user_email,
                v.voiture_id AS voiture_voiture_id,
                v.modele AS voiture_modele,
                m.nom AS voiture_marque, 
                v.immatriculation AS voiture_immatriculation,
                v.energie AS voiture_energie,
                v.couleur AS voiture_couleur,
                vd.ville AS ville_depart,
                va.ville AS ville_arrivee,
                vd.cp AS cp_depart, 
                va.cp AS cp_arrivee, 
                COALESCE(SUM(r.nb_place), 0) nb_place_reserve, 
                (c.nb_place - COALESCE(SUM(r.nb_place), 0)) nb_place_reste,
                a.avis_id note_donnee
                
                
                FROM covoiturage c
                JOIN ville vd ON vd.ville_id = c.ville_depart_id
                JOIN ville va ON va.ville_id = c.ville_arrivee_id
                JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
                JOIN voiture v ON v.voiture_id = c.voiture_id
                LEFT JOIN marque m ON m.marque_id = v.marque_id
                left JOIN resa r ON r.covoiturage_id = c.covoiturage_id AND r.statut='confirmed'
                LEFT JOIN avis a ON a.covoiturage_id = c.covoiturage_id AND a.auteur_id = :auteur_id
        
        WHERE c.covoiturage_id IN (SELECT covoiturage_id FROM resa WHERE utilisateur_id = :utilisateur_id) 
                GROUP BY c.covoiturage_id
                ORDER BY c.date_depart, c.heure_depart";


        $param = ['utilisateur_id' => $utilisateur_id,'auteur_id' => $utilisateur_id];

        $rs = $this->db->fetchAll($req, $param);
        return $rs ?? [];
    }

    public function findAllTrajet(int $ville_depart_id, int $ville_arrivee_id, string $date_depart, int $nb_place_reste,$user_connecte=false): array
    {



        $param = [
            'ville_depart_id' => $ville_depart_id,
            'ville_arrivee_id' => $ville_arrivee_id,
            'date_depart' => $date_depart,
            'nb_place_reste' => $nb_place_reste
        ];

        $req = "SELECT c.*, 
                u.prenom user_prenom,
                u.nom user_nom,u.utilisateur_id AS user_utilisateur_id, 
                u.telephone AS user_telephone,
                u.email AS user_email,
                v.voiture_id AS voiture_voiture_id,
                v.modele AS voiture_modele,
                m.nom AS voiture_marque, 
                v.immatriculation AS voiture_immatriculation,
                v.energie AS voiture_energie,
                v.couleur AS voiture_couleur,

                vd.ville AS ville_depart,va.ville AS ville_arrivee,vd.cp AS cp_depart, va.cp AS cp_arrivee, COALESCE(SUM(r.nb_place), 0) nb_place_reserve, (c.nb_place - COALESCE(SUM(r.nb_place), 0)) nb_place_reste
                FROM covoiturage c
                JOIN ville vd ON vd.ville_id = c.ville_depart_id
                JOIN ville va ON va.ville_id = c.ville_arrivee_id
                JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
                JOIN voiture v ON v.voiture_id = c.voiture_id
                LEFT JOIN marque m ON m.marque_id = v.marque_id
                left JOIN resa r ON r.covoiturage_id = c.covoiturage_id AND r.statut='confirmed'

                WHERE c.ville_depart_id =:ville_depart_id
                AND c.ville_arrivee_id =:ville_arrivee_id
                AND c.date_depart = :date_depart";
        if (is_object($user_connecte)) {
            $req .= " AND c.utilisateur_id != :utilisateur_id";
            $param['utilisateur_id'] = $user_connecte->utilisateur_id;
        }
        $req .= "
                GROUP BY c.covoiturage_id
                HAVING  nb_place_reste >= :nb_place_reste
                ORDER BY c.date_depart, c.heure_depart";
        $rs = $this->db->fetchAll($req, $param);
        return $rs ?? [];
    }

    public function getResaFromCovoiturageId(int $covoiturage_id): array
    {
        $req = "SELECT r.*, 
                u.prenom user_prenom,
                u.nom user_nom,u.utilisateur_id AS user_utilisateur_id, 
                u.telephone AS user_telephone,
                u.email AS user_email 
                FROM resa r 
                JOIN utilisateur u ON r.utilisateur_id = u.utilisateur_id 
                WHERE r.covoiturage_id = :covoiturage_id AND r.statut='confirmed' GROUP BY u.utilisateur_id";
        $param = ['covoiturage_id' => $covoiturage_id];

        $rs = $this->db->fetchAll($req, $param);
        return $rs ?? [];
    }

    public function reserverCovoiturage(int $covoiturage_id, int $utilisateur_id, int $nb_place): bool
    {
        $this->db->beginTransaction();

        try {
            // 1. Vérifier que l'utilisateur n'a pas déjà réservé ce covoiturage
            $existing = $this->db->fetchRow(
                "SELECT resa_id FROM resa WHERE covoiturage_id = :covoiturage_id AND utilisateur_id = :utilisateur_id AND statut = 'confirmed'",
                ['covoiturage_id' => $covoiturage_id, 'utilisateur_id' => $utilisateur_id]
            );

            if ($existing) {
                $this->db->rollBack();
                throw new \Exception("Vous avez déjà réservé ce covoiturage");
            }

            // 2. Vérifier les places disponibles avec un verrou (FOR UPDATE)
            $req = "SELECT 
                        c.nb_place,
                        c.utilisateur_id as chauffeur_id,
                        COALESCE(SUM(r.nb_place), 0) as places_reservees,
                        (c.nb_place - COALESCE(SUM(r.nb_place), 0)) as places_disponibles
                    FROM covoiturage c
                    LEFT JOIN resa r ON r.covoiturage_id = c.covoiturage_id AND r.statut = 'confirmed'
                    WHERE c.covoiturage_id = :covoiturage_id
                    GROUP BY c.covoiturage_id
                    FOR UPDATE";

            $covoiturage = $this->db->fetchRow($req, ['covoiturage_id' => $covoiturage_id]);

            if (!$covoiturage) {
                $this->db->rollBack();
                throw new \Exception("Covoiturage introuvable");
            }

            // 3. Vérifier que ce n'est pas le chauffeur qui réserve
            if ($covoiturage['chauffeur_id'] == $utilisateur_id) {
                $this->db->rollBack();
                throw new \Exception("Vous ne pouvez pas réserver votre propre covoiturage");
            }

            // 4. Vérifier qu'il y a assez de places
            if ($covoiturage['places_disponibles'] < $nb_place) {
                $this->db->rollBack();
                throw new \Exception("Pas assez de places disponibles ({$covoiturage['places_disponibles']} restantes)");
            }

            // 5. Insérer la réservation
            $req = "INSERT INTO resa (covoiturage_id, utilisateur_id, nb_place, statut, date_reservation) 
                    VALUES (:covoiturage_id, :utilisateur_id, :nb_place, 'confirmed', NOW())";

            $this->db->execute($req, [
                'covoiturage_id' => $covoiturage_id,
                'utilisateur_id' => $utilisateur_id,
                'nb_place' => $nb_place
            ]);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e; // Re-lancer l'exception pour que l'appelant puisse la gérer
        }
    }

    public function annulerReservation(int $covoiturage_id, int $utilisateur_id): bool
    {
        try {
            // Vérifier que la réservation existe
            $existing = $this->db->fetchRow(
                "SELECT resa_id FROM resa WHERE covoiturage_id = :covoiturage_id AND utilisateur_id = :utilisateur_id AND statut = 'confirmed'",
                ['covoiturage_id' => $covoiturage_id, 'utilisateur_id' => $utilisateur_id]
            );

            if (!$existing) {
                throw new \Exception("Aucune réservation trouvée pour ce covoiturage");
            }

            // Mettre à jour le statut
            $req = "UPDATE resa SET statut = 'annuled', date_annulation = NOW() 
                    WHERE covoiturage_id = :covoiturage_id AND utilisateur_id = :utilisateur_id AND statut = 'confirmed'";

            $this->db->execute($req, [
                'covoiturage_id' => $covoiturage_id,
                'utilisateur_id' => $utilisateur_id
            ]);

            return true;

        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function getReservationDetails(int $covoiturage_id, int $utilisateur_id): ?array
    {
        $req = "SELECT r.*, c.date_depart, c.heure_depart 
                FROM resa r
                JOIN covoiturage c ON c.covoiturage_id = r.covoiturage_id
                WHERE r.covoiturage_id = :covoiturage_id 
                AND r.utilisateur_id = :utilisateur_id 
                AND r.statut = 'confirmed'";

        return $this->db->fetchRow($req, [
            'covoiturage_id' => $covoiturage_id,
            'utilisateur_id' => $utilisateur_id
        ]);
    }


}
