jQuery(document).ready(function($) {
    // Obsługa kliknięcia przycisku "Powiel"
    $('a[href*="action=cpmanager_clone_post"]').on('click', function(e) {
        // Zapytanie o potwierdzenie przed wykonaniem klonowania
        var confirmClone = confirm(cpm_clone_data.confirm_message); // Użycie zmiennej przekazanej z PHP
        
        if (!confirmClone) {
            // Jeśli użytkownik anuluje, zatrzymaj akcję
            e.preventDefault();
            return false;
        }

        // Jeżeli potwierdzone, pokazujemy powiadomienie (opcjonalnie)
        alert(cpm_clone_data.success_message); // Użycie zmiennej przekazanej z PHP
    });
});
