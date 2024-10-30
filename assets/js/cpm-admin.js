jQuery(document).ready(function ($) {
    // Obsługa kliknięcia na przycisk recenzji
    $('.cpmanager-review-button').click(function (evt) {
        var href = $(this).attr('href'),
            id = $(this).attr('id');

        if ('cpmanager-rate' !== id) {
            evt.preventDefault();
        }

        var data = {
            action: 'cpmanager_review',
            security: cpmanager_vars.ajax_nonce, // Użycie zmiennej z `wp_localize_script`
            check: id
        };

        $.post(cpmanager_vars.ajax_url, data, function (response) {
            $('#' + cpmanager_vars.slug + '-cpmanager-review-notice').slideUp('fast', function () {
                $(this).remove();
            });
        });
    });

    // Obsługa zamknięcia powiadomienia recenzji
    $('#custom-posts-manager-cpmanager-review-notice .notice-dismiss').click(function () {
        var data = {
            action: 'cpmanager_review',
            security: cpmanager_vars.ajax_nonce,
            check: 'cpmanager-later'
        };

        $.post(cpmanager_vars.ajax_url, data, function (response) {
            $('#' + cpmanager_vars.slug + '-cpmanager-review-notice').slideUp('fast', function () {
                $(this).remove();
            });
        });
    });

    // Obsługa kliknięcia na przycisk cpm-review
    $('.cpm-review-button').click(function (evt) {
        var href = $(this).attr('href'),
            id = $(this).attr('id');

        if ('cpm-rate' !== id) {
            evt.preventDefault();
        }

        var data = {
            action: 'cpm_review',
            security: cpmanager_vars.ajax_nonce,
            check: id
        };

        $.post(cpmanager_vars.ajax_url, data, function (response) {
            $('#' + cpmanager_vars.slug + '-cpm-review-notice').slideUp('fast', function () {
                $(this).remove();
            });
        });
    });

    // Obsługa zamknięcia powiadomienia cpm-review
    $('#custom-posts-manager-cpm-review-notice .notice-dismiss').click(function () {
        var data = {
            action: 'cpm_review',
            security: cpmanager_vars.ajax_nonce,
            check: 'cpm-later'
        };

        $.post(cpmanager_vars.ajax_url, data, function (response) {
            $('#' + cpmanager_vars.slug + '-cpm-review-notice').slideUp('fast', function () {
                $(this).remove();
            });
        });
    });
});
