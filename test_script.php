<?php
require_once 'vendor/autoload.php';
require_once 'include/config.php';

use Src\Entity\Database;

$db = Database::getInstance();

// Récupérer tous les utilisateurs
$users = $db->fetchAll("SELECT utilisateur_id, password FROM utilisateur");

foreach ($users as $user) {
    // Vérifier si le mot de passe est déjà haché
    if (strlen($user['password']) < 60) { // Les hashs bcrypt font 60 caractères
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);

        $db->update('utilisateur',
            ['password' => $hashedPassword],
            'utilisateur_id = ?',
            [$user['utilisateur_id']]
        );

        echo "Mot de passe mis à jour pour l'utilisateur ID: " . $user['utilisateur_id'] . "\n";
    }
}

echo "Migration terminée!\n";