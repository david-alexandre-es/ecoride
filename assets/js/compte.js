

$(document).on('click', '#logout', function () {
    
        $.ajax({
            url: '/ecoride/ajax.php',
            type: 'POST',
            data: {
                controller: 'user',  // nom du contrôleur
                method: 'logout',  // méthode à appeler
            },
            success: function (response) {
                if (response.success) {
                    window.location.reload();  // Recharger la page pour mettre à jour l'état de connexion
                }
            },
            error: function () {
                alert('Une erreur est survenue lors de la participation au covoiturage.');
            }
        });
});
$(document).on('click', '.start_covoit', function () {
    // Code pour démarrer le covoiturage
    $.ajax({
        url: '/ecoride/ajax.php',
        type: 'POST',
        data: {
            controller: 'covoiturage',
            method: 'start',
            covoiturage_id: $(this).closest('.trip-item').data('id')
        },
        success: function (response) {
            if (response.success) {
                window.location.reload();
            }
        },
        error: function () {
            alert('Une erreur est survenue lors du démarrage du covoiturage.');
        }
    });
});
$(document).on('click', '.close_covoit', function () {
    // Code pour terminer le covoiturage
    $.ajax({
        url: '/ecoride/ajax.php',
        type: 'POST',
        data: {
            controller: 'covoiturage',
            method: 'close',
            covoiturage_id: $(this).closest('.trip-item').data('id')
        },
        success: function (response) {
            if (response.success) {
                window.location.reload();
            }
        },
        error: function () {
            alert('Une erreur est survenue lors de la fermeture du covoiturage.');
        }
    });
});
