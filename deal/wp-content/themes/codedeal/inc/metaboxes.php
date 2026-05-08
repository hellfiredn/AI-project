<?php
/**
 * Metaboxes cho Deal & Coupon.
 */
if (!defined('ABSPATH')) exit;

/* ---------- DEAL METABOX ---------- */
add_action('add_meta_boxes', function () {
    add_meta_box('cd_deal_meta', 'Thông tin deal', 'cd_deal_meta_render', 'deal', 'normal', 'high');
    add_meta_box('cd_coupon_meta', 'Thông tin mã', 'cd_coupon_meta_render', 'coupon', 'normal', 'high');
    add_meta_box('cd_store_meta', 'Màu thương hiệu', 'cd_store_color_render', 'store', 'normal', 'default');
});

function cd_field($id, $label, $value, $type = 'text', $help = '') {
    $value = esc_attr($value);
    echo "<p><label style='display:block;font-weight:600;margin:14px 0 4px'>$label</label>";
    if ($type === 'textarea') {
        $clean = esc_textarea($value);
        echo "<textarea style='width:100%;min-height:60px' name='$id'>$clean</textarea>";
    } else {
        echo "<input type='$type' style='width:100%;padding:6px' name='$id' value='$value' />";
    }
    if ($help) echo "<small style='color:#888'>$help</small>";
    echo "</p>";
}

function cd_deal_meta_render($post) {
    wp_nonce_field('cd_save_deal', 'cd_deal_nonce');
    $price_old = get_post_meta($post->ID, '_cd_price_old', true);
    $price_new = get_post_meta($post->ID, '_cd_price_new', true);
    $url       = get_post_meta($post->ID, '_cd_affiliate_url', true);
    $expires   = get_post_meta($post->ID, '_cd_expires_at', true);
    $image     = get_post_meta($post->ID, '_cd_image_url', true);
    $featured  = get_post_meta($post->ID, '_cd_featured', true);
    cd_field('cd_price_old', 'Giá gốc (VNĐ)', $price_old, 'number');
    cd_field('cd_price_new', 'Giá deal (VNĐ)', $price_new, 'number');
    cd_field('cd_affiliate_url', 'Link affiliate (deeplink Accesstrade)', $url, 'url');
    cd_field('cd_expires_at', 'Hết hạn (YYYY-MM-DD)', $expires, 'text');
    cd_field('cd_image_url', 'Ảnh sản phẩm (URL)', $image, 'url', 'Không bắt buộc nếu đã chọn featured image.');
    $checked = $featured ? 'checked' : '';
    echo "<p><label><input type='checkbox' name='cd_featured' value='1' $checked /> Hiện ở banner trang chủ</label></p>";
}

function cd_coupon_meta_render($post) {
    wp_nonce_field('cd_save_coupon', 'cd_coupon_nonce');
    $code     = get_post_meta($post->ID, '_cd_code', true);
    $url      = get_post_meta($post->ID, '_cd_affiliate_url', true);
    $expires  = get_post_meta($post->ID, '_cd_expires_at', true);
    $min      = get_post_meta($post->ID, '_cd_min_order', true);
    cd_field('cd_code', 'Mã code', $code);
    cd_field('cd_affiliate_url', 'Link sàn/affiliate', $url, 'url');
    cd_field('cd_expires_at', 'Hết hạn (YYYY-MM-DD)', $expires);
    cd_field('cd_min_order', 'Đơn tối thiểu (VNĐ)', $min, 'number');
}

function cd_store_color_render($term) {
    $color = is_object($term) ? get_term_meta($term->term_id, 'store_color', true) : '';
    $color = esc_attr($color ?: '#1E40AF');
    echo '<table class="form-table"><tr>';
    echo '<th><label for="store_color">Màu chủ đạo</label></th>';
    echo "<td><input type='color' name='store_color' value='$color' /></td>";
    echo '</tr></table>';
}

/* ---------- SAVE ---------- */
add_action('save_post_deal', function ($post_id) {
    if (!isset($_POST['cd_deal_nonce']) || !wp_verify_nonce($_POST['cd_deal_nonce'], 'cd_save_deal')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    foreach (['cd_price_old', 'cd_price_new', 'cd_affiliate_url', 'cd_expires_at', 'cd_image_url'] as $f) {
        if (isset($_POST[$f])) update_post_meta($post_id, '_' . $f, sanitize_text_field($_POST[$f]));
    }
    update_post_meta($post_id, '_cd_featured', !empty($_POST['cd_featured']) ? '1' : '');
});

add_action('save_post_coupon', function ($post_id) {
    if (!isset($_POST['cd_coupon_nonce']) || !wp_verify_nonce($_POST['cd_coupon_nonce'], 'cd_save_coupon')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    foreach (['cd_code', 'cd_affiliate_url', 'cd_expires_at', 'cd_min_order'] as $f) {
        if (isset($_POST[$f])) update_post_meta($post_id, '_' . $f, sanitize_text_field($_POST[$f]));
    }
});

/* ---------- STORE term color save ---------- */
add_action('edited_store', 'cd_save_store_color');
add_action('create_store', 'cd_save_store_color');
function cd_save_store_color($term_id) {
    if (isset($_POST['store_color'])) {
        update_term_meta($term_id, 'store_color', sanitize_hex_color($_POST['store_color']) ?: '#1E40AF');
    }
}
