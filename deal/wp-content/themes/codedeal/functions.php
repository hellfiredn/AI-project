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
require_once CODEDEAL_DIR . '/inc/acf-fields.php';
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
    if (is_front_page()) {
        wp_enqueue_style(
            'codedeal-swiper',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
            [],
            '11'
        );
        wp_enqueue_script(
            'codedeal-swiper',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
            [],
            '11',
            true
        );
    }

    wp_enqueue_style(
        'codedeal-main',
        CODEDEAL_URI . '/assets/css/main.css',
        [],
        CODEDEAL_VERSION
    );
    wp_enqueue_script(
        'codedeal-main',
        CODEDEAL_URI . '/assets/js/main.js',
        is_front_page() ? ['codedeal-swiper'] : [],
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

function codedeal_home_page_id() {
    $front_page_id = (int) get_option('page_on_front');
    return $front_page_id ?: (int) get_queried_object_id();
}

function codedeal_home_banner_fallback() {
    return [
        [
            'image_url' => CODEDEAL_URI . '/assets/brand/tgg-website-banner.png',
            'image_alt' => __('Tạp Hóa Giảm Giá - săn deal thật, tiết kiệm mỗi ngày', 'codedeal'),
            'link_url' => '',
            'target' => '_self',
        ],
    ];
}

function codedeal_normalize_home_banner($banner) {
    $image = $banner['cd_banner_image'] ?? null;
    $image_url = '';
    $image_alt = trim((string) ($banner['cd_banner_alt'] ?? ''));

    if (is_array($image)) {
        $image_url = $image['url'] ?? '';
        $image_alt = $image_alt ?: ($image['alt'] ?? '');
    } elseif (is_numeric($image)) {
        $image_url = wp_get_attachment_image_url((int) $image, 'full') ?: '';
        $image_alt = $image_alt ?: get_post_meta((int) $image, '_wp_attachment_image_alt', true);
    } elseif (is_string($image)) {
        $image_url = $image;
    }

    if (!$image_url) {
        return null;
    }

    return [
        'image_url' => $image_url,
        'image_alt' => $image_alt ?: get_bloginfo('name'),
        'link_url' => $banner['cd_banner_link'] ?? '',
        'target' => !empty($banner['cd_banner_open_new_tab']) ? '_blank' : '_self',
    ];
}

function codedeal_home_banners($post_id = 0) {
    $post_id = $post_id ?: codedeal_home_page_id();
    $rows = function_exists('get_field') ? get_field('cd_home_banners', $post_id) : [];

    if (!is_array($rows) || empty($rows)) {
        return codedeal_home_banner_fallback();
    }

    $banners = array_filter(array_map('codedeal_normalize_home_banner', $rows));
    return $banners ?: codedeal_home_banner_fallback();
}

function codedeal_is_primary_nav($args) {
    return !empty($args->theme_location) && 'primary' === $args->theme_location;
}

add_filter('nav_menu_css_class', function ($classes, $item, $args, $depth) {
    if (codedeal_is_primary_nav($args)) {
        $classes[] = 'nav-item';
    }

    return array_unique($classes);
}, 10, 4);

add_filter('nav_menu_link_attributes', function ($atts, $item, $args, $depth) {
    if (!codedeal_is_primary_nav($args)) {
        return $atts;
    }

    $classes = empty($atts['class']) ? [] : preg_split('/\s+/', $atts['class']);
    $classes[] = 'nav-link';

    if (in_array('current-menu-item', (array) $item->classes, true) || in_array('current-menu-ancestor', (array) $item->classes, true)) {
        $classes[] = 'active';
        $atts['aria-current'] = 'page';
    }

    $atts['class'] = implode(' ', array_unique(array_filter($classes)));

    return $atts;
}, 10, 4);

function codedeal_main_menu_object() {
    foreach (['main-menu', 'main menu', 'Main Menu'] as $menu_name) {
        $menu = wp_get_nav_menu_object($menu_name);
        if ($menu) {
            return $menu;
        }
    }

    return null;
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
