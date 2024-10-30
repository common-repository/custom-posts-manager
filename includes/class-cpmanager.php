<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class CPManager_Review {

    private $value;
    private $messages;
    private $link = 'https://wordpress.org/plugins/custom-posts-manager/#reviews';
    private $slug = 'custom-posts-manager';

    function __construct() {
        $this->messages = array(
            'notice'  => esc_html__( "Cześć! Super, że korzystasz z Custom Posts Manager już od kilku dni - mamy nadzieję, że Ci się podoba! Jeśli tak, rozważ ocenienie wtyczki. Będzie to dla nas bardzo ważne. Trzymaj się!", 'custom-posts-manager' ),
            'rate'    => esc_html__( 'Oceń wtyczkę', 'custom-posts-manager' ),
            'rated'   => esc_html__( 'Przypomnij mi później', 'custom-posts-manager' ),
            'no_rate' => esc_html__( 'Nie pokazuj więcej', 'custom-posts-manager' ),
        );

        // Rejestracja akcji i filtrów
        add_action( 'init', array( $this, 'init' ) );
    }

    public function init() {
        if ( ! is_admin() ) {
            return;
        }

        $this->value = $this->get_value();

        if ( $this->check() ) {
            add_action( 'admin_notices', array( $this, 'cpmanager_rate_notice' ) );
            add_action( 'wp_ajax_cpmanager_review', array( $this, 'ajax' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
        }
    }

    private function check() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }
        return ( time() > $this->value );
    }

    private function get_value() {
        $value = get_option( 'cpmanager-rate-time', false );

        if ( $value ) {
            return $value;
        }

        $value = time() + DAY_IN_SECONDS;
        update_option( 'cpmanager-rate-time', $value );
        return $value;
    }

    public function cpmanager_rate_notice() {
        $url = esc_url( sprintf( $this->link, $this->slug ) );
        ?>
        <div id="<?php echo esc_attr( $this->slug ) ?>-cpmanager-review-notice" class="notice notice-success is-dismissible" style="margin-top:30px;">
            <p><?php echo esc_html( $this->messages['notice'] ); ?></p>
            <p class="actions">
                <a id="cpmanager-rate" href="<?php echo esc_url( $url ); ?>" target="_blank" class="button button-primary cpmanager-review-button">
                    <?php echo esc_html( $this->messages['rate'] ); ?>
                </a>
                <a id="cpmanager-later" href="#" style="margin-left:10px" class="cpmanager-review-button"><?php echo esc_html( $this->messages['rated'] ); ?></a>
                <a id="cpmanager-no-rate" href="#" style="margin-left:10px" class="cpmanager-review-button"><?php echo esc_html( $this->messages['no_rate'] ); ?></a>
            </p>
        </div>
        <?php
    }

    public function ajax() {
        check_ajax_referer( 'cpmanager-review', 'security' );

        if ( ! isset( $_POST['check'] ) ) {
            wp_die( 'ok' );
        }

        $time = $this->get_value();

        switch ( $_POST['check'] ) {
            case 'cpmanager-rate':
            case 'cpmanager-no-rate':
                $time = time() + YEAR_IN_SECONDS * 5;
                break;
            case 'cpmanager-later':
                $time = time() + WEEK_IN_SECONDS;
                break;
        }

        update_option( 'cpmanager-rate-time', $time );
        wp_die( 'ok' );
    }

    public function enqueue() {
        // Rejestracja skryptu i jego lokalizacja
        wp_enqueue_script( 'cpmanager-review-script', CPMANAGER_URL . '/assets/js/cpm-review.js', array( 'jquery' ), '1.0', true );

        // Przekazanie wartości do skryptu JS
        $ajax_nonce = wp_create_nonce( 'cpmanager-review' );
        wp_localize_script( 'cpmanager-review-script', 'cpmanager_review_vars', array(
            'ajax_nonce' => $ajax_nonce,
            'ajax_url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
            'slug'       => esc_attr( $this->slug )
        ));
    }
}

new CPManager_Review();
