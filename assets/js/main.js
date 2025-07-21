$(document).ready(function () {
    $('.select2').select2({
        placeholder: 'Rechercher une ville',
        minimumInputLength: 2,
        language: {
            inputTooShort: function (args) {
                const n = args.minimum - args.input.length;
                return `Veuillez entrer encore ${n} caractère${n > 1 ? 's' : ''}`;
            }
        },
        ajax: {
            url: '/ecoride/ajax.php',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            with: '100%',
            data: function (params) {
                return {
                    controller: 'option',  // nom du contrôleur
                    method: 'getVille',  // méthode à appeler
                    text: params.term  // terme de recherche
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return {
                            id: item.ville_id,
                            text: item.ville  // ou item.ville, selon ta structure
                        };
                    })
                };
            },
            cache: true
        },
    });

});

$(document).on('click', '.onglet', function () {
    const div_a_ferme = $(this).data('hide');
    const div_a_ouvrir = $(this).data('open');
    const replace_class = $(this).data('replace');

    if (div_a_ferme) {
        $('.' + div_a_ferme).addClass('none');
    }

    if (div_a_ouvrir) {
        $('#' + div_a_ouvrir).removeClass('none');
    }
    if (replace_class) {
        const classes = replace_class.split('|');
        $(this).removeClass(classes[1]).addClass(classes[0]);
        $('.onglet').not(this).removeClass(classes[0]).addClass(classes[1]);
    }
});



$(document).on('click', '#masque', function () {
    ferme_popup();
});
function ouvrir_popup(popup) {
    $('#calque').removeClass('none');
    $('.divpop').addClass('none');
    $(popup).removeClass('none');
}
function ferme_popup() {
    $('#calque').addClass('none');
    $('.divpop').addClass('none');
}