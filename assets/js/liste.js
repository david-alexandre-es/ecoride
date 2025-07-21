$(document).ready(function () {
    // Sauvegarde de tous les covoiturages au chargement
    window.tousLesCovoiturages = $('.ticket-covoit').clone();

    // Initialisation du slider de prix
    $("#slider-range").slider({
        range: true,
        min: parseInt($("#slider-range").attr('data-min')),
        max: parseInt($("#slider-range").attr('data-max')),
        values: [parseInt($("#slider-range").attr('data-min')), parseInt($("#slider-range").attr('data-max'))],
        slide: function (event, ui) {

            $("#div_prix_min").html(ui.values[0]);
            $("#div_prix_max").html(ui.values[1]);
            $("#prix_min").val(ui.values[0]);
            $("#prix_max").val(ui.values[1]);
            // Filtrage en temps réel pendant le glissement
            filtrerCovoiturages();
        }
    });

    // Initialisation des valeurs du slider
    $("#div_prix_min").html($("#slider-range").slider("values", 0));
    $("#div_prix_max").html($("#slider-range").slider("values", 1));
    $("#prix_min").val($("#slider-range").slider("values", 0));
    $("#prix_max").val($("#slider-range").slider("values", 1));
});

// Gestion des checkboxes avec classe sel_filtre
$(document).on('change', '.sel_filtre', function () {
    filtrerCovoiturages();
});

// Gestion du slider de prix (à la fin du mouvement)
$(document).on("slidestop", "#slider-range", function (event, ui) {
    filtrerCovoiturages();
});

$(document).on("click", ".participer", function (event, ui) {
    if (confirm("Êtes-vous sûr de vouloir participer à ce covoiturage ?")) {
        // Empêche le comportement par défaut du lien
        event.preventDefault();
        const idCovoit = $(this).closest('.ticket-covoit').data('id');
        const url = '/ecoride/ajax.php';

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                controller: 'covoiturage',  // nom du contrôleur
                method: 'reserver',  // méthode à appeler
                covoiturage_id: idCovoit,  // ID du covoiturage
                nb_place: $('#nb_place').val()  // Nombre de places restantes
            },
            success: function (retour) {
                if (retour.success) {
                    alert('Vous avez rejoint le covoiturage avec succès !');
                    // Mettre à jour l'affichage ou rediriger si nécessaire
                } else {
                    if (retour.cause == "login") {

                        $('#pop-connexion').html(retour.html);
                        ouvrir_popup('#pop-connexion');

                    }
                }
            },
            error: function () {
                alert('Une erreur est survenue lors de la participation au covoiturage.');
            }
        });
    }
});
$(document).on("click", ".annuler", function (event, ui) {
    if (confirm("Êtes-vous sûr de vouloir annuler votre participation à ce covoiturage ?")) {
        event.preventDefault();
        const idCovoit = $(this).closest('.ticket-covoit').data('id');
        const url = '/ecoride/ajax.php';

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                controller: 'covoiturage',  // nom du contrôleur
                method: 'annuler',  // méthode à appeler
                covoiturage_id: idCovoit,  // ID du covoiturage
            },
            success: function (response) {
                if (response.success) {
                    alert('Vous avez rejoint le covoiturage avec succès !');
                    // Mettre à jour l'affichage ou rediriger si nécessaire
                } else {
                    if (retour.cause == "login") {

                        $('#pop-connexion').html(retour.html);
                        ouvrir_popup('#pop-connexion');

                    }
                }
            },
            error: function () {
                alert('Une erreur est survenue lors de la participation au covoiturage.');
            }
        });
    }
});


// Fonction de filtrage côté client - Version dynamique
function filtrerCovoiturages() {
    var prixMin = parseInt($("#prix_min").val());
    var prixMax = parseInt($("#prix_max").val());
    console.log("Filtrage des covoiturages entre " + prixMin + " et " + prixMax + " euros");

    // Récupération dynamique des filtres actifs
    var filtresActifs = [];
    $('.sel_filtre:checked').each(function () {
        console.log("Filtre actif: " + $(this).data('type'));
        filtresActifs.push($(this).data('type'));
    });

    var covoituragesAffiches = 0;

    // Mise à jour de l'affichage
    $('#liste-covoiturages').empty();

    window.tousLesCovoiturages.each(function () {
        var $covoiturage = $(this);
        var afficher = true;

        // Vérification du prix
        var prix = parseInt($covoiturage.data('prix'));
        if (prix < prixMin || prix > prixMax) {
            afficher = false;
        }

        // Vérification dynamique des filtres
        if (afficher && filtresActifs.length > 0) {
            $.each(filtresActifs, function (index, typeFiltre) {
                console.log("Vérification du filtre: " + typeFiltre);
                // Conversion du type de filtre en attribut data
                var dataAttribute = typeFiltre.replace(/_/g, '-');
                var valeurCovoiturage = $covoiturage.data(dataAttribute);

                // Gestion spéciale pour les filtres matin/après-midi
                if (typeFiltre === 'am') {
                    if ($covoiturage.data('matin') != '1') {
                        afficher = false;
                        return false;
                    }
                } else if (typeFiltre === 'pm') {
                    if ($covoiturage.data('matin') != '0') {
                        afficher = false;
                        return false;
                    }
                } else {
                    // Pour les autres filtres (fumeur, animal, etc.)
                    if (!valeurCovoiturage || valeurCovoiturage == '0') {
                        afficher = false;
                        return false;
                    }
                }
            });
        }

        if (afficher) {
            $('#liste-covoiturages').append($covoiturage.clone());
            covoituragesAffiches++;
        }
    });

    // Mise à jour du compteur
    $('#nb_covoit').text(covoituragesAffiches);

    // Animation si aucun résultat
    if (covoituragesAffiches === 0) {
        $('#liste-covoiturages').html('<div class="no-results">Aucun covoiturage ne correspond à vos critères</div>');
    }
}

// Fonction utilitaire pour formater les prix
function format_prix(prix) {
    return (prix / 100).toLocaleString('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }) + ' €';
}