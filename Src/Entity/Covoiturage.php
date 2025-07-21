<?php

namespace Src\Entity;

use Src\Helper\Helper;
use Src\Helper\Config;
use Src\Entity\Voiture;
use Src\Entity\Utilisateur;
use Src\Entity\Database;

use \DateTime;
use \DateInterval;



class Covoiturage
{

    public $covoiturage_id;

    public $date_depart;

    public $heure_depart;

    public $depart_matin;

    public $heure_depart_formated;

    public $ville_depart_id;

    public $ville_depart;

    public $cp_depart;

    public $date_arrivee;

    public $heure_arrivee;

    public $heure_arrivee_formated;

    public $ville_arrivee_id;

    public $ville_arrivee;

    public $duree;

    public $cp_arrivee;

    public $statut;

    public $utilisateur_id;

    public $fumeur;
    public $animal;

    public $nb_place;
    public $nb_place_reste;
    public $nb_place_reserve;

    public $date_fin;
    public $date_debut;

    public $prix_personne;

    public $voiture_id;

    public $note_donnee;

    public Utilisateur $chauffeur;

    public Voiture $voiture;

    public $passagers = [];


    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
                if ($key === 'heure_depart') {
                    $this->heure_depart_formated = $this->getHeureDepartFormated();
                    $this->getDepartMatin();
                } elseif ($key === 'heure_arrivee') {
                    $this->heure_arrivee_formated = $this->getHeureArriveeFormated();
                }
            }
        }
    }

    private function getHeureDepartFormated(): string
    {
        return Helper::transformToTime($this->heure_depart);
    }

    private function getHeureArriveeFormated(): string
    {
        return Helper::transformToTime($this->heure_arrivee);
    }

    private function getDepartMatin()
    {
        if (strtotime($this->heure_depart) < strtotime('12:00:00')) {
            $this->depart_matin = true;
        } else {
            $this->depart_matin = false;
        }
    }

    public static function clone(array $data, string $prefix = "voiture_"): self
    {

        $tab_tmp = [];
        foreach ($data as $key => $value) {

            if (strpos($key, $prefix) === 0) {
                $new_key = substr($key, strlen($prefix));
                $tab_tmp[$new_key] = $value;
            }
        }

        return new self($tab_tmp);
    }

    public function setChauffeur(Utilisateur $chauffeur): void
    {
        $this->chauffeur = $chauffeur;
    }

    public function setVoiture(Voiture $voiture): void
    {
        $this->voiture = $voiture;
    }

    public function addPassager(Utilisateur $utilisateur): void
    {
        $this->passagers[] = $utilisateur;
    }

    public static function calculerDureeEntreVilles(array $ville1, array $ville2): array|false
    {
        if (empty($ville1) || empty($ville2)) {
            return false;
        }

        if (empty($ville1['lat']) && empty($ville1['lon'])) {
            $coords1 = self::getCoordsFromNom($ville1['ville'], $ville1['cp'], $ville1['ville_id']);
        } else {
            $coords1 = ['lat' => $ville1['lat'], 'lon' => $ville1['lon']];
        }

        if (empty($ville2['lat']) && empty($ville2['lon'])) {
            $coords2 = self::getCoordsFromNom($ville2['ville'], $ville2['cp'], $ville2['ville_id']);
        } else {
            $coords2 = ['lat' => $ville2['lat'], 'lon' => $ville2['lon']];
        }

        if (!$coords1 || !$coords2) return false;

        // Utiliser la configuration pour l'URL OSRM
        $baseUrl = Config::get('api.osrm_url', 'http://router.project-osrm.org/route/v1/driving/');
        $url = $baseUrl . "{$coords1['lon']},{$coords1['lat']};{$coords2['lon']},{$coords2['lat']}?overview=false";

        // Timeout configurable
        $timeout = Config::get('api.timeout', 5);
        $context = stream_context_create([
            'http' => [
                'timeout' => $timeout,
                'user_agent' => Config::get('app.name', 'EcoRide') . '/1.0'
            ]
        ]);

        $response = file_get_contents($url, false, $context);
        if (!$response) {
            error_log("Erreur lors de l'appel OSRM: timeout ou erreur réseau");
            return false;
        }

        $data = json_decode($response, true);

        if (!isset($data['routes'][0]['duration'])) {
            error_log("Erreur OSRM: pas de route trouvée");
            return false;
        }

        $duration = $data['routes'][0]['duration']; // en secondes
        $minutes = round($duration / 60);
        $heures = floor($minutes / 60);
        $restMin = $minutes % 60;

        $formatCourt = $heures . 'h' . str_pad($restMin, 2, '0', STR_PAD_LEFT);
        $formatLong = str_pad($heures, 2, '0', STR_PAD_LEFT) . ':' . str_pad($restMin, 2, '0', STR_PAD_LEFT) . ':00';

        return [
            'court' => $formatCourt,
            'long' => $formatLong
        ];
    }

    private static function getCoordsFromNom(string $ville, string $cp = '', $ville_id = ''): ?array
    {
        $baseUrl = Config::get('api.nominatim_url', 'https://nominatim.openstreetmap.org/search');
        $url = $baseUrl . "?format=json&q=" . urlencode("$ville $cp France");

        $timeout = Config::get('api.timeout', 5);
        $userAgent = Config::get('app.name', 'EcoRide') . '/1.0';

        $opts = [
            "http" => [
                "header" => "User-Agent: $userAgent",
                "timeout" => $timeout
            ]
        ];

        $context = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);

        if (!$response) {
            error_log("Erreur lors de l'appel Nominatim pour $ville $cp");
            return null;
        }

        $data = json_decode($response, true);

        if (!empty($data[0])) {
            try {
                $req = "UPDATE ville SET lat = :lat, lon = :lon WHERE ville_id = :id";
                $param = [
                    'lat' => $data[0]['lat'],
                    'lon' => $data[0]['lon'],
                    'id' => $ville_id
                ];
                Database::getInstance()->execute($req, $param);

                return [
                    'lat' => $data[0]['lat'],
                    'lon' => $data[0]['lon']
                ];
            } catch (Exception $e) {
                error_log("Erreur lors de la mise à jour des coordonnées: " . $e->getMessage());
            }
        }

        return null;
    }


    public static function calculerHeureArrivee(string $heureDepart, string $duree): string
    {
        $depart = \DateTime::createFromFormat('H:i:s', $heureDepart);

        if (!$depart) {
            throw new \Exception("Heure de départ invalide : $heureDepart");
        }

        // S'assurer que la durée est bien au format H:i:s
        $parts = explode(':', $duree);
        if (count($parts) !== 3) {
            throw new \Exception("Durée invalide, format attendu H:i:s : $duree");
        }

        list($h, $m, $s) = $parts;

        $interval = new DateInterval("PT{$h}H{$m}M{$s}S");
        $depart->add($interval);

        return $depart->format('H:i:s');
    }

    public static function calculerDateArrivee(string $dateDepart,string $heureDepart, string $duree): string
    {
        // Exemple d’entrée : "2025-07-15 08:00:00"
        $dateHeureDepart = $dateDepart . ' ' . $heureDepart;
        $depart = \DateTime::createFromFormat('Y-m-d H:i:s', $dateHeureDepart) ?: \DateTime::createFromFormat('Y-m-d H:i', $dateHeureDepart);

        if (!$depart) {
            throw new \Exception("Date/heure de départ invalide : $dateHeureDepart");
        }

        // Exemple durée : "01:31:00"
        $parts = explode(':', $duree);
        if (count($parts) !== 3) {
            throw new \Exception("Durée invalide, format attendu H:i:s : $duree");
        }

        [$h, $m, $s] = $parts;
        $interval = new \DateInterval("PT{$h}H{$m}M{$s}S");

        $depart->add($interval);

        // Retourne la date complète d’arrivée
        return $depart->format('Y-m-d');
    }
}
