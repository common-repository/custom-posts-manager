<?php
// Sprawdzenie uprawnień użytkownika
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'Nie masz wystarczających uprawnień, aby uzyskać dostęp do tej strony.', 'custom-posts-manager' ) );
}

// Pobranie opcji z bazy danych, z domyślną wartością jako pustą tablicą
$cpmanager_options = get_option( 'cpmanager_options', array() );
$enable_cloning = isset( $cpmanager_options['enable_cloning'] ) ? $cpmanager_options['enable_cloning'] : 0;

// Obsługa przesłania formularza
if ( isset( $_POST['cpm_cloning_submit'] ) && check_admin_referer( 'nonce_cpmanager_cloning' ) ) {
    // Zaktualizuj ustawienia w zależności od wartości checkboxa
    $cpmanager_options['enable_cloning'] = isset( $_POST['enable_cloning'] ) ? 1 : 0;
    update_option( 'cpmanager_options', $cpmanager_options );

    // Wyświetlenie komunikatu o zapisaniu ustawień
    echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Ustawienia zostały zapisane.', 'custom-posts-manager' ) . '</p></div>';
}
?>

<div class="wrap">
    <h1><?php _e( 'Ustawienia CP Manager', 'custom-posts-manager' ); ?></h1>
    <form method="post">
        <?php wp_nonce_field( 'nonce_cpmanager_cloning' ); ?>
        <h3><?php _e( 'Ustawienia klonowania wpisów', 'custom-posts-manager' ); ?></h3>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Włącz klonowanie wpisów', 'custom-posts-manager' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_cloning" value="1" <?php checked( $enable_cloning, 1 ); ?> />
                            <?php _e( 'Zezwalaj na klonowanie wpisów', 'custom-posts-manager' ); ?>
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" class="button-primary" name="cpm_cloning_submit" value="<?php _e( 'Zapisz ustawienia', 'custom-posts-manager' ); ?>" />
        </p>
    </form>
</div>
