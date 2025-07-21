<?php

namespace Src\Controller;

use Src\Manager\CovoiturageManager;
use Src\Manager\UtilisateurManager;
use Src\Manager\AvisManager;
use Src\Entity\Covoiturage;
use Src\Entity\Utilisateur;
use Src\Manager\TwigManager;
use Src\Entity\Database;
use Src\Repository\CommunRepository;
use Src\Repository\VoitureRepository;
use Src\Repository\avisRepository;
use Src\Helper\Session;


class SiteController
{

    private TwigManager $twigManager;
    private Database $db;
    private $user_connecte = false;

    public function __construct()
    {
        $this->twigManager = new TwigManager();
        $this->db = Database::getInstance();

        $user_connecte = Session::get('utilisateur');
        if ($user_connecte) {
            $this->user_connecte = $user_connecte;
        }
    }

    private function getDefaultData(): array
    {
        return [
            'user_connecte' => $this->user_connecte,
            'tab_js' => [],
            'dossier' => 'home',
            'tab_ville' => [],
            'liste_covoit' => [],
            'liste_marque' => [],
            'redirect_to' => null
        ];
    }

    public function showHomePage()
    {

         $data = array_merge($this->getDefaultData(), [
            'dossier' => 'home',
            'tab_ville' => (new CommunRepository($this->db))->getAllVille(),
        ]);
        echo $this->twigManager->render('index.twig', $data);
    }


    public function showPageListe(int $ville_depart_id, int $ville_arrivee_id, string $date_depart, int $nb_place)
    {


        $manager = new CovoiturageManager($this->db);
        $liste_covoit = $manager->getListCovoiturage($ville_depart_id, $ville_arrivee_id, $date_depart, $nb_place, $this->user_connecte);


        $data = array_merge($this->getDefaultData(), [
            'dossier' => 'liste',
            'liste_covoit' => $liste_covoit['covoiturage'],
            'tab_filtre' => $liste_covoit['filtre'],
            'tab_js' => ['liste'],
            'nb_place' => $nb_place,
        ]);
        echo $this->twigManager->render('index.twig', $data);
    }

    public function showFicheProduit(int $covoiturage_id)
    {
         
         $data = array_merge($this->getDefaultData(), [
            'dossier' => 'fiche',
        ]);
        echo $this->twigManager->render('index.twig', $data);
    }

    public function showPagePublier()
    {

         // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
        $data = array_merge($this->getDefaultData(), [
            'dossier' => 'publier',
            'tab_js' => ['publier'],
            'redirect_to' => 'publier-trajet',
         ]);
        echo $this->twigManager->render('index.twig', $data);
    }

    public function showPageCompte()
    {
        $liste_covoit = $liste_marque= [];
        if (is_object($this->user_connecte)) {
            $manager = new CovoiturageManager($this->db);
            $liste_covoit['chauffeur'] = $manager->getCovoiturageByUserId($this->user_connecte->utilisateur_id);
            $liste_covoit['passager'] = $manager->getCovoiturageByPassagerId($this->user_connecte->utilisateur_id);

            // Utilisation de l'AvisManager
            $avisManager = new AvisManager($this->db);
            $liste_covoit['avis'] = $avisManager->getAvisEnrichisByUser($this->user_connecte);
            $liste_covoit['stats_avis'] = $avisManager->getStatistiquesAvis($this->user_connecte->utilisateur_id);
            $liste_covoit['nb_avis'] = $avisManager->getNombreAvisValides($this->user_connecte->utilisateur_id);
        
            // Récupération des marques de voiture
            $voitureRepo = new VoitureRepository($this->db);
            $liste_marque = $voitureRepo->getMarques();

        }

        $data = array_merge($this->getDefaultData(), [
            'dossier' => 'compte',
            'liste_covoit' => $liste_covoit,
            'liste_marque' => $liste_marque,
            'tab_js' => ['compte', 'avis'],
        ]);
        echo $this->twigManager->render('index.twig', $data);
    }
    public function showPageContact()
    {
        $data = [
            'dossier' => 'contact',
            'user_connecte' => $this->user_connecte,
        ];
        echo $this->twigManager->render('index.twig', $data);
    }
}
