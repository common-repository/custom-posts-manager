jQuery(document).ready(function($){
    $('#cpm-notice .notice-dismiss').click(function(evt){
        evt.preventDefault();

        var ajaxData = {
            'action': 'cpmanager_dismiss_notices', // Dostosuj nazwę akcji do używanej w PHP
            'security': cpm_ajax_data.ajax_nonce // Użycie zmiennej przekazanej z PHP
        };

        $.ajax({
            url: cpm_ajax_data.ajax_url, // Użycie URL przekazanego z PHP
            method: "POST",
            data: ajaxData,
            dataType: "html",
            success: function(response){
                if (response === 'ok') {
                    $("#cpm-notice").slideUp('fast', function(){
                        $(this).remove();
                    });
                } else {
                    console.error('Błąd podczas ukrywania powiadomienia: ', response);
                }
            },
            error: function(xhr, status, error){
                console.error('Wystąpił błąd podczas wysyłania żądania AJAX: ', error);
            }
        });
    });
});
