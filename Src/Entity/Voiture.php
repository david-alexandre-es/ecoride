<?php

namespace Src\Entity;


class Voiture {
    
    public $voiture_id;
    
    public $modele;

    public $immatriculation;
    public $energie;
    public $couleur;

    public $date_premiere_immatriculation;

    public $utilisateur_id;

    public $marque_id;

    public $marque;


public function __construct (array $data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public static function clone(array $data,string $prefix="voiture_"): self {

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