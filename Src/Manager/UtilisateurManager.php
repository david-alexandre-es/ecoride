<?php

namespace Src\Manager;

use Src\Controller\UtilisateurController;
use Src\Repository\UtilisateurRepository;
use src\Entity\Database;
use Src\Entity\Utilisateur;
use Src\Entity\Voiture;
use Src\Helper\Validator;

class UtilisateurManager
{
    private UtilisateurRepository $utilisateurRepository;
    public function __construct(Database $db)
    {

        $this->utilisateurRepository = new UtilisateurRepository($db);
    }

    public function getUser(int $id_user): ?Utilisateur
    {
        try {
            if ($id_user <= 0) {
                return null;
            }

            $tab_user = $this->utilisateurRepository->findById($id_user);
            return $this->traitementData($tab_user);

        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération de l'utilisateur {$id_user}: " . $e->getMessage());
            return null;
        }
    }

    public function getUtilisateurFromLogin(string $login, string $password): ?Utilisateur
    {
        try {
            // Nettoyer les données d'entrée
            $login = Validator::sanitizeString($login);

            if (empty($login) || empty($password)) {
                return null;
            }

            // Récupérer l'utilisateur par login uniquement
            $tab_user = $this->utilisateurRepository->findByLogin($login);

            if (empty($tab_user)) {
                return null;
            }

            // Vérifier le mot de passe
            if (!password_verify($password, $tab_user['password'])) {
                return null;
            }

            return $this->traitementData($tab_user);

        } catch (\Exception $e) {
            error_log("Erreur lors de l'authentification de l'utilisateur: " . $e->getMessage());
            return null;
        }
    }

    private function traitementData(array $tab_user): ?Utilisateur
    {
        try {
            if (empty($tab_user)) {
                return null;
            }

            $obj_user = new Utilisateur($tab_user);

            // Charger la voiture si elle existe
            $voiture = $this->utilisateurRepository->findVoitureById($obj_user->utilisateur_id);
            if (!empty($voiture)) {
                $obj_user->setVoiture(new Voiture($voiture));
            }

            return $obj_user;

        } catch (\Exception $e) {
            error_log("Erreur lors du traitement des données utilisateur: " . $e->getMessage());
            return null;
        }
    }

    public function newUser(array $data): int
    {
        try {
            // Validation des données
            $errors = Validator::validateUserData($data);

            if (!empty($errors)) {
                throw new \InvalidArgumentException(implode(', ', $errors));
            }

            // Vérifier si l'email existe déjà
            $existingUser = $this->utilisateurRepository->findByEmail($data['email']);
            if (!empty($existingUser)) {
                throw new \InvalidArgumentException("Cette adresse email est déjà utilisée");
            }

            // Vérifier si le pseudo existe déjà
            $existingUser = $this->utilisateurRepository->findByLogin($data['pseudo']);
            if (!empty($existingUser)) {
                throw new \InvalidArgumentException("Ce pseudo est déjà utilisé");
            }

            // Nettoyer les données
            $data['prenom'] = Validator::sanitizeString($data['prenom']);
            $data['nom'] = Validator::sanitizeString($data['nom']);
            $data['pseudo'] = Validator::sanitizeString($data['pseudo']);
            $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

            // Hacher le mot de passe
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            return $this->utilisateurRepository->insert($data);

        } catch (\Exception $e) {
            error_log("Erreur lors de la création de l'utilisateur: " . $e->getMessage());
            throw $e; // Re-lancer pour que l'appelant puisse gérer
        }
    }
    /**
 * Met à jour un utilisateur
 */
public function updateUser(array $data, int $utilisateur_id): bool
{
    try {
        if ($utilisateur_id <= 0) {
            throw new \InvalidArgumentException("ID utilisateur invalide");
        }
        
        // Vérifier que l'utilisateur existe
        $existingUser = $this->utilisateurRepository->findById($utilisateur_id);
        if (empty($existingUser)) {
            throw new \InvalidArgumentException("Utilisateur introuvable");
        }
        
        // Nettoyer les données
        if (isset($data['prenom'])) {
            $data['prenom'] = Validator::sanitizeString($data['prenom']);
        }
        if (isset($data['nom'])) {
            $data['nom'] = Validator::sanitizeString($data['nom']);
        }
        if (isset($data['email'])) {
            $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
            if (!Validator::validateEmail($data['email'])) {
                throw new \InvalidArgumentException("Email invalide");
            }
        }
        
        // Gestion du mot de passe
        if (isset($data['password'])) {
            if (empty($data['password'])) {
                // Si le mot de passe est vide, ne pas le modifier
                unset($data['password']);
            } else {
                // Validation du mot de passe
                if (strlen($data['password']) < 6) {
                    throw new \InvalidArgumentException("Le mot de passe doit contenir au moins 6 caractères");
                }
                
                // Optionnel : Vérification de la confirmation
                if (isset($data['password_confirm'])) {
                    if ($data['password'] !== $data['password_confirm']) {
                        throw new \InvalidArgumentException("Les mots de passe ne correspondent pas");
                    }
                    unset($data['password_confirm']); // Supprimer la confirmation avant la mise à jour
                }
                
                // Hacher le nouveau mot de passe
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
        }
        
        $this->utilisateurRepository->update($data, $utilisateur_id);
        return true;
        
    } catch (\Exception $e) {
        error_log("Erreur lors de la mise à jour de l'utilisateur {$utilisateur_id}: " . $e->getMessage());
        throw $e;
    }
}
}
