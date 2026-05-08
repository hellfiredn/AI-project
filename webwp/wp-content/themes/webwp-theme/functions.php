<?php
/**
 * webwp-theme functions
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WEBWP_VERSION', '1.1.0' );
define( 'WEBWP_DIR', get_template_directory() );
define( 'WEBWP_URI', get_template_directory_uri() );

/*
 * Include framework files
 */
foreach (glob(WEBWP_DIR . "/includes/*.php") as $file_name) {
    require_once($file_name);
}

/* ---------- Theme support ---------- */
add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'custom-logo', [
        'height'      => 60,
        'width'       => 180,
        'flex-height' => true,
        'flex-width'  => true,
    ] );

    register_nav_menus( [
        'primary' => __( 'Primary Menu', 'webwp' ),
        'footer'  => __( 'Footer Menu', 'webwp' ),
    ] );
} );

/* ---------- Enqueue assets ---------- */
add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts (Poppins fallback until CriteriaCF .woff2 files are dropped in assets/fonts/)
    wp_enqueue_style(
        'webwp-google-fonts',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap',
        [],
        null
    );

    // Bootstrap 5.3 (CSS + bundle JS with Popper)
    wp_enqueue_style(
        'bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        [],
        '5.3.3'
    );
    wp_enqueue_style(
        'bootstrap-icons',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
        [],
        '1.11.3'
    );

    // Theme main CSS (loaded after Bootstrap to override)
    wp_enqueue_style(
        'webwp-main',
        WEBWP_URI . '/assets/css/main.css',
        [ 'bootstrap' ],
        WEBWP_VERSION
    );

    // Bootstrap JS bundle (includes Popper)
    wp_enqueue_script(
        'bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        [],
        '5.3.3',
        true
    );

    // GSAP core + ScrollTrigger + SplitText alternative
    wp_enqueue_script(
        'gsap',
        'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js',
        [],
        '3.12.5',
        true
    );
    wp_enqueue_script(
        'gsap-scrolltrigger',
        'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js',
        [ 'gsap' ],
        '3.12.5',
        true
    );

    // Theme main JS
    wp_enqueue_script(
        'webwp-main',
        WEBWP_URI . '/assets/js/main.js',
        [ 'bootstrap', 'gsap', 'gsap-scrolltrigger' ],
        WEBWP_VERSION,
        true
    );

    wp_localize_script( 'webwp-main', 'WEBWP', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'home_url' => home_url( '/' ),
        'nonce'    => wp_create_nonce( 'webwp_nonce' ),
    ] );
} );

/* ---------- Bootstrap 5 nav walker (minimal) ---------- */
require_once WEBWP_DIR . '/inc/class-bootstrap-nav-walker.php';

/* ---------- Cleanup head ---------- */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
