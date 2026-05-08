<?php
/**
 * Map dữ liệu Accesstrade → CPT Deal/Coupon + taxonomy Store.
 */
if (!defined('ABSPATH')) exit;

class CDAT_Mapper {

    /** Lấy term Store từ tên/domain — tạo mới nếu chưa có. */
    public static function ensure_store(string $name, string $color = ''): ?int {
        $name = trim($name);
        if ($name === '') return null;
        $slug = sanitize_title($name);

        // Tránh duplicate dạng www / sub
        $slug = preg_replace('#^www-#', '', $slug);

        $term = get_term_by('slug', $slug, 'store');
        if ($term) return (int) $term->term_id;

        $created = wp_insert_term($name, 'store', ['slug' => $slug]);
        if (is_wp_error($created)) return null;

        $term_id = $created['term_id'];
        if ($color) update_term_meta($term_id, 'store_color', $color);
        return $term_id;
    }

    /**
     * Tạo / update 1 deal từ dữ liệu top_products | datafeeds | offers.
     * @param array $row Một row trả về từ API
     * @param array $ctx [
     *   'campaign_name' => string,
     *   'auto_publish'  => bool,
     *   'featured'      => bool,           // Force featured (manual override)
     *   'source'        => string,         // 'top_products' | 'datafeed' | 'offers'
     *   'featured_min_discount' => int,    // Auto-feature nếu discount % ≥ giá trị này (0 = tắt)
     * ]
     * @return int|null post_id
     */
    public static function upsert_deal(array $row, array $ctx = []): ?int {
        $title = trim((string) ($row['name'] ?? $row['title'] ?? ''));
        if ($title === '') return null;

        $product_id = (string) ($row['product_id'] ?? $row['id'] ?? md5($title . ($row['url'] ?? '')));
        $existing = get_posts([
            'post_type'   => 'deal',
            'meta_key'    => '_cd_at_uid',
            'meta_value'  => $product_id,
            'posts_per_page' => 1,
            'post_status' => 'any',
        ]);

        $post_data = [
            'post_type'    => 'deal',
            'post_title'   => $title,
            'post_status'  => !empty($ctx['auto_publish']) ? 'publish' : 'draft',
            'post_content' => (string) ($row['short_desc'] ?? $row['description'] ?? ''),
            'post_excerpt' => mb_substr((string) ($row['short_desc'] ?? ''), 0, 220),
        ];

        if ($existing) {
            $post_data['ID'] = $existing[0]->ID;
            $post_id = wp_update_post($post_data, true);
        } else {
            $post_id = wp_insert_post($post_data, true);
        }
        if (is_wp_error($post_id) || !$post_id) return null;

        $price_old = (int) self::pluck_number($row, ['price_old', 'original_price', 'list_price', 'price_before']);
        $price_new = (int) self::pluck_number($row, ['price', 'sale_price', 'discount_price', 'price_after']);
        $url       = (string) ($row['aff_link'] ?? $row['url'] ?? '');
        $image     = (string) ($row['image'] ?? '');
        $store     = trim((string) ($row['domain'] ?? $row['merchant'] ?? $row['campaign'] ?? $ctx['campaign_name'] ?? ''));
        $expires   = (string) ($row['discount_to'] ?? $row['end_time'] ?? $row['expires_at'] ?? '');
        $expires_norm = $expires ? date('Y-m-d', strtotime($expires)) : '';

        update_post_meta($post_id, '_cd_at_uid', $product_id);
        update_post_meta($post_id, '_cd_at_raw', wp_slash(wp_json_encode($row, JSON_UNESCAPED_UNICODE)));
        update_post_meta($post_id, '_cd_price_old', $price_old);
        update_post_meta($post_id, '_cd_price_new', $price_new);
        update_post_meta($post_id, '_cd_affiliate_url', $url);
        update_post_meta($post_id, '_cd_image_url', $image);
        update_post_meta($post_id, '_cd_expires_at', $expires_norm);

        // Lưu nguồn gốc (top_products | datafeed | offers) — dùng để query "Top bán chạy" trên homepage.
        $source = (string) ($ctx['source'] ?? '');
        if ($source !== '') update_post_meta($post_id, '_cd_at_source', $source);

        // Quy tắc auto-flag _cd_featured:
        //   1. ctx['featured'] = true → force flag (giữ tương thích cũ)
        //   2. source = 'top_products' → đây là sản phẩm bán chạy do Accesstrade certify
        //   3. discount % ≥ threshold (mặc định 50%) → deal giảm sâu, đáng nổi bật
        $should_feature = !empty($ctx['featured']) || $source === 'top_products';
        if (!$should_feature) {
            $threshold = (int) ($ctx['featured_min_discount'] ?? 0);
            if ($threshold > 0 && $price_old > 0 && $price_new > 0 && $price_new < $price_old) {
                $disc = (int) round((($price_old - $price_new) / $price_old) * 100);
                if ($disc >= $threshold) $should_feature = true;
            }
        }
        if ($should_feature) update_post_meta($post_id, '_cd_featured', '1');

        if ($store) {
            $store_id = self::ensure_store($store);
            if ($store_id) wp_set_object_terms($post_id, [$store_id], 'store');
        }

        // Optional: gán category nếu có
        $cat_name = (string) ($row['category_name'] ?? $row['cate'] ?? '');
        if ($cat_name) {
            $cat = term_exists($cat_name, 'category');
            if (!$cat) $cat = wp_insert_term($cat_name, 'category');
            if (!is_wp_error($cat)) wp_set_object_terms($post_id, [(int) $cat['term_id']], 'category');
        }

        return $post_id;
    }

    /**
     * Xử lý 1 row từ /v1/offers_informations.
     * Accesstrade trả về 2 dạng:
     *   1. Promotion có nested coupons[] array → tạo N coupon (1/code)
     *   2. Promotion không có code (click-to-claim) → tạo 1 coupon với code rỗng
     *
     * @return int[]  Mảng post ID đã upsert
     */
    public static function upsert_coupon_or_iterate(array $row, array $ctx = []): array {
        $ids = [];

        // Pattern 1: nested coupons array
        if (isset($row['coupons']) && is_array($row['coupons']) && !empty($row['coupons'])) {
            foreach ($row['coupons'] as $idx => $coupon_obj) {
                if (!is_array($coupon_obj)) {
                    // string đơn lẻ
                    $coupon_obj = ['code' => (string) $coupon_obj];
                }
                $merged = array_merge($row, $coupon_obj);
                unset($merged['coupons']);
                // Đảm bảo unique uid khi cùng promotion có nhiều code
                $merged['_uid_suffix'] = '-c' . $idx;
                $id = self::upsert_coupon($merged, $ctx);
                if ($id) $ids[] = $id;
            }
            return $ids;
        }

        // Pattern 2: single coupon at root or click-to-claim
        $id = self::upsert_coupon($row, $ctx);
        if ($id) $ids[] = $id;
        return $ids;
    }

    /**
     * Tạo / update 1 coupon từ 1 row data (đã được normalize).
     */
    public static function upsert_coupon(array $row, array $ctx = []): ?int {
        $title = trim((string) ($row['name'] ?? $row['title'] ?? $row['coupon_name'] ?? ''));
        if ($title === '') return null;

        // Accesstrade có nhiều tên field cho mã code tuỳ endpoint/nested object:
        $code = (string) (
            $row['coupon_code']   ??
            $row['voucher_code']  ??
            $row['promotion_code']??
            $row['coupon']        ??
            $row['code']          ??
            ''
        );
        // Click-to-claim không có code → vẫn lưu để frontend hiện nút "Nhận ưu đãi"
        $uid  = (string) ($row['id'] ?? $row['promotion_id'] ?? md5($title . $code))
              . ($row['_uid_suffix'] ?? '');

        $existing = get_posts([
            'post_type'   => 'coupon',
            'meta_key'    => '_cd_at_uid',
            'meta_value'  => $uid,
            'posts_per_page' => 1,
            'post_status' => 'any',
        ]);

        $post_data = [
            'post_type'   => 'coupon',
            'post_title'  => $title,
            'post_status' => !empty($ctx['auto_publish']) ? 'publish' : 'draft',
            'post_content'=> (string) ($row['content'] ?? $row['description'] ?? ''),
        ];

        if ($existing) {
            $post_data['ID'] = $existing[0]->ID;
            $post_id = wp_update_post($post_data, true);
        } else {
            $post_id = wp_insert_post($post_data, true);
        }
        if (is_wp_error($post_id) || !$post_id) return null;

        $url     = (string) ($row['aff_link'] ?? $row['link'] ?? $row['url'] ?? '');
        $expires = (string) ($row['end_time'] ?? $row['expires_at'] ?? '');
        $expires_norm = $expires ? date('Y-m-d', strtotime($expires)) : '';
        $min     = (int) self::pluck_number($row, ['min_order', 'minimum', 'min_value']);
        $store   = trim((string) ($row['merchant'] ?? $row['campaign'] ?? $ctx['campaign_name'] ?? ''));

        update_post_meta($post_id, '_cd_at_uid', $uid);
        update_post_meta($post_id, '_cd_at_raw', wp_slash(wp_json_encode($row, JSON_UNESCAPED_UNICODE)));
        update_post_meta($post_id, '_cd_code', $code);
        update_post_meta($post_id, '_cd_affiliate_url', $url);
        update_post_meta($post_id, '_cd_expires_at', $expires_norm);
        update_post_meta($post_id, '_cd_min_order', $min);

        if ($store) {
            $store_id = self::ensure_store($store);
            if ($store_id) wp_set_object_terms($post_id, [$store_id], 'store');
        }

        return $post_id;
    }

    protected static function pluck_number(array $row, array $keys): int {
        foreach ($keys as $k) {
            if (isset($row[$k])) {
                $v = (string) $row[$k];
                $v = preg_replace('/[^\d]/', '', $v);
                if ($v !== '') return (int) $v;
            }
        }
        return 0;
    }
}
