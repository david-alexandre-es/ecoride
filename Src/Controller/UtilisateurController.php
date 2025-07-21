<?php

namespace Src\Controller;

use Src\Manager\UtilisateurManager;
use Src\Entity\Utilisateur;
use Src\Manager\TwigManager;
use Src\Entity\Database;
use Src\Repository\UtilisateurRepository;
use Src\Helper\Session;


class UtilisateurController
{

    private TwigManager $twigManager;
    private Database $db;

    public function __construct()
    {
        $this->twigManager = new TwigManager();
        $this->db = Database::getInstance();
    }

    public function showFormulaireLogin()
    {

        echo $this->twigManager->render('login.twig');
    }


    public function connexionUtilisateur(string $login, string $password): bool|Utilisateur
    {
        $manager = new UtilisateurManager($this->db);

        $obj_utilisateur = $manager->getUtilisateurFromLogin($login, $password);

        Session::set('utilisateur', $obj_utilisateur);
        return $obj_utilisateur;  // ← Problème : retourne false si pas d'utilisateur
    }
}
