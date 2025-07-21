<?php

namespace Src\Helper;

use Src\Helper\Config;

class Validator
{
    /**
     * Valide une adresse email
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valide une date au format Y-m-d
     */
    public static function validateDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Valide un numéro de téléphone français
     */
    public static function validatePhone(string $phone): bool
    {
        $phone = preg_replace('/[\s\-\.]/', '', $phone);
        return preg_match('/^(?:\+33|0)[1-9](?:[0-9]{8})$/', $phone);
    }

    /**
     * Valide un fichier uploadé
     */
    public static function validateFile(array $file): array
    {
        $errors = [];

        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Erreur lors de l'upload du fichier";
            return $errors;
        }

        $maxSize = Config::get('upload.max_size', 5 * 1024 * 1024); // 5MB par défaut
        if ($file['size'] > $maxSize) {
            $errors[] = "Le fichier est trop volumineux (max " . round($maxSize / 1024 / 1024) . "MB)";
        }

        $allowedTypes = Config::get('upload.allowed_types', ['jpg', 'jpeg', 'png', 'gif']);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedTypes)) {
            $errors[] = "Type de fichier non autorisé. Types acceptés : " . implode(', ', $allowedTypes);
        }

        return $errors;
    }

    /**
     * Valide les données d'un covoiturage
     */
    public static function validateCovoiturageData(array $data): array
    {
        $errors = [];

        if (!isset($data['ville_depart_id']) || !is_numeric($data['ville_depart_id'])) {
            $errors[] = "Ville de départ invalide";
        }

        if (!isset($data['ville_arrivee_id']) || !is_numeric($data['ville_arrivee_id'])) {
            $errors[] = "Ville d'arrivée invalide";
        }

        if (!isset($data['date_depart']) || !self::validateDate($data['date_depart'])) {
            $errors[] = "Date de départ invalide";
        } elseif (strtotime($data['date_depart']) < strtotime('today')) {
            $errors[] = "La date de départ ne peut pas être dans le passé";
        }

        $maxPlaces = Config::get('covoiturage.max_places', 8);
        if (!isset($data['nb_place']) || $data['nb_place'] < 1 || $data['nb_place'] > $maxPlaces) {
            $errors[] = "Nombre de places invalide (entre 1 et $maxPlaces)";
        }

        $maxPrice = Config::get('covoiturage.max_price', 100);
        if (!isset($data['prix_personne']) || $data['prix_personne'] < 0 || $data['prix_personne'] > $maxPrice) {
            $errors[] = "Prix par personne invalide (entre 0 et $maxPrice €)";
        }

        return $errors;
    }

    /**
     * Valide les données d'un utilisateur
     */
    public static function validateUserData(array $data): array
    {
        $errors = [];

        if (!isset($data['prenom']) || strlen(trim($data['prenom'])) < 2) {
            $errors[] = "Le prénom doit contenir au moins 2 caractères";
        }

        if (!isset($data['nom']) || strlen(trim($data['nom'])) < 2) {
            $errors[] = "Le nom doit contenir au moins 2 caractères";
        }

        if (!isset($data['email']) || !self::validateEmail($data['email'])) {
            $errors[] = "Adresse email invalide";
        }

        if (!isset($data['telephone']) || !self::validatePhone($data['telephone'])) {
            $errors[] = "Numéro de téléphone invalide";
        }

        $minPseudoLength = Config::get('validation.min_pseudo_length', 3);
        if (!isset($data['pseudo']) || strlen(trim($data['pseudo'])) < $minPseudoLength) {
            $errors[] = "Le pseudo doit contenir au moins $minPseudoLength caractères";
        }

        $minPasswordLength = Config::get('validation.min_password_length', 6);
        if (!isset($data['password']) || strlen($data['password']) < $minPasswordLength) {
            $errors[] = "Le mot de passe doit contenir au moins $minPasswordLength caractères";
        }

        return $errors;
    }

    /**
     * Nettoie et sécurise une chaîne de caractères
     */
    public static function sanitizeString(string $input): string
    {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
}