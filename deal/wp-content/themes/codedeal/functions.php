<?php
/**
 * CodeDeal — Theme bootstrap.
 */
if (!defined('ABSPATH')) exit;

define('CODEDEAL_VERSION', '1.1.0');
define('CODEDEAL_DIR', get_template_directory());
define('CODEDEAL_URI', get_template_directory_uri());

require_once CODEDEAL_DIR . '/inc/post-types.php';
require_once CODEDEAL_DIR . '/inc/metaboxes.php';
require_once CODEDEAL_DIR . '/inc/template-tags.php';

/* ----------------------------------------------------------- *
 *  Theme setup
 * ----------------------------------------------------------- */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('responsive-embeds');
    add_theme_support('automatic-feed-links');

    register_nav_menus([
        'primary' => __('Menu chính', 'codedeal'),
        'footer'  => __('Menu chân trang', 'codedeal'),
    ]);

    add_image_size('deal-card', 600, 400, true);
    add_image_size('deal-hero', 1200, 600, true);
});

/* ----------------------------------------------------------- *
 *  Assets
 * ----------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'codedeal-main',
        CODEDEAL_URI . '/assets/css/main.css',
        [],
        CODEDEAL_VERSION
    );
    wp_enqueue_script(
        'codedeal-main',
        CODEDEAL_URI . '/assets/js/main.js',
        [],
        CODEDEAL_VERSION,
        true
    );
});

/* ----------------------------------------------------------- *
 *  Helpers
 * ----------------------------------------------------------- */
function codedeal_format_price($value) {
    $value = (int) $value;
    if (!$value) return '';
    return number_format($value, 0, ',', '.') . 'đ';
}

function codedeal_discount_percent($old, $new) {
    $old = (int) $old;
    $new = (int) $new;
    if ($old <= 0 || $new <= 0 || $new >= $old) return 0;
    return (int) round(($old - $new) / $old * 100);
}

function codedeal_store_color($store_term) {
    if (!$store_term || is_wp_error($store_term)) return '#1E40AF';
    $color = get_term_meta($store_term->term_id, 'store_color', true);
    return $color ?: '#1E40AF';
}

function codedeal_get_first_store($post_id) {
    $terms = get_the_terms($post_id, 'store');
    if ($terms && !is_wp_error($terms)) return $terms[0];
    return null;
}

function codedeal_deal_image($post_id, $size = 'deal-card') {
    if (has_post_thumbnail($post_id)) return get_the_post_thumbnail_url($post_id, $size);
    $url = get_post_meta($post_id, '_cd_image_url', true);
    return $url ?: 'https://picsum.photos/seed/' . $post_id . '/600/400';
}

function codedeal_days_left($expires) {
    if (!$expires) return null;
    $now = current_time('timestamp');
    $end = strtotime($expires);
    if (!$end) return null;
    return (int) ceil(($end - $now) / DAY_IN_SECONDS);
}

/* ----------------------------------------------------------- *
 *  Body classes for layout polish
 * ----------------------------------------------------------- */
add_filter('body_class', function ($classes) {
    if (is_front_page()) $classes[] = 'cd-front-page';
    return $classes;
});

/* ----------------------------------------------------------- *
 *  Allow external image URL on the deal/coupon cards
 * ----------------------------------------------------------- */
add_filter('upload_mimes', function ($mimes) {
    return $mimes;
});
