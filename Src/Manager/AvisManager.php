<?php

namespace Src\Manager;

use Src\Repository\AvisRepository;
use src\Entity\Database;
use Src\Entity\Covoiturage;
use Src\Entity\Avis;
use Src\Entity\Utilisateur;
use Src\Entity\Voiture;

class AvisManager
{
    private AvisRepository $avisRepository;

    public function __construct(Database $db)
    {
        $this->avisRepository = new AvisRepository($db);
    }

    /**
     * Récupère les avis d'un utilisateur avec toutes les informations enrichies
     */
    public function getAvisEnrichisByUser(Utilisateur $utilisateur): array
    {
        // Utilisation du repository pour la requête
        $avis_data = $this->avisRepository->getAvisEnrichisParUtilisateur($utilisateur->utilisateur_id);

        $avis_enrichis = [];
        foreach ($avis_data as $data) {
            $avis_enrichi = $this->traitementDataEnrichi($data);
            if ($avis_enrichi) {
                $avis_enrichis[] = $avis_enrichi;
            }
        }

        return $avis_enrichis;
    }

    /**
     * Récupère les statistiques d'avis d'un utilisateur
     */
    public function getStatistiquesAvis(int $utilisateur_id): ?array
    {
        return $this->avisRepository->getMoyennesPourUtilisateur($utilisateur_id);
    }

    /**
     * Récupère le nombre d'avis valides d'un utilisateur
     */
    public function getNombreAvisValides(int $utilisateur_id): int
    {
        return $this->avisRepository->getNombreAvisValidesPourUtilisateur($utilisateur_id);
    }

    public function getNoteMoyenneEtNombreAvisByUser(int $utilisateur_id): ?array
    {
        $tab = [];
        $tabMoyenne = $this->avisRepository->getMoyennesPourUtilisateur($utilisateur_id);
        if (!empty($tabMoyenne)) {
            $tab['moyenne'] = $tabMoyenne['moyenne_globale'] ?? 0;
        }
        $nombreAvis = $this->avisRepository->getNombreAvisValidesPourUtilisateur($utilisateur_id);
        if (!empty($nombreAvis)) {
            $tab['nombre'] = $nombreAvis;
        }
        if (empty($tab)) {
            return null;
        }
        return $tab;
    }

    /**
     * Récupère les avis d'un utilisateur (méthode simple)
     */
    public function getAvisByUser(Utilisateur $utilisateur): array
    {
        return $this->avisRepository->getAvisByUser($utilisateur);
    }

    private function traitementData(array $tab_avis): bool|Avis
    {
        if (!empty($tab_avis)) {
            $obj_avis = new Avis($tab_avis);

            $obj_user = Utilisateur::clone($tab_avis);
            $obj_avis->setUtilisateur($obj_user);

            $obj_auteur = Utilisateur::clone($tab_avis, 'auteur_');
            $obj_avis->setAuteur($obj_auteur);

            return $obj_avis;
        } else {
            return false;
        }
    }

    /**
     * Traitement enrichi avec les données du covoiturage
     */
    private function traitementDataEnrichi(array $tab_data): bool|array
    {
        if (!empty($tab_data)) {
            $obj_avis = new Avis($tab_data);

            // Utilisateur noté
            $obj_user = Utilisateur::clone($tab_data);
            $obj_avis->setUtilisateur($obj_user);

            // Auteur de l'avis
            $obj_auteur = Utilisateur::clone($tab_data, 'auteur_');
            $obj_avis->setAuteur($obj_auteur);

            // Informations du covoiturage
            $covoiturage_data = [
                'ville_depart' => $tab_data['ville_depart'] ?? '',
                'ville_arrivee' => $tab_data['ville_arrivee'] ?? '',
                'date_depart' => $tab_data['date_depart'] ?? '',
                'heure_depart' => $tab_data['heure_depart'] ?? '',
                'date_arrivee' => $tab_data['date_arrivee'] ?? '',
                'heure_arrivee' => $tab_data['heure_arrivee'] ?? '',
            ];

            return [
                'avis' => $obj_avis,
                'covoiturage' => $covoiturage_data
            ];
        } else {
            return false;
        }
    }
}