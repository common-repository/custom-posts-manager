<?php
/**
 * Plugin Name: Custom Post Manager - Duplicate or Clone Posts, Pages, and Custom Post Types
 * Plugin URI: https://custompostmanager.com
 * Description: Manage Clone Posts, Pages, and Custom Post Types.
 * Version: 1.0.0
 * Author: CPManager
 * Author URI: https://custompostmanager.com
 * Tested up to: 6.6
 * Requires at least: 5.0
 * Requires PHP: 7.2.5
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-posts-manager
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Definicje stałych używanych w wtyczce
define( 'CPMANAGER_URL', plugins_url( '', __FILE__ ) );
define( 'CPMANAGER_DIR', plugin_dir_path( __FILE__ ) );
define( 'CPMANAGER_VERSION', '1.0.0' );

// Funkcja inicjalizująca wtyczkę
function cpmanager_init() {
    $cpmanager = new CPManager_Engine();
}
add_action( 'plugins_loaded', 'cpmanager_init' );

// Dodanie linka "Postaw mi kawę" obok "Deactivate"
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'cpmanager_add_custom_links');
function cpmanager_add_custom_links($links) {
    $settings_link = '<a href="' . esc_url( admin_url('admin.php?page=cpmanager-settings') ) . '">' . esc_html__( 'Ustawienia', 'custom-posts-manager' ) . '</a>';
    $donate_link = '<a href="https://ko-fi.com/wpdesigner" target="_blank" style="color: #ff4500; font-weight: bold;">' . esc_html__( 'Postaw mi kawę', 'custom-posts-manager' ) . '</a>';
    array_push($links, $settings_link, $donate_link);
    return $links;
}

class CPManager_Engine {

    public function __construct() {
        $this->register_hooks();
    }

    private function register_hooks() {
        // Rejestracja akcji i filtrów WordPressa
        add_action( 'admin_action_clone', array( $this, 'cpmanager_clone_post' ) );
        add_filter( 'post_row_actions', array( $this, 'cpmanager_add_clone_link' ), 10, 2 );
        add_filter( 'page_row_actions', array( $this, 'cpmanager_add_clone_link' ), 10, 2 );

        // Rejestracja menu w panelu admina
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        // Powiadomienia w panelu admina
        add_action( 'admin_notices', array( $this, 'cpmanager_notice_not_checked' ) );
        add_action( 'wp_ajax_cpmanager_dismiss_notices', array( $this, 'dismiss_notices' ) );

        // Ładowanie plików językowych
        add_action( 'plugins_loaded', array( $this, 'load_cpmanager_textdomain' ) );

        // Funkcja do ładowania stylów i skryptów na stronach administracyjnych
        add_action('admin_enqueue_scripts', array( $this, 'cpmanager_enqueue_scripts' ));
    }

    public function cpmanager_enqueue_scripts($hook_suffix) {
        // Ładowanie stylów i skryptów tylko na stronach związanych z wtyczką
        if ( strpos( $hook_suffix, 'cpmanager' ) === false ) {
            return;
        }

        // Rejestracja i wczytanie stylów
        wp_enqueue_style( 'cpmanager-notice-style', CPMANAGER_URL . '/assets/css/cpmanager-notice.css', array(), '1.0' );

        // Rejestracja i wczytanie skryptów dla powiadomienia
        wp_enqueue_script( 'cpmanager-notice-script', CPMANAGER_URL . '/assets/js/cpm-notice.js', array( 'jquery' ), '1.0', true );

        // Rejestracja i wczytanie skryptu dla klonowania
        wp_enqueue_script( 'cpmanager-clone-script', CPMANAGER_URL . '/assets/js/cpm-clone.js', array( 'jquery' ), '1.0', true );

        // Rejestracja i wczytanie skryptów dla recenzji
        wp_enqueue_script( 'cpmanager-review-script', CPMANAGER_URL . '/assets/js/cpm-review.js', array( 'jquery' ), '1.0', true );

        // Utworzenie nonce i przekazanie zmiennych do JavaScript
        $ajax_nonce = wp_create_nonce( 'cpmanager-ajax-nonce' );
        wp_localize_script( 'cpmanager-notice-script', 'cpm_ajax_data', array(
            'ajax_nonce' => $ajax_nonce,
            'ajax_url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
            'slug'       => 'custom-posts-manager'
        ));

        // Przekazanie zmiennych dla skryptu klonowania
        wp_localize_script( 'cpmanager-clone-script', 'cpm_clone_data', array(
            'ajax_nonce'       => $ajax_nonce,
            'ajax_url'         => esc_url( admin_url( 'admin-ajax.php' ) ),
            'confirm_message'  => esc_html__( 'Czy na pewno chcesz powielić ten post?', 'custom-posts-manager' ),
            'success_message'  => esc_html__( 'Post zostanie powielony...', 'custom-posts-manager' )
        ));

        // Przekazanie zmiennych dla skryptu recenzji
        wp_localize_script( 'cpmanager-review-script', 'cpmanager_review_vars', array(
            'ajax_nonce' => $ajax_nonce,
            'ajax_url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
            'slug'       => esc_attr( 'custom-posts-manager' )
        ));
    }

    // Funkcja klonowania postów
        public function cpmanager_clone_post() {
        // Przetworzenie i sanitizacja wartości GET przy użyciu wp_unslash() i sanitize_text_field()
        $clone_nonce = isset( $_GET['clone_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['clone_nonce'] ) ) : '';

        // Sprawdzenie, czy nonce jest prawidłowy
        if ( ! isset( $_GET['post'] ) || ! wp_verify_nonce( $clone_nonce, basename( __FILE__ ) ) ) {
            wp_die( esc_html__( 'Błąd bezpieczeństwa: nieprawidłowy nonce.', 'custom-posts-manager' ) );
        }

        // Sanitizuj identyfikator postu
        $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

        // Sprawdzenie, czy post istnieje
        $post = get_post( $post_id );
        if ( empty( $post ) ) {
            wp_die( esc_html__( 'Nie znaleziono wpisu.', 'custom-posts-manager' ) );
        }

        // Przygotowanie danych do nowego postu
        $new_post_data = array(
            'post_title'    => $post->post_title . ' (Kopia)',
            'post_content'  => $post->post_content,
            'post_status'   => 'draft',
            'post_type'     => $post->post_type,
            'post_author'   => get_current_user_id(),
            'post_excerpt'  => $post->post_excerpt,
            'post_category' => wp_get_post_categories( $post_id ),
            'post_parent'   => $post->post_parent,
            'menu_order'    => $post->menu_order,
        );

        // Wstawienie nowego postu
        $new_post_id = wp_insert_post( $new_post_data );

        // Skopiowanie metadanych postu
        $meta_data = get_post_meta( $post_id );
        if ( ! empty( $meta_data ) ) {
            foreach ( $meta_data as $key => $value ) {
                update_post_meta( $new_post_id, $key, maybe_unserialize( $value[0] ) );
            }
        }

        // Przekierowanie do odpowiedniej strony po klonowaniu
        $redirect_url = admin_url( 'post.php?action=edit&post=' . $new_post_id );
        if ( $post->post_type === 'page' ) {
            $redirect_url = admin_url( 'edit.php?post_type=page' );
        } elseif ( $post->post_type !== 'post' ) {
            $redirect_url = admin_url( 'edit.php?post_type=' . $post->post_type );
        }

        wp_safe_redirect( esc_url( $redirect_url ) );
        exit;
    }


    public function cpmanager_add_clone_link( $actions, $post ) {
        // Pobierz opcje wtyczki
        $cpmanager_options = get_option( 'cpmanager_options', array() );
        $enable_cloning = isset( $cpmanager_options['enable_cloning'] ) ? $cpmanager_options['enable_cloning'] : 0;

        // Sprawdź, czy klonowanie jest włączone
        if ( $enable_cloning === 1 && current_user_can( 'edit_post', $post->ID ) ) {
            $actions['clone'] = '<a href="' . esc_url( wp_nonce_url( 'admin.php?action=clone&post=' . $post->ID, basename( __FILE__ ), 'clone_nonce' ) ) . '" title="' . esc_attr__( 'Powiel ten post', 'custom-posts-manager' ) . '">' . esc_html__( 'Powiel', 'custom-posts-manager' ) . '</a>';
        }

        return $actions;
    }

    public function admin_menu() {
        add_menu_page(
            esc_html__( 'CP Manager', 'custom-posts-manager' ),
            esc_html__( 'CP Manager', 'custom-posts-manager' ),
            'manage_options',
            'cpmanager',
            array( $this, 'cpmanager_page' ),
            'dashicons-admin-generic',
            5
        );

        add_submenu_page(
            'cpmanager',
            esc_html__( 'Ustawienia', 'custom-posts-manager' ),
            esc_html__( 'Ustawienia', 'custom-posts-manager' ),
            'manage_options',
            'cpmanager-settings',
            array( $this, 'admin_page' )
        );
    }

    public function cpmanager_page() {
        echo '<h1>' . esc_html__( 'Witaj w CP Manager', 'custom-posts-manager' ) . '</h1>';
        $info_file = CPMANAGER_DIR . 'includes/cpmanager-info.php';
        if ( file_exists( $info_file ) ) {
            include $info_file;
        } else {
            echo '<p>' . esc_html__( 'Plik informacji nie został znaleziony.', 'custom-posts-manager' ) . '</p>';
        }
    }

    public function admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Nie masz wystarczających uprawnień, aby uzyskać dostęp do tej strony.', 'custom-posts-manager' ) );
        }
        $settings_page_path = CPMANAGER_DIR . 'admin/cpmanager-settings.php';
        if ( file_exists( $settings_page_path ) ) {
            require $settings_page_path;
        } else {
            echo '<h1>' . esc_html__( 'Błąd: Plik ustawień nie istnieje.', 'custom-posts-manager' ) . '</h1>';
        }
    }

    public function load_cpmanager_textdomain() {
        load_plugin_textdomain( 'custom-posts-manager', false, basename( dirname( __FILE__ ) ) . '/languages/' );
    }

    public function dismiss_notices() {
        if ( ! check_admin_referer( 'cpmanager_dismiss_notice', 'cpmanager_nonce' ) ) {
            wp_die( 'nok' );
        }
        update_option( 'cpmanager_notice', '1' );
        wp_die( 'ok' );
    }

    public function cpmanager_notice_not_checked() {
        // Sprawdzenie, czy powiadomienie zostało już ukryte
        $dismissed = get_option( 'cpmanager_notice', false );
        if ( $dismissed ) {
            return; // Nie wyświetlaj ponownie powiadomienia
        }

        // Sprawdzenie, czy jest już dodane powiadomienie
        global $cpmanager_notice_displayed;
        if ( isset( $cpmanager_notice_displayed ) && $cpmanager_notice_displayed ) {
            return; // Uniknij wielokrotnego wyświetlania powiadomienia
        }
        $cpmanager_notice_displayed = true;

        // Wyświetlenie powiadomienia, jeśli jeszcze nie było wyświetlone
        if ( file_exists( CPMANAGER_DIR . 'includes/cpmanager-notice.php' ) ) {
            include CPMANAGER_DIR . 'includes/cpmanager-notice.php';
        }
    }

}
