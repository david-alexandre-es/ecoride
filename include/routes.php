<?php
return [
    'home'=> [
        'path' => '/ecoride/index',
        'controller' => \Src\Controller\SiteController::class,
        'method' => 'showHomePage',
        'pattern' => [],
    ],
    'liste'=> [
        'path' => '/ecoride/#ville_dep_id#/#ville_arr_id#/#date_dep#/#nb_place#/liste',
        'controller' => \Src\Controller\SiteController::class,
        'method' => 'showPageListe',
        'pattern' => [
            'ville_dep_id' => '[0-9]+',
            'ville_arr_id' => '[0-9]+',
            'date_dep' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
            'nb_place' => '[1-9][0-9]*'
        ],
    ],
    'fiche'=> [
        'path' => '/ecoride/#id#/fiche',
        'controller' => \Src\Controller\SiteController::class,
        'method' => 'showFicheProduit',
        'pattern' => [
            'id' => '[0-9]+'
        ],
    ],
    'compte'=> [
        'path' => '/ecoride/compte',
        'controller' => \Src\Controller\SiteController::class,
        'method' => 'showPageCompte',
        'pattern' => [],
    ],
    'contact'=> [
        'path' => '/ecoride/contact',
        'controller' => \Src\Controller\SiteController::class,
        'method' => 'showPageContact',
        'pattern' => [],
    ],
    'publier'=> [
        'path' => '/ecoride/publier-trajet',
        'controller' => \Src\Controller\SiteController::class,
        'method' => 'showPagePublier',
        'pattern' => [],
    ],

];