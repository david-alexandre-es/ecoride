<?php

namespace Src\Entity;


class Utilisateur {
    
    public $utilisateur_id;
    
    public $nom;

    public $prenom;
    public $date_naissance;
    public $photo;

    public $email;

    public $telephone;

    public $adresse;

    public $cp;

    public $ville_id;

    public $ville;

    public $role;

    public $pseudo;

    public $password;

    public $credit;

    public $note_moyenne;

    public $nombre_avis;

    public Voiture $voiture;


public function __construct (array $data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function setVoiture (Voiture $voiture): void 
    {
       $this->voiture = $voiture ;
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function clone(array $data,string $prefix="user_"): self {

        $tab_tmp=[];
        foreach ($data as $key => $value) {
           
            if (strpos($key, $prefix) === 0) {
               
              
                $new_key = substr( $key, strlen($prefix));
                $tab_tmp[$new_key] = $value;
            }
        }

        return new self($tab_tmp);
}



}