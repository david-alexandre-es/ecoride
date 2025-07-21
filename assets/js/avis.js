// Objet pour stocker les notes
let ratings = {
    global: 0,
    ponctualite: 0,
    conduite: 0,
    convivialite: 0
};

// Textes pour les différentes notes
const ratingTexts = {
    1: "😞 Très mauvais",
    2: "😕 Mauvais",
    3: "😐 Moyen",
    4: "😊 Bon",
    5: "😍 Excellent"
};

$(document).ready(function () {
    // Gestion des étoiles avec délégation d'événements pour AJAX
    $(document).on('click', '.star', function () {
        const $star = $(this);
        const $container = $star.closest('.rating-stars');
        const ratingType = $container.data('rating');
        const $stars = $container.find('.star');
        const value = parseInt($star.data('value'));

        ratings[ratingType] = value;
        console.log(`Clic sur étoile ${value} pour ${ratingType}`);

        // Mettre à jour les étoiles
        $stars.each(function (index) {
            if (index < value) {
                $(this).addClass('jaune').removeClass('gris');
                console.log(`Étoile ${index + 1} → jaune`);
            } else {
                $(this).addClass('gris').removeClass('jaune');
                console.log(`Étoile ${index + 1} → grise`);
            }
        });

        // Mettre à jour le texte
        $(`#${ratingType}-text`).text(ratingTexts[value]);

        // Mettre à jour la valeur dans le formulaire
        $(`input[name="data[note_${ratingType}]"]`).val(value);
    });

    // Effet hover avec délégation
    $(document).on('mouseenter', '.star', function () {
        const $star = $(this);
        const $container = $star.closest('.rating-stars');
        const $stars = $container.find('.star');
        const value = parseInt($star.data('value'));

        $stars.each(function (index) {
            if (index < value) {
                $(this).addClass('jaune').removeClass('gris');
            } else {
                $(this).addClass('gris').removeClass('jaune');
            }
        });
    });

    // Restaurer l'état au mouseout avec délégation
    $(document).on('mouseleave', '.star', function () {
        const $star = $(this);
        const $container = $star.closest('.rating-stars');
        const ratingType = $container.data('rating');
        const $stars = $container.find('.star');
        const currentRating = ratings[ratingType] || 0;

        $stars.each(function (index) {
            if (index < currentRating) {
                $(this).addClass('jaune').removeClass('gris');
            } else {
                $(this).addClass('gris').removeClass('jaune');
            }
        });
    });

    // Gestion du formulaire avec délégation
    $(document).on('submit', '#notation-form', function (e) {
        e.preventDefault();

        if (ratings.global === 0) {
            alert('Veuillez donner au moins une note globale');
            return;
        }

        const $form = $(this);

        // Envoi AJAX avec serialize() direct - tout est déjà dans le formulaire
        $.ajax({
            url: '/ecoride/ajax.php',
            type: 'POST',
            data: $form.serialize(),
            success: function (retour) {
                alert('Notation envoyée avec succès !');
                ferme_popup();
                // Optionnel : recharger la liste des covoiturages
                // location.reload();
            },
            error: function () {
                alert('Erreur lors de l\'envoi de la notation');
            }
        });
    });
});

// Fonctions pour afficher/masquer la popup
function noterTrajet(covoiturageId) {
    $.ajax({
        url: '/ecoride/ajax.php',
        type: 'POST',
        data: {
            controller: 'avis',
            method: 'getFormAvis',
            covoiturage_id: covoiturageId
        },
        success: function (retour) {
            $('#pop-avis').html(retour.html);
            ouvrir_popup('#pop-avis');
            // Réinitialiser le formulaire après chargement
            resetForm();
        }
    });
}

function resetForm() {
    // Réinitialiser les notes
    ratings = {
        global: 0,
        ponctualite: 0,
        conduite: 0,
        convivialite: 0
    };


    // Réinitialiser les textes
    $('#global-text').text('Cliquez sur les étoiles');
    $('#ponctualite-text').text('Cliquez sur les étoiles');
    $('#conduite-text').text('Cliquez sur les étoiles');
    $('#convivialite-text').text('Cliquez sur les étoiles');

    // Réinitialiser le formulaire si il existe
    const form = document.getElementById('notation-form');
    if (form) {
        form.reset();
    }
}
