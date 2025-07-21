function updatePrixTotal() {
    const prixPersonne = parseFloat($('#prix_personne').val()) || 0;
    const nbPlace = parseInt($('#nb_place').val()) || 0;
    const total = prixPersonne * nbPlace;
    $('#prix-total').text(total.toFixed(2) + ' €');
}

function calculerDureeTrajet() {
    const villeDepart = $('#ville_depart_id').val();
    const villeArrivee = $('#ville_arrivee_id').val();

    // Vérifier que les deux villes sont sélectionnées
    if (!villeDepart || !villeArrivee) {
        $('#duree').val('');
        return;
    }

    // Afficher un indicateur de chargement
    $('#duree').val('Calcul en cours...');

    $.ajax({
        url: '/ecoride/ajax.php',
        type: 'POST',
        data: {
            controller: 'option',
            method: 'calculerDuree',
            ville_depart_id: villeDepart,
            ville_arrivee_id: villeArrivee
        },
        success: function (response) {
            if (response.success) {
                $('#duree').val(response.duree.court); // ou .long selon usage
                $('#duree-raw').val(response.duree.long); // si tu veux stocker le format technique
            } else {
                $('#duree').val('Non disponible');
                console.error('Erreur:', response.error);
            }
        },
        error: function (xhr, status, error) {
            $('#duree').val('Erreur de calcul');
            console.error('Erreur AJAX:', error);
        }
    });
}


$(document).ready(function () {
    // Calcul du prix total
    $(document).on('input', '#prix_personne, #nb_place', updatePrixTotal);

    $(document).on('change', '#ville_depart_id, #ville_arrivee_id', function () {
        calculerDureeTrajet();
    });
    // Permettre le calcul manuel en cliquant sur le champ durée
    $('#duree').on('click', function () {
        if ($(this).val() === 'Non disponible' || $(this).val() === 'Erreur de calcul') {
            calculerDureeTrajet();
        }
    });

    // Date minimum = aujourd'hui
    const today = new Date().toISOString().split('T')[0];
    $('#date_depart').attr('min', today);
});