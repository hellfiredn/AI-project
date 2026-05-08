<?php
/**
 * Custom Post Types & Taxonomies cho CodeDeal.
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {

    /* ---------- TAXONOMY: STORE ---------- */
    register_taxonomy('store', ['deal', 'coupon'], [
        'label'        => __('Cửa hàng', 'codedeal'),
        'labels'       => [
            'name'          => 'Cửa hàng',
            'singular_name' => 'Cửa hàng',
            'add_new_item'  => 'Thêm cửa hàng mới',
            'edit_item'     => 'Sửa cửa hàng',
            'menu_name'     => 'Cửa hàng',
        ],
        'public'       => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite'      => ['slug' => 'store', 'with_front' => false],
        'show_admin_column' => true,
    ]);

    /* ---------- POST TYPE: DEAL ---------- */
    register_post_type('deal', [
        'label'        => 'Deal',
        'labels'       => [
            'name'          => 'Deal',
            'singular_name' => 'Deal',
            'menu_name'     => 'Deal',
            'add_new_item'  => 'Thêm deal mới',
            'edit_item'     => 'Sửa deal',
            'all_items'     => 'Tất cả deal',
            'search_items'  => 'Tìm deal',
        ],
        'public'       => true,
        'show_in_rest' => true,
        'has_archive'  => 'deal',
        'rewrite'      => ['slug' => 'deal', 'with_front' => false],
        'menu_icon'    => 'dashicons-tag',
        'menu_position'=> 5,
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
        'taxonomies'   => ['category', 'post_tag', 'store'],
    ]);

    /* ---------- POST TYPE: COUPON ---------- */
    register_post_type('coupon', [
        'label'        => 'Coupon',
        'labels'       => [
            'name'          => 'Mã giảm giá',
            'singular_name' => 'Mã giảm giá',
            'menu_name'     => 'Coupon',
            'add_new_item'  => 'Thêm mã mới',
            'edit_item'     => 'Sửa mã',
            'all_items'     => 'Tất cả mã',
            'search_items'  => 'Tìm mã',
        ],
        'public'       => true,
        'show_in_rest' => true,
        'has_archive'  => 'coupon',
        'rewrite'      => ['slug' => 'coupon', 'with_front' => false],
        'menu_icon'    => 'dashicons-tickets-alt',
        'menu_position'=> 6,
        'supports'     => ['title', 'editor', 'thumbnail'],
        'taxonomies'   => ['category', 'store'],
    ]);
});
