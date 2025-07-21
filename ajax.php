<?php

use Src\Entity\Voiture;


if (empty($_POST)) {
    die;
}


include 'include/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Src\Controller\SiteController;
use Src\Controller\UtilisateurController;
use Src\Entity\Covoiturage;
use Src\Repository\CommunRepository;
use Src\Repository\UtilisateurRepository;
use Src\Repository\AvisRepository;
use Src\Repository\VoitureRepository;
use Src\Repository\CovoiturageRepository;
use Src\Manager\TwigManager;
use Src\Manager\CovoiturageManager;
use Src\Manager\UtilisateurManager;
use Src\Entity\Database;
use Src\Entity\Utilisateur;
use Src\Helper\Helper;
use Src\Helper\Session;
use Src\Helper\ApiResponse;
use Src\Helper\Validator;

switch ($_POST['controller']) {
    case 'site':
        $controller = new SiteController();
        switch ($_POST['method']) {
            case 'showListeCovoiturage':

                header('location: /ecoride/' . $_POST['ville_depart_id'] . '/' . $_POST['ville_arrivee_id'] . '/' . $_POST['date_depart'] . '/' . $_POST['nb_place'] . '/liste');
                break;
        }
        break;
    case 'user':
        try {
            $controller = new UtilisateurController();
            $manager = new UtilisateurManager(Database::getInstance());
            switch ($_POST['method']) {
                case 'showUserFromLogin':
                    if (empty($_POST['login']) || empty($_POST['password'])) {
                        ApiResponse::error('Login et mot de passe requis');
                    }

                    $manager = new UtilisateurManager(Database::getInstance());
                    $user = $manager->getUtilisateurFromLogin($_POST['login'], $_POST['password']);

                    if (!$user) {
                        ApiResponse::error('Identifiants incorrects');
                    }

                    // Mettre l'utilisateur en session
                    Session::set('utilisateur', $user);

                    // Gestion des redirections
                    if (isset($_POST['redirect_to'])) {
                        if ($_POST['redirect_to'] === 'covoiturage_reserver') {
                            $pending_reservation = Session::get('pending_reservation');
                            if ($pending_reservation) {
                                $_POST['controller'] = 'covoiturage';
                                $_POST['method'] = 'reserver';
                                $_POST['covoiturage_id'] = $pending_reservation['covoiturage_id'];
                                $_POST['nb_place'] = $pending_reservation['nb_place'];

                                Session::remove('pending_reservation');
                                goto covoiturage_case;
                            }
                        } else {

                            header('location: /ecoride/' . $_POST['redirect_to']);
                            exit;
                        }
                    }
                    header('location: /ecoride/compte');
                    exit;

                    break;
                case 'logout':
                    // Déconnexion de l'utilisateur
                    Session::remove('utilisateur');
                    ApiResponse::success(null, 'Déconnexion réussie');
                    break;
                case 'insertUser':
                    $utilisateur_id = $manager->newUser($_POST['data']);
                    $obj_utilisateur = $manager->getUser($utilisateur_id);
                    Session::set('utilisateur', $obj_utilisateur);

                    // Gestion des redirections
                    if (isset($_POST['redirect_to']) && $_POST['redirect_to'] === 'covoiturage_reserver') {
                        // Cas spécial : redirection vers réservation
                        $pending_reservation = Session::get('pending_reservation');
                        if ($pending_reservation) {
                            // Rediriger vers la réservation
                            $_POST['controller'] = 'covoiturage';
                            $_POST['method'] = 'reserver';
                            $_POST['covoiturage_id'] = $pending_reservation['covoiturage_id'];
                            $_POST['nb_place'] = $pending_reservation['nb_place'];

                            // Nettoyer la session
                            Session::remove('pending_reservation');

                            // Continuer vers le case covoiturage
                            goto covoiturage_case;
                        }
                    }

                    // Déterminer l'URL de redirection
                    $redirect_url = '/ecoride/compte'; // par défaut
                    if (isset($_POST['redirect_to']) && $_POST['redirect_to'] !== 'covoiturage_reserver') {
                        $redirect_url = '/ecoride/' . $_POST['redirect_to'];
                    }


                    header('location: /ecoride/' . $redirect_url);
                    exit;
                    break;

                case 'update':
                    $utilisateur_connecte = Session::get('utilisateur');
                    if (!$utilisateur_connecte) {
                        ApiResponse::error('Connexion requise', null, 401);
                    }

                    $manager->updateUser($_POST['data'], $utilisateur_connecte->utilisateur_id);

                    // Recharger l'utilisateur en session
                    $updated_user = $manager->getUser($utilisateur_connecte->utilisateur_id);
                    if ($updated_user) {
                        Session::set('utilisateur', $updated_user);
                    }

                    header('location: /ecoride/compte');
                    break;
            }
        } catch (\InvalidArgumentException $e) {
            ApiResponse::error($e->getMessage());
        } catch (\Exception $e) {
            error_log("Erreur utilisateur: " . $e->getMessage());
            ApiResponse::error('Une erreur est survenue', null, 500);
        }
        break;
    case 'voiture':
        switch ($_POST['method']) {
            case 'update':
                $repo = new VoitureRepository(Database::getInstance());
                $repo->update($_POST['data'], $_POST['voiture_id']);
                $manager_user = new UtilisateurManager(Database::getInstance());
                $obj_utilisateur = $manager_user->getUser($_POST['data']['utilisateur_id']);
                Session::set('utilisateur', $obj_utilisateur);
                header('location: /ecoride/compte');
                break;
            case 'insert':
                $repo = new VoitureRepository(Database::getInstance());
                $repo->insert($_POST['data']);
                $manager_user = new UtilisateurManager(Database::getInstance());
                $obj_utilisateur = $manager_user->getUser($_POST['data']['utilisateur_id']);
                Session::set('utilisateur', $obj_utilisateur);
                header('location: /ecoride/compte');
                break;
        }
        break;
        covoiturage_case:
    case 'covoiturage':
        $manager = new CovoiturageManager(Database::getInstance());

        try {
            switch ($_POST['method']) {
                case 'reserver':
                    $covoiturage_id = $_POST['covoiturage_id'];
                    $utilisateur_connecte = Session::get('utilisateur');

                    if (!$utilisateur_connecte) {
                        $twigManager = new TwigManager();
                        $data = [
                            'controller' => 'covoiturage',
                            'method' => 'reserver',
                            'covoiturage_id' => $covoiturage_id,
                            'nb_place' => $_POST['nb_place'],
                            'redirect_to' => 'covoiturage_reserver'
                        ];

                        Session::set('pending_reservation', [
                            'covoiturage_id' => $covoiturage_id,
                            'nb_place' => $_POST['nb_place']
                        ]);

                        $html = $twigManager->render('compte/formulaire_connexion.twig', $data);
                        ApiResponse::html($html, false, 'Connexion requise');
                    }

                    $utilisateur_id = $utilisateur_connecte->utilisateur_id;
                    $repo = new CovoiturageRepository(Database::getInstance());
                    $nb_place = (int)$_POST['nb_place'];

                    // Validation
                    if ($nb_place < 1 || $nb_place > 8) {
                        ApiResponse::error('Nombre de places invalide');
                    }

                    $repo->reserverCovoiturage($covoiturage_id, $utilisateur_id, $nb_place);

                    // Si on arrive ici après connexion/inscription, rediriger vers la page
                    if (isset($_POST['redirect_to']) && $_POST['redirect_to'] === 'covoiturage_reserver') {
                        header('location: ' . $_SERVER['HTTP_REFERER']);
                    } else {
                        ApiResponse::success(['covoiturage_id' => $covoiturage_id], 'Réservation confirmée !');
                    }
                    break;

                case 'annuler':
                    $covoiturage_id = $_POST['covoiturage_id'];
                    $utilisateur_connecte = Session::get('utilisateur');

                    if (!$utilisateur_connecte) {
                        ApiResponse::error('Connexion requise', null, 401);
                    }

                    $repo = new CovoiturageRepository(Database::getInstance());
                    $repo->annulerReservation($covoiturage_id, $utilisateur_connecte->utilisateur_id);

                    ApiResponse::success(['covoiturage_id' => $covoiturage_id], 'Réservation annulée');
                    break;

                case 'insert':
                    $utilisateur_connecte = Session::get('utilisateur');

                    if (!$utilisateur_connecte) {
                        ApiResponse::error('Connexion requise', null, 401);
                    }

                    // Validation des données
                    $errors = Validator::validateCovoiturageData($_POST['data']);
                    if (!empty($errors)) {
                        ApiResponse::error('Données invalides', $errors);
                    }

                    $repo = new CovoiturageRepository(Database::getInstance());
                    $_POST['data']['heure_depart'] = Helper::transformTimeToSql($_POST['data']['heure_depart']);
                    $_POST['data']['heure_arrivee'] = Covoiturage::calculerHeureArrivee($_POST['data']['heure_depart'], $_POST['data']['duree']);
                    $_POST['data']['date_arrivee'] = Covoiturage::calculerDateArrivee($_POST['data']['date_depart'], $_POST['data']['heure_depart'], $_POST['data']['duree']);

                    $covoiturage_id = $repo->insert($_POST['data']);


                    header('location: /ecoride/compte');
                    exit;
                    break;
                case 'start':
                    $covoiturage_id = $_POST['covoiturage_id'];
                    $manager->startCovoiturage($covoiturage_id);
                    ApiResponse::success(['covoiturage_id' => $covoiturage_id], 'Covoiturage démarré');

                    break;
                case 'close':
                    try {
                        $covoiturage_id = $_POST['covoiturage_id'];
                        $utilisateur_connecte = Session::get('utilisateur');

                        if (!$utilisateur_connecte) {
                            ApiResponse::error('Connexion requise', null, 401);
                        }

                        // Vérifier que l'utilisateur est bien le chauffeur
                        $covoiturage_data = $manager->getCovoiturage($covoiturage_id);
                        if (!$covoiturage_data || $covoiturage_data->chauffeur->utilisateur_id !== $utilisateur_connecte->utilisateur_id) {
                            ApiResponse::error('Vous n\'êtes pas autorisé à fermer ce covoiturage');
                        }

                        $manager->closeCovoiturage($covoiturage_id);
                        ApiResponse::success(['covoiturage_id' => $covoiturage_id], 'Covoiturage terminé et paiements effectués');
                    } catch (\Exception $e) {
                        error_log("Erreur fermeture covoiturage: " . $e->getMessage());
                        ApiResponse::error($e->getMessage());
                    }
                    break;
            }
        } catch (\Exception $e) {
            error_log("Erreur covoiturage: " . $e->getMessage());
            ApiResponse::error($e->getMessage());
        }
        break;
    case 'avis':
        switch ($_POST['method']) {
            case 'getFormAvis':
                $covoiturage_id = $_POST['covoiturage_id'];
                $utilisateur_connecte = Session::get('utilisateur');
                $manager = new CovoiturageManager(Database::getInstance());
                $obj_covoiturage = $manager->getCovoiturage($covoiturage_id);
                $twigManager = new TwigManager();
                $data = [
                    'covoiturage' => $obj_covoiturage,
                    'user_connecte' => $utilisateur_connecte,
                ];

                $html = $twigManager->render('compte/avis.twig', $data);
                $response = ['html' => $html];
                header('Content-Type: application/json');
                echo json_encode($response);
                break;
            case 'insert':
                $repo = new AvisRepository(Database::getInstance());
                $avis_id = $repo->insert($_POST['data']);
                break;
        }
        break;
    case 'option':
        switch ($_POST['method']) {
            case 'getVille':
                $repo = new CommunRepository(Database::getInstance());
                $tab_ville = $repo->getSearchlVille($_POST['text']);
                header('Content-Type: application/json');
                echo json_encode($tab_ville);
                break;
            case 'calculerDuree':
                // Récupérer les coordonnées des villes
                $repo = new CommunRepository(Database::getInstance());
                $ville_depart = $repo->getVilleById($_POST['ville_depart_id']);
                $ville_arrivee = $repo->getVilleById($_POST['ville_arrivee_id']);

                if (!$ville_depart || !$ville_arrivee) {
                    $response = ['success' => false, 'error' => 'Ville non trouvée'];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    break;
                }

                // Utiliser la méthode statique de l'entité Covoiturage
                $duree = Covoiturage::calculerDureeEntreVilles($ville_depart, $ville_arrivee);

                if ($duree !== false) {
                    $response = ['success' => true, 'duree' => $duree];
                } else {
                    $response = ['success' => false, 'error' => 'Impossible de calculer la durée'];
                }

                header('Content-Type: application/json');
                echo json_encode($response);
                break;
        }
        break;
}
