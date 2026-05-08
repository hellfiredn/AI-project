<?php
/**
 * Register all theme ACF blocks via block.json (Block API v3 / ACF 6+).
 * Each folder under /blocks/<slug>/ contains:
 *   - block.json   (block metadata + "acf" key with renderTemplate)
 *   - render.php   (server-side template using get_field())
 *
 * ACF field groups live in /acf-field/ (see includes/acf.php for JSON sync).
 * Each field group is tied to its block via location rule:
 *   param=block, value=totc/<slug>
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------- Custom block category "TOTC" ---------- */
add_filter( 'block_categories_all', function ( $categories ) {
    array_unshift( $categories, [
        'slug'  => 'totc',
        'title' => __( 'TOTC Sections', 'webwp' ),
        'icon'  => null,
    ] );
    return $categories;
} );

/* ---------- Auto-register every block under /blocks ---------- */
add_action( 'init', function () {
    $blocks_dir = get_stylesheet_directory() . '/blocks';
    if ( ! is_dir( $blocks_dir ) ) return;

    foreach ( glob( $blocks_dir . '/*/block.json' ) as $block_json ) {
        register_block_type( dirname( $block_json ) );
    }
} );

/* ---------- Render helpers used by block templates ---------- */
if ( ! function_exists( 'webwp_img' ) ) {
    function webwp_img( $filename ) {
        return get_stylesheet_directory_uri() . '/assets/images/' . ltrim( $filename, '/' );
    }
}
if ( ! function_exists( 'webwp_field' ) ) {
    function webwp_field( $key, $fallback = '' ) {
        $v = get_field( $key );
        return ( $v === null || $v === '' ) ? $fallback : $v;
    }
}
if ( ! function_exists( 'webwp_image_url' ) ) {
    /** Accept ACF image (array | id | url) + optional fallback filename from assets/images/. */
    function webwp_image_url( $img, $fallback_filename = '' ) {
        if ( is_array( $img ) && ! empty( $img['url'] ) ) return $img['url'];
        if ( is_numeric( $img ) ) {
            $u = wp_get_attachment_image_url( $img, 'large' );
            if ( $u ) return $u;
        }
        if ( is_string( $img ) && $img !== '' ) return $img;
        return $fallback_filename ? webwp_img( $fallback_filename ) : '';
    }
}
