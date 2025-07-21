<?php

namespace Src\Manager;


use Src\Manager\AvisManager;
use Src\Repository\CovoiturageRepository;
use Src\Repository\UtilisateurRepository;
use src\Entity\Database;
use Src\Entity\Covoiturage;
use Src\Entity\Utilisateur;
use Src\Entity\Voiture;

class CovoiturageManager
{
    private CovoiturageRepository $covoiturageRepository;
    private UtilisateurRepository $utilisateurRepository;
    private Database $db;
    private AvisManager $avisManager;

    public function __construct(Database $db)
    {

        $this->covoiturageRepository = new CovoiturageRepository($db);
        $this->utilisateurRepository = new UtilisateurRepository($db);
        $this->avisManager = new AvisManager($db);
        $this->db = $db;
    }

    public function getCovoiturage(int $covoiturage_id): ?Covoiturage
    {
        try {
            $tab_covoiturage = $this->covoiturageRepository->findById($covoiturage_id);

            if (empty($tab_covoiturage)) {
                return null;
            }

            $obj_covoit = $this->traitementData($tab_covoiturage);
            if (!$obj_covoit) {
                return null;
            }

            // Charger les passagers
            $data_passagers = $this->covoiturageRepository->getResaFromCovoiturageId($covoiturage_id);
            foreach ($data_passagers as $passager) {
                $obj_passager = Utilisateur::clone($passager);
                $obj_covoit->addPassager($obj_passager);
            }

            return $obj_covoit;
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération du covoiturage {$covoiturage_id}: " . $e->getMessage());
            return null;
        }
    }

    public function getListCovoiturage(int $ville_depart_id, int $ville_arrivee_id, string $date_depart, int $nb_place_reste, $user_connecte = false): array
    {
        try {
            $tab_covoit = [
                'covoiturage' => [],
                'filtre' => []
            ];

            $data_bdd = $this->covoiturageRepository->findAllTrajet($ville_depart_id, $ville_arrivee_id, $date_depart, $nb_place_reste, $user_connecte);

            foreach ($data_bdd as $covoit_bdd) {
                $obj_covoit = $this->traitementData($covoit_bdd);
                if (!$obj_covoit) {
                    continue; // Passer au suivant si problème
                }

                // Charger les passagers
                $data_passagers = $this->covoiturageRepository->getResaFromCovoiturageId($covoit_bdd['covoiturage_id']);
                foreach ($data_passagers as $passager) {
                    $obj_passager = Utilisateur::clone($passager);
                    $obj_covoit->addPassager($obj_passager);
                }

                $tab_covoit['covoiturage'][] = $obj_covoit;
            }

            // Générer les filtres
            $tab_covoit['filtre'] = $this->getFiltre($tab_covoit['covoiturage']);

            return $tab_covoit;
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération de la liste des covoiturages: " . $e->getMessage());
            return ['covoiturage' => [], 'filtre' => []];
        }
    }

    public function getCovoiturageByUserId(int $utilisateur_id): array
    {
        try {
            $tab_covoit = [];
            $data_bdd = $this->covoiturageRepository->findAllByUserId($utilisateur_id);

            foreach ($data_bdd as $covoit_bdd) {
                $obj_covoit = $this->traitementData($covoit_bdd);
                if (!$obj_covoit) {
                    continue;
                }

                $data_passagers = $this->covoiturageRepository->getResaFromCovoiturageId($covoit_bdd['covoiturage_id']);
                foreach ($data_passagers as $passager) {
                    $obj_passager = Utilisateur::clone($passager);
                    $obj_covoit->addPassager($obj_passager);
                }

                $tab_covoit[] = $obj_covoit;
            }

            return $tab_covoit;
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération des covoiturages de l'utilisateur {$utilisateur_id}: " . $e->getMessage());
            return [];
        }
    }

    public function getCovoiturageByPassagerId(int $utilisateur_id): array
    {
        try {
            $tab_covoit = [];
            $data_bdd = $this->covoiturageRepository->findAllByPassagerId($utilisateur_id);

            foreach ($data_bdd as $covoit_bdd) {
                $obj_covoit = $this->traitementData($covoit_bdd);
                if (!$obj_covoit) {
                    continue;
                }

                $data_passagers = $this->covoiturageRepository->getResaFromCovoiturageId($covoit_bdd['covoiturage_id']);
                foreach ($data_passagers as $passager) {
                    $obj_passager = Utilisateur::clone($passager);
                    $obj_covoit->addPassager($obj_passager);
                }

                $tab_covoit[] = $obj_covoit;
            }

            return $tab_covoit;
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération des réservations de l'utilisateur {$utilisateur_id}: " . $e->getMessage());
            return [];
        }
    }

    public function startCovoiturage(int $covoiturage_id): bool
    {
        try {
            $data = [
                'statut' => 'ongoing',
                'date_debut' => date('Y-m-d H:i:s'),
            ];
            $this->covoiturageRepository->update($data, $covoiturage_id);
            return true;
        } catch (\Exception $e) {
            error_log("Erreur lors du démarrage du covoiturage {$covoiturage_id}: " . $e->getMessage());
            return false;
        }
    }
    public function closeCovoiturage(int $covoiturage_id): bool
    {
        try {
            // Récupérer les informations du covoiturage
            $covoiturage_data = $this->covoiturageRepository->findById($covoiturage_id);
            if (!$covoiturage_data) {
                throw new \Exception("Covoiturage introuvable");
            }

            // Récupérer les réservations confirmées
            $reservations = $this->covoiturageRepository->getResaFromCovoiturageId($covoiturage_id);

            // Si des réservations existent
            if (!empty($reservations)) {
                $this->db->beginTransaction();

                // Calculer les montants
                $prix_par_personne = (float)$covoiturage_data['prix_personne'];
                $chauffeur_id = $covoiturage_data['user_utilisateur_id'];
                $total_a_crediter = 0;

                // Débiter chaque passager
                foreach ($reservations as $reservation) {
                    $passager_id = $reservation['user_utilisateur_id'];
                    $nb_places = $reservation['nb_place'];
                    $montant_a_debiter = $prix_par_personne * $nb_places;

                    // Vérifier que le passager a suffisamment de crédit
                    $passager_data = $this->utilisateurRepository->findById($passager_id);
                    if (!$passager_data) {
                        throw new \Exception("Passager introuvable (ID: $passager_id)");
                    }

                    $credit_actuel = (float)$passager_data['credit'];

                    // Débiter le passager
                    $nouveau_credit_passager = $credit_actuel - $montant_a_debiter;
                    $this->utilisateurRepository->update([
                        'credit' => $nouveau_credit_passager
                    ], $passager_id);

                    $total_a_crediter += $montant_a_debiter;
                }

                // Créditer le chauffeur
                $chauffeur_data = $this->utilisateurRepository->findById($chauffeur_id);
                if (!$chauffeur_data) {
                    throw new \Exception("Chauffeur introuvable");
                }

                $credit_chauffeur = (float)$chauffeur_data['credit'];

                // Déduire les frais de service, if applicable
                if ($total_a_crediter > 2) {
                    $total_a_crediter = $total_a_crediter - 2;
                }

                $nouveau_credit_chauffeur = $credit_chauffeur + $total_a_crediter;

                $this->utilisateurRepository->update([
                    'credit' => $nouveau_credit_chauffeur
                ], $chauffeur_id);
            }
            // Fermer le covoiturage
            $data = [
                'statut' => 'completed',
                'date_fin' => date('Y-m-d H:i:s'),
            ];
            $this->covoiturageRepository->update($data, $covoiturage_id);

            // Confirmer la transaction
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            error_log("Erreur lors de la fermeture du covoiturage {$covoiturage_id}: " . $e->getMessage());
            throw $e; // Re-lancer l'exception pour que l'appelant puisse la gérer
        }
    }

    public function getFiltre(array $tab_covoit): array
    {
        $tab_filtre = [
            'option' => [
                'titre' => 'Vos options',
                'type' => 'checkbox',
                'tableau' => [
                    'fumeur' => [
                        'nom' => 'Fumeur accepté',
                        'nb' => 0,
                    ],
                    'animal' => [
                        'nom' => 'Animaux acceptés',
                        'nb' => 0,
                    ],
                    'eco' => [
                        'nom' => 'Voyage ECO',
                        'nb' => 0,
                    ],
                    'note' => [
                        'nom' => 'Super chauffeur',
                        'nb' => 0,
                    ],
                ]
            ],
            'prix' => [
                'titre' => 'Prix par personne',
                'type' => 'range',
                'tableau' => [
                    'min' => 99999999999999, // Initialiser à un montant élevé pour trouver le minimum
                    'max' => 0,
                ],
            ],
            'heure_dep' => [
                'titre' => 'Heure de départ',
                'type' => 'checkbox',
                'tableau' => [
                    'am' => [
                        'nom' => 'Le matin',
                        'nb' => 0,
                    ],
                    'pm' => [
                        'nom' => "l'après-midi",
                        'nb' => 0,
                    ],
                ],
            ],
        ];

        foreach ($tab_covoit as $covoit) {
            if (!empty($covoit->fumeur)) {
                $tab_filtre['option']['tableau']['fumeur']['nb']++;
            }
            if (!empty($covoit->animal)) {
                $tab_filtre['option']['tableau']['animal']['nb']++;
            }
            if (!empty($covoit->voiture->energie) && $covoit->voiture->energie === 'electrique') {
                $tab_filtre['option']['tableau']['eco']['nb']++;
            }
            if (!empty($covoit->chauffeur->note_moyenne) && $covoit->chauffeur->note_moyenne >= 4) {
                $tab_filtre['option']['tableau']['note']['nb']++;
            }



            if ((float)$covoit->prix_personne < (float)$tab_filtre['prix']['tableau']['min']) {
                $tab_filtre['prix']['tableau']['min'] = $covoit->prix_personne;
            }
            if ((float)$covoit->prix_personne > (float)$tab_filtre['prix']['tableau']['max']) {
                $tab_filtre['prix']['tableau']['max'] = $covoit->prix_personne;
            }
            if (strtotime($covoit->heure_depart) < strtotime('12:00:00')) {
                $tab_filtre['heure_dep']['tableau']['am']['nb']++;
            } else {
                $tab_filtre['heure_dep']['tableau']['pm']['nb']++;
            }
        }

        return $tab_filtre;
    }

    private function traitementData(array $tab_covoit): ?Covoiturage
    {
        try {
            if (empty($tab_covoit)) {
                return null;
            }

            $obj_covoit = new Covoiturage($tab_covoit);

            // Créer l'objet utilisateur (chauffeur)
            $obj_user = Utilisateur::clone($tab_covoit);
            if ($obj_user) {
                $tabNote = $this->avisManager->getNoteMoyenneEtNombreAvisByUser($obj_user->utilisateur_id);
                if  ($tabNote) {
                    $obj_user->note_moyenne = $tabNote['moyenne'] ?? 0;
                    $obj_user->nombre_avis = $tabNote['nombre'] ?? 0;
                }
            }
            $obj_covoit->setChauffeur($obj_user);

            // Créer l'objet voiture
            $obj_voiture = Voiture::clone($tab_covoit);
            $obj_covoit->setVoiture($obj_voiture);

            return $obj_covoit;
        } catch (\Exception $e) {
            error_log("Erreur lors du traitement des données de covoiturage: " . $e->getMessage());
            return null;
        }
    }
}
