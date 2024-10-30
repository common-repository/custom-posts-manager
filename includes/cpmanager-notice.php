<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
?>

<div class="notice cpmanager-notice" id="cpmanager-notice">
    <div class="cpmanager-notice-container">
        <div class="cpmanager-notice-logo">
            <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'assets/img/logo.png' ); ?>" width="80" alt="<?php echo esc_attr( 'Logo', 'custom-posts-manager' ); ?>" />
        </div>
        <div class="cpmanager-notice-content">
            <h1><?php esc_html_e( 'Custom Posts Manager', 'custom-posts-manager' ); ?></h1>
            <p><?php esc_html_e( 'Dziękujemy za zainstalowanie wtyczki Custom Posts Manager. Ta wtyczka umożliwia klonowanie wpisów, stron oraz niestandardowych typów postów jednym kliknięciem. Aby rozpocząć korzystanie z funkcji klonowania, włącz ją w ustawieniach wtyczki.', 'custom-posts-manager' ); ?></p>
            <p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=cpmanager-settings' ) ); ?>" class="button button-primary button-hero">
                    <?php esc_html_e( 'Włącz klonowanie teraz', 'custom-posts-manager' ); ?>
                </a>
                <a href="https://custompostmanager.com/" target="_blank" class="button button-secondary button-hero">
                    <?php esc_html_e( 'Wybierz wersję PRO', 'custom-posts-manager' ); ?>
                </a>
            </p>
        </div>
    </div>
    <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Zamknij to powiadomienie.', 'custom-posts-manager' ); ?></span></button>
</div>
