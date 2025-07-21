<?php

namespace Src\Helper;

use \DateTime;
use \DateInterval;
use \Exception;

class Helper
{
    /**
     * Convertit une chaîne de caractères en format camelCase.
     *
     * @param string $string La chaîne à convertir.
     * @return string La chaîne convertie en camelCase.
     */
    public static function toCamelCase(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }

    public static function transformToTime(string $time): string
    {
        // Convertit le format HH:MM en minutes
        $parts = explode(':', $time);
        if (count($parts) !== 2 && count($parts) !== 3) {
            throw new \InvalidArgumentException("Le format de l'heure doit être HH:MM");
        }

        $hours = (int)$parts[0];
        $minutes = (int)$parts[1];

        return $hours . ":" . $minutes;
    }

    public static function transformTimeToSql(string $time): string
    {
        $depart = DateTime::createFromFormat('H:i', $time);
        $departFormatee = $depart->format('H:i:s');
        return $departFormatee;
    }




}