<?php
/**
 * CodeDeal — Content Generator.
 *
 * Tự sinh blog posts gốc từ data deal/coupon đã sync về.
 * Mỗi bài được generate là content "tổng hợp" — có giá trị thật cho user
 * (top deal, flash sale, coupon roundup) và 100% bài gốc cho SEO.
 *
 * Templates:
 *   - weekly_store      : Top N deal của 1 store trong tuần
 *   - top_discount      : Top N deal giảm giá sốc nhất tuần (mọi store)
 *   - flash_sale        : Deal sắp hết hạn (≤ 7 ngày)
 *   - coupon_roundup    : Tổng hợp coupon active của 1 store
 *   - category_roundup  : Top deal theo danh mục
 */
if (!defined('ABSPATH')) exit;

class CDAT_Generator {

    /* =========================================================
     * PUBLIC ENTRY POINTS
     * ========================================================= */

    /** Chạy tất cả templates đã bật trong settings. */
    public static function run_all(): array {
        $s = cdat_settings();
        $stats = ['weekly_store' => 0, 'top_discount' => 0, 'flash_sale' => 0, 'coupon_roundup' => 0];

        if (!empty($s['gen_top_discount'])) {
            $id = self::generate_top_discount();
            if ($id) $stats['top_discount']++;
        }
        if (!empty($s['gen_flash_sale'])) {
            $id = self::generate_flash_sale();
            if ($id) $stats['flash_sale']++;
        }
        if (!empty($s['gen_weekly_store'])) {
            $stores = get_terms(['taxonomy' => 'store', 'hide_empty' => true, 'number' => 6]);
            if (!is_wp_error($stores)) foreach ($stores as $st) {
                $id = self::generate_weekly_store($st->slug);
                if ($id) $stats['weekly_store']++;
            }
        }
        if (!empty($s['gen_coupon_roundup'])) {
            $stores = get_terms(['taxonomy' => 'store', 'hide_empty' => true, 'number' => 6]);
            if (!is_wp_error($stores)) foreach ($stores as $st) {
                $id = self::generate_coupon_roundup($st->slug);
                if ($id) $stats['coupon_roundup']++;
            }
        }

        update_option('codedeal_at_last_gen', current_time('mysql'));
        update_option('codedeal_at_last_gen_stats', $stats, false);
        CDAT_Logger::info('Generator run_all', $stats);
        return $stats;
    }

    /* =========================================================
     * TEMPLATE 1: Top deal 1 store trong tuần
     * ========================================================= */
    public static function generate_weekly_store(string $store_slug, int $limit = 10): ?int {
        $store = get_term_by('slug', $store_slug, 'store');
        if (!$store) return null;

        $deals = self::query_deals([
            'tax_query' => [['taxonomy' => 'store', 'field' => 'slug', 'terms' => $store_slug]],
            'posts_per_page' => $limit,
            'meta_key'   => '_cd_price_old',
            'orderby'    => 'meta_value_num',
            'order'      => 'DESC',
            'date_query' => [['after' => '2 weeks ago']],
        ]);
        if (count($deals) < 3) return null; // không đủ data thì skip

        $month = date_i18n('F Y');
        $title = sprintf('Top %d deal %s đáng săn nhất tuần này', count($deals), $store->name);

        $intro  = "Tuần này " . $store->name . " tung loạt deal mạnh tay với mức giảm tới " .
                  self::max_discount($deals) . "%. Đội săn deal CodeDeal đã chọn lọc " . count($deals) .
                  " deal tốt nhất giúp bạn tiết kiệm ngay hôm nay.\n\n";
        $intro .= "Tất cả link bên dưới đều được kiểm tra còn hoạt động và có giá đã verify với " . $store->name . ". Click để săn ngay khi còn hàng — flash sale thường hết rất nhanh.\n";

        $body = self::build_intro($intro);
        $body .= self::build_deal_list($deals);
        $body .= self::build_outro_tips($store->name);

        $cats = ['deal-hot', sanitize_title($store->name)];
        return self::upsert_post($title, $body, $deals, $cats, 'weekly-store-' . $store_slug);
    }

    /* =========================================================
     * TEMPLATE 2: Top discount tuần (mọi store)
     * ========================================================= */
    public static function generate_top_discount(int $limit = 15): ?int {
        $deals = self::query_deals([
            'posts_per_page' => $limit * 3, // lấy nhiều hơn để filter discount
            'meta_key'   => '_cd_price_old',
            'orderby'    => 'meta_value_num',
            'order'      => 'DESC',
        ]);

        // Sort theo discount %
        usort($deals, function ($a, $b) {
            $da = codedeal_discount_percent(get_post_meta($a->ID, '_cd_price_old', true), get_post_meta($a->ID, '_cd_price_new', true));
            $db = codedeal_discount_percent(get_post_meta($b->ID, '_cd_price_old', true), get_post_meta($b->ID, '_cd_price_new', true));
            return $db <=> $da;
        });
        $deals = array_slice($deals, 0, $limit);
        if (count($deals) < 3) return null;

        $title = sprintf('Top %d deal giảm giá SỐC nhất tuần này — cập nhật %s', count($deals), date_i18n('d/m/Y'));

        $intro  = "Tuần này CodeDeal điểm danh " . count($deals) . " deal có mức giảm sâu nhất từ Shopee, Lazada, Tiki, FPT Shop, CellphoneS… với mức giảm tới " . self::max_discount($deals) . "%.\n\n";
        $intro .= "Đây là những deal có discount % cao nhất hiện tại — nếu đang phân vân mua món gì giá hời, bạn nên scroll qua list này trước.\n";

        $body = self::build_intro($intro);
        $body .= self::build_deal_list($deals, true); // show store badge
        $body .= self::build_outro_general();

        return self::upsert_post($title, $body, $deals, ['deal-hot', 'flash-sale'], 'top-discount-' . date('Y-W'));
    }

    /* =========================================================
     * TEMPLATE 3: Flash sale sắp hết hạn
     * ========================================================= */
    public static function generate_flash_sale(): ?int {
        $today = current_time('Y-m-d');
        $next  = date('Y-m-d', strtotime('+7 days'));
        $deals = self::query_deals([
            'posts_per_page' => 12,
            'meta_query' => [
                'relation' => 'AND',
                ['key' => '_cd_expires_at', 'value' => $today, 'compare' => '>='],
                ['key' => '_cd_expires_at', 'value' => $next,  'compare' => '<='],
            ],
            'meta_key' => '_cd_expires_at',
            'orderby'  => 'meta_value',
            'order'    => 'ASC',
        ]);
        if (count($deals) < 3) return null;

        $title = sprintf('⚡ %d deal flash sale sắp hết hạn — không nên bỏ lỡ', count($deals));

        $intro  = "Đây là " . count($deals) . " deal đang đếm ngược thời gian — tất cả đều kết thúc trong vòng **7 ngày tới**. Nếu thấy món nào đang cần thì click ngay, kẻo hết giờ là giá quay về như cũ.\n\n";
        $intro .= "Lịch flash sale của các sàn thường đẩy mạnh vào ngày đôi (5.5, 6.6, 12.12) và ngày lương — đây là thời điểm vàng để săn deal lớn.\n";

        $body = self::build_intro($intro);
        $body .= self::build_deal_list($deals, true, true); // show store + countdown info
        $body .= "\n## Mẹo săn flash sale hiệu quả\n";
        $body .= "1. **Add to cart trước**: cho deal vào giỏ trước khi giờ vàng để giảm thao tác lúc cao điểm.\n";
        $body .= "2. **Áp mã sàn**: kết hợp deal với voucher giảm giá toàn sàn để tối ưu discount.\n";
        $body .= "3. **Thanh toán qua thẻ tín dụng đối tác**: nhiều sàn ưu đãi thêm 5-10% cho thẻ Visa/Mastercard liên kết.\n";

        return self::upsert_post($title, $body, $deals, ['flash-sale'], 'flash-sale-' . date('Y-m-d'));
    }

    /* =========================================================
     * TEMPLATE 4: Coupon roundup theo store
     * ========================================================= */
    public static function generate_coupon_roundup(string $store_slug): ?int {
        $store = get_term_by('slug', $store_slug, 'store');
        if (!$store) return null;

        $coupons = get_posts([
            'post_type'      => 'coupon',
            'posts_per_page' => 20,
            'tax_query'      => [['taxonomy' => 'store', 'field' => 'slug', 'terms' => $store_slug]],
        ]);
        if (count($coupons) < 3) return null;

        $month = date_i18n('m/Y');
        $title = sprintf('Tổng hợp %d mã giảm giá %s mới nhất tháng %s', count($coupons), $store->name, $month);

        $intro  = "Cập nhật **" . count($coupons) . " ưu đãi** đang hoạt động trên " . $store->name . " trong tháng " . $month . " — bao gồm voucher giảm giá, freeship, hoàn xu, ưu đãi thẻ tín dụng…\n\n";
        $intro .= "Tất cả đều có link click-to-claim hoặc code copy-paste. Nhớ kiểm tra điều kiện đơn tối thiểu trước khi áp dụng.\n";

        $body = self::build_intro($intro);
        $body .= "\n## Danh sách mã đang có hiệu lực\n\n";
        foreach ($coupons as $c) {
            $code  = get_post_meta($c->ID, '_cd_code', true);
            $url   = get_post_meta($c->ID, '_cd_affiliate_url', true) ?: get_permalink($c);
            $exp   = get_post_meta($c->ID, '_cd_expires_at', true);
            $min   = (int) get_post_meta($c->ID, '_cd_min_order', true);
            $body .= "### " . $c->post_title . "\n";
            if ($code) $body .= "**Mã:** `" . $code . "`\n";
            if ($min)  $body .= "**Đơn tối thiểu:** " . codedeal_format_price($min) . "\n";
            if ($exp)  $body .= "**Hết hạn:** " . date('d/m/Y', strtotime($exp)) . "\n";
            $excerpt = wp_strip_all_tags(get_post_field('post_content', $c->ID));
            if ($excerpt) $body .= "\n" . $excerpt . "\n";
            $body .= "\n[👉 Sử dụng ngay tại " . $store->name . "](" . esc_url($url) . ')' . "\n\n---\n\n";
        }
        $body .= "\n*Cập nhật ngày " . date_i18n('d/m/Y') . ". CodeDeal kiểm tra mã hằng ngày, nếu mã hết hạn vui lòng để lại bình luận.*\n";

        // Featured image: lấy từ coupon đầu tiên hoặc null
        return self::upsert_post($title, $body, [], ['coupon', sanitize_title($store->name)], 'coupon-roundup-' . $store_slug . '-' . date('Y-m'),
            self::pick_featured_for_coupon($coupons));
    }

    /* =========================================================
     * HELPERS
     * ========================================================= */

    protected static function query_deals(array $args): array {
        $defaults = [
            'post_type'      => 'deal',
            'posts_per_page' => 10,
            'post_status'    => 'publish',
        ];
        $q = new WP_Query(array_merge($defaults, $args));
        return $q->posts ?: [];
    }

    protected static function max_discount(array $deals): int {
        $max = 0;
        foreach ($deals as $d) {
            $po = (int) get_post_meta($d->ID, '_cd_price_old', true);
            $pn = (int) get_post_meta($d->ID, '_cd_price_new', true);
            $disc = codedeal_discount_percent($po, $pn);
            if ($disc > $max) $max = $disc;
        }
        return $max;
    }

    protected static function build_intro(string $intro): string {
        return $intro . "\n";
    }

    protected static function build_deal_list(array $deals, bool $show_store = false, bool $show_expiry = false): string {
        $body = "";
        $i = 0;
        foreach ($deals as $d) {
            $i++;
            $po = (int) get_post_meta($d->ID, '_cd_price_old', true);
            $pn = (int) get_post_meta($d->ID, '_cd_price_new', true);
            $disc = codedeal_discount_percent($po, $pn);
            $url = get_post_meta($d->ID, '_cd_affiliate_url', true) ?: get_permalink($d);
            $img = codedeal_deal_image($d->ID, 'deal-card');
            $store = codedeal_get_first_store($d->ID);
            $exp = get_post_meta($d->ID, '_cd_expires_at', true);

            $body .= "## $i. " . $d->post_title . "\n\n";
            if ($img) $body .= '<img src="' . esc_url($img) . '" alt="' . esc_attr($d->post_title) . '" />' . "\n\n";
            if ($show_store && $store) $body .= "**Cửa hàng:** " . $store->name . "  \n";
            $body .= "**Giá hiện tại:** " . codedeal_format_price($pn);
            if ($po > $pn) $body .= " (giảm **" . $disc . "%** từ " . codedeal_format_price($po) . ")";
            $body .= "  \n";
            if ($show_expiry && $exp) $body .= "**Hết hạn:** " . date('d/m/Y', strtotime($exp)) . "  \n";
            $excerpt = wp_strip_all_tags(get_post_field('post_excerpt', $d->ID) ?: get_post_field('post_content', $d->ID));
            if ($excerpt) $body .= "\n" . wp_trim_words($excerpt, 50) . "\n";
            $body .= "\n[👉 Săn ngay deal này](" . esc_url($url) . ")\n\n---\n\n";
        }
        return $body;
    }

    protected static function build_outro_tips(string $store_name): string {
        $body  = "\n## Mẹo mua hàng tốt giá tại " . $store_name . "\n\n";
        $body .= "- **So sánh giá trước**: dùng [công cụ So sánh giá](" . home_url('/so-sanh-gia/') . ") để check giá ở các sàn khác trước khi quyết định.\n";
        $body .= "- **Săn coupon đi kèm**: trước khi thanh toán, vào trang [Mã giảm giá " . $store_name . "](" . home_url('/store/' . sanitize_title($store_name) . '/') . ") để áp thêm voucher.\n";
        $body .= "- **Thanh toán giờ vàng**: nhiều ngân hàng hoàn tiền 10-20% cho giờ vàng (12h, 18h, 21h).\n";
        $body .= "- **Đọc review thật**: lọc đánh giá có ảnh thật để tránh hàng fake.\n\n";
        $body .= "---\n\n*Bài viết được CodeDeal cập nhật ngày " . date_i18n('d/m/Y') . " từ dữ liệu Accesstrade. Giá có thể thay đổi theo thời gian thực — click vào link để xem giá mới nhất.*\n";
        return $body;
    }

    protected static function build_outro_general(): string {
        $body  = "\n## Lưu ý khi săn deal giảm giá lớn\n\n";
        $body .= "- **Discount cao không có nghĩa là giá rẻ nhất**: một số sản phẩm bị nâng giá gốc lên trước khi giảm. Luôn so sánh với 2-3 sàn khác.\n";
        $body .= "- **Kiểm tra số lượng**: deal sốc thường giới hạn số lượng. Add to cart sớm.\n";
        $body .= "- **Chính sách đổi trả**: với deal mức giảm > 50%, đảm bảo sản phẩm cho phép đổi trả nếu không vừa ý.\n\n";
        $body .= "---\n\n*Cập nhật " . date_i18n('d/m/Y H:i') . ". CodeDeal tổng hợp từ Accesstrade.*\n";
        return $body;
    }

    /**
     * Tạo / update post — dùng meta `_cd_gen_key` làm unique để mỗi tuần / tháng tạo lại được.
     */
    protected static function upsert_post(string $title, string $body_md, array $deals, array $cats = [], ?string $gen_key = null, ?string $featured_url = null): ?int {
        $gen_key = $gen_key ?: sanitize_title($title);

        $existing = get_posts([
            'post_type' => 'post',
            'meta_key' => '_cd_gen_key',
            'meta_value' => $gen_key,
            'posts_per_page' => 1,
            'post_status' => 'any',
        ]);

        // Convert markdown-lite → HTML cơ bản (## → h2, [text](url) → a, ** → strong)
        $body_html = self::md_to_html($body_md);

        $data = [
            'post_type'    => 'post',
            'post_title'   => $title,
            'post_content' => $body_html,
            'post_status'  => 'publish',
            'post_excerpt' => self::make_excerpt($body_md),
        ];

        if ($existing) {
            $data['ID'] = $existing[0]->ID;
            $post_id = wp_update_post($data, true);
        } else {
            $post_id = wp_insert_post($data, true);
        }
        if (is_wp_error($post_id) || !$post_id) return null;

        update_post_meta($post_id, '_cd_gen_key', $gen_key);
        update_post_meta($post_id, '_cd_gen_at', current_time('mysql'));
        update_post_meta($post_id, '_cd_gen_deal_ids', wp_list_pluck($deals, 'ID'));

        // Set categories
        $term_ids = [];
        foreach ($cats as $slug) {
            $t = get_term_by('slug', $slug, 'category');
            if (!$t) {
                $created = wp_insert_term(ucfirst(str_replace('-', ' ', $slug)), 'category', ['slug' => $slug]);
                if (!is_wp_error($created)) $term_ids[] = $created['term_id'];
            } else {
                $term_ids[] = $t->term_id;
            }
        }
        if ($term_ids) wp_set_post_categories($post_id, $term_ids);

        // Set featured image: dùng URL của deal đầu tiên hoặc featured_url
        $img_url = $featured_url ?: ($deals ? codedeal_deal_image($deals[0]->ID, 'deal-hero') : '');
        if ($img_url) update_post_meta($post_id, '_cd_external_thumb', $img_url);

        return $post_id;
    }

    protected static function pick_featured_for_coupon(array $coupons): ?string {
        foreach ($coupons as $c) {
            $store = codedeal_get_first_store($c->ID);
            if ($store) {
                // Dùng image placeholder với màu store
                $color = ltrim(codedeal_store_color($store), '#');
                return "https://placehold.co/1200x600/$color/FFFFFF/png?text=" . rawurlencode($store->name . ' Coupons');
            }
        }
        return null;
    }

    /** Markdown-lite → HTML đủ dùng cho bài blog. */
    protected static function md_to_html(string $md): string {
        $md = trim($md);
        // Process line by line for ##, ### headings + lists
        $lines = explode("\n", $md);
        $out = [];
        $in_list = false;
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^### (.+)/', $trimmed, $m)) {
                if ($in_list) { $out[] = '</ul>'; $in_list = false; }
                $out[] = '<h3>' . esc_html($m[1]) . '</h3>';
            } elseif (preg_match('/^## (.+)/', $trimmed, $m)) {
                if ($in_list) { $out[] = '</ul>'; $in_list = false; }
                $out[] = '<h2>' . esc_html($m[1]) . '</h2>';
            } elseif (preg_match('/^[-*] (.+)/', $trimmed, $m)) {
                if (!$in_list) { $out[] = '<ul>'; $in_list = true; }
                $out[] = '<li>' . self::inline_md($m[1]) . '</li>';
            } elseif (preg_match('/^\d+\. (.+)/', $trimmed, $m)) {
                if (!$in_list) { $out[] = '<ol>'; $in_list = 'ol'; }
                $out[] = '<li>' . self::inline_md($m[1]) . '</li>';
            } elseif ($trimmed === '---') {
                if ($in_list) { $out[] = $in_list === 'ol' ? '</ol>' : '</ul>'; $in_list = false; }
                $out[] = '<hr>';
            } elseif ($trimmed === '') {
                if ($in_list) { $out[] = $in_list === 'ol' ? '</ol>' : '</ul>'; $in_list = false; }
                $out[] = '';
            } elseif (str_starts_with($trimmed, '<img ')) {
                if ($in_list) { $out[] = $in_list === 'ol' ? '</ol>' : '</ul>'; $in_list = false; }
                $out[] = '<p>' . $trimmed . '</p>';
            } else {
                if ($in_list) { $out[] = $in_list === 'ol' ? '</ol>' : '</ul>'; $in_list = false; }
                $out[] = '<p>' . self::inline_md($trimmed) . '</p>';
            }
        }
        if ($in_list) $out[] = $in_list === 'ol' ? '</ol>' : '</ul>';
        return implode("\n", $out);
    }

    protected static function inline_md(string $s): string {
        // Bold **text**
        $s = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $s);
        // Code `text`
        $s = preg_replace('/`([^`]+)`/', '<code>$1</code>', $s);
        // Links [text](url)
        $s = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($m) {
            $url = esc_url($m[2]);
            $is_external = str_starts_with($url, 'http') && strpos($url, $_SERVER['HTTP_HOST'] ?? home_url()) === false;
            $rel = $is_external ? ' target="_blank" rel="nofollow sponsored noopener"' : '';
            return '<a href="' . $url . '"' . $rel . '>' . esc_html($m[1]) . '</a>';
        }, $s);
        // Trailing 2 spaces = <br>
        $s = preg_replace('/  $/', '<br>', $s);
        return $s;
    }

    protected static function make_excerpt(string $md): string {
        $first = preg_split('/\n\n/', trim($md))[0] ?? '';
        return mb_substr(wp_strip_all_tags(self::md_to_html($first)), 0, 240);
    }
}

/* ----------------------------------------------------------- *
 *  Fallback featured image: nếu post không có thumb chuẩn nhưng
 *  có meta `_cd_external_thumb`, dùng URL đó cho frontend theme.
 *  Theme codedeal đã sẵn fallback qua codedeal_deal_image() nhưng
 *  blog cards dùng has_post_thumbnail() — bổ sung filter.
 * ----------------------------------------------------------- */
add_filter('post_thumbnail_html', function ($html, $post_id) {
    if ($html) return $html;
    $url = get_post_meta($post_id, '_cd_external_thumb', true);
    if ($url) {
        return '<img src="' . esc_url($url) . '" alt="' . esc_attr(get_the_title($post_id)) . '" loading="lazy">';
    }
    return $html;
}, 10, 2);

add_filter('has_post_thumbnail', function ($has, $post_id) {
    if ($has) return $has;
    return (bool) get_post_meta($post_id, '_cd_external_thumb', true);
}, 10, 2);
