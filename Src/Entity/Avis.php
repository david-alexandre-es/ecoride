<?php

namespace Src\Entity;

use Src\Entity\Utilisateur;
use Src\Entity\Covoiturage;
use DateTime;

class Avis
{
    public ?int $avis_id = null;

    public int $utilisateur_id;    // Utilisateur noté
    public int $auteur_id;         // Celui qui laisse l'avis
    public int $covoiturage_id;

    public ?float $note_global = null;
    public ?float $note_conduite = null;
    public ?float $note_ponctualite = null;
    public ?float $note_convivialite = null;

    public ?string $commentaire = null;
    public ?string $statut = null;
    public ?bool $recommandation = null;
    public ?string $date_creation = null;

    public ?Utilisateur $utilisateur = null;
    public ?Utilisateur $auteur = null;
    public ?Covoiturage $covoiturage = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        if (empty($this->note_global)) {
            $this->note_global = $this->calculateNoteGlobale();
        }

        if (empty($this->date_creation)) {
            $this->date_creation = (new DateTime())->format('Y-m-d H:i:s');
        }
    }

    public static function clone(array $data, string $prefix = "avis_"): self
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

    public function calculateNoteGlobale(): ?float
    {
        $notes = array_filter([
            $this->note_conduite,
            $this->note_ponctualite,
            $this->note_convivialite,
        ], fn($n) => $n !== null);

        if (count($notes) === 0) {
            return null;
        }

        return round(array_sum($notes) / count($notes), 2);
    }

    public function setUtilisateur(Utilisateur $utilisateur): void
    {
        $this->utilisateur = $utilisateur;
        $this->utilisateur_id = $utilisateur->utilisateur_id;
    }

    public function setAuteur(Utilisateur $utilisateur): void
    {
        $this->auteur = $utilisateur;
        $this->auteur_id = $utilisateur->utilisateur_id;
    }

    public function setCovoiturage(Covoiturage $covoiturage): void
    {
        $this->covoiturage = $covoiturage;
        $this->covoiturage_id = $covoiturage->covoiturage_id;
    }

    public function getArrayCopy(): array
    {
        return [
            'avis_id' => $this->avis_id,
            'utilisateur_id' => $this->utilisateur_id,
            'auteur_id' => $this->auteur_id,
            'covoiturage_id' => $this->covoiturage_id,
            'note_global' => $this->note_global,
            'note_conduite' => $this->note_conduite,
            'note_ponctualite' => $this->note_ponctualite,
            'note_convivialite' => $this->note_convivialite,
            'commentaire' => $this->commentaire,
            'recommandation' => $this->recommandation,
            'statut' => $this->statut,
            'date_creation' => $this->date_creation,
        ];
    }
}
