<?php
/**
 * ACF fields for theme-managed pages.
 */
if (!defined('ABSPATH')) exit;

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_codedeal_home_banner_slider',
        'title' => __('Trang chủ - Banner slider', 'codedeal'),
        'fields' => [
            [
                'key' => 'field_codedeal_home_banners',
                'label' => __('Banner slider', 'codedeal'),
                'name' => 'cd_home_banners',
                'type' => 'repeater',
                'instructions' => __('Thêm một hoặc nhiều banner cho khu vực banner lớn trên trang chủ. Kích thước khuyến nghị: 1600 x 700px.', 'codedeal'),
                'required' => 0,
                'layout' => 'row',
                'button_label' => __('Thêm banner', 'codedeal'),
                'collapsed' => 'field_codedeal_home_banner_image',
                'sub_fields' => [
                    [
                        'key' => 'field_codedeal_home_banner_image',
                        'label' => __('Ảnh banner', 'codedeal'),
                        'name' => 'cd_banner_image',
                        'type' => 'image',
                        'instructions' => __('Nên dùng ảnh ngang 1600 x 700px để khớp layout hiện tại.', 'codedeal'),
                        'required' => 1,
                        'return_format' => 'array',
                        'preview_size' => 'medium',
                        'library' => 'all',
                        'parent_repeater' => 'field_codedeal_home_banners',
                    ],
                    [
                        'key' => 'field_codedeal_home_banner_link',
                        'label' => __('Link khi click', 'codedeal'),
                        'name' => 'cd_banner_link',
                        'type' => 'url',
                        'required' => 0,
                        'parent_repeater' => 'field_codedeal_home_banners',
                    ],
                    [
                        'key' => 'field_codedeal_home_banner_alt',
                        'label' => __('Alt text', 'codedeal'),
                        'name' => 'cd_banner_alt',
                        'type' => 'text',
                        'required' => 0,
                        'parent_repeater' => 'field_codedeal_home_banners',
                    ],
                    [
                        'key' => 'field_codedeal_home_banner_open_new_tab',
                        'label' => __('Mở link trong tab mới', 'codedeal'),
                        'name' => 'cd_banner_open_new_tab',
                        'type' => 'true_false',
                        'required' => 0,
                        'ui' => 1,
                        'default_value' => 0,
                        'parent_repeater' => 'field_codedeal_home_banners',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'page_type',
                    'operator' => '==',
                    'value' => 'front_page',
                ],
            ],
        ],
        'position' => 'acf_after_title',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
    ]);
});
