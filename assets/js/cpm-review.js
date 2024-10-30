jQuery(document).ready(function ($) {
    // Obsługa kliknięcia na przyciski recenzji
    $('.cpmanager-review-button').on('click', function (e) {
        e.preventDefault();

        // Pobieranie id klikniętego przycisku
        var buttonId = $(this).attr('id');

        // Sprawdzenie, czy użytkownik kliknął "Przypomnij później" lub "Nie pokazuj więcej"
        if (buttonId !== 'cpmanager-rate') {
            // Jeśli kliknięto któryś z pozostałych przycisków, wyślij żądanie AJAX do serwera
            var data = {
                action: 'cpmanager_review', // Nazwa akcji dla PHP
                security: cpmanager_review_vars.ajax_nonce, // Przekazanie nonce z PHP
                check: buttonId // Przekazanie id przycisku
            };

            // Wysłanie żądania AJAX
            $.post(cpmanager_review_vars.ajax_url, data, function (response) {
                if (response === 'ok') {
                    // Jeżeli odpowiedź jest poprawna, ukryj powiadomienie
                    $('#' + cpmanager_review_vars.slug + '-cpmanager-review-notice').slideUp('fast', function () {
                        $(this).remove();
                    });
                } else {
                    console.error('Wystąpił błąd podczas ukrywania powiadomienia: ', response);
                }
            }).fail(function (xhr, status, error) {
                console.error('Wystąpił błąd AJAX: ', error);
            });
        } else {
            // Jeśli kliknięto przycisk "Oceń", otwórz nową kartę i przejdź do strony oceny
            window.open($(this).attr('href'), '_blank');
        }
    });

    // Obsługa zamknięcia powiadomienia przy użyciu standardowego przycisku zamknięcia
    $('.notice-dismiss').on('click', function (e) {
        e.preventDefault();

        // Przesyłanie żądania AJAX do zamknięcia powiadomienia
        var data = {
            action: 'cpmanager_review', // Nazwa akcji dla PHP
            security: cpmanager_review_vars.ajax_nonce, // Przekazanie nonce z PHP
            check: 'cpmanager-later' // Przekazanie id dla "Przypomnij później"
        };

        // Wysłanie żądania AJAX
        $.post(cpmanager_review_vars.ajax_url, data, function (response) {
            if (response === 'ok') {
                // Ukryj powiadomienie po zamknięciu
                $('#' + cpmanager_review_vars.slug + '-cpmanager-review-notice').slideUp('fast', function () {
                    $(this).remove();
                });
            } else {
                console.error('Wystąpił błąd podczas zamykania powiadomienia: ', response);
            }
        }).fail(function (xhr, status, error) {
            console.error('Wystąpił błąd AJAX: ', error);
        });
    });
});
