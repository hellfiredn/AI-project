<?php
/**
 * Admin settings page.
 */
if (!defined('ABSPATH')) exit;

class CDAT_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'register']);
        add_action('admin_post_cdat_sync_now', [$this, 'handle_sync_now']);
        add_action('admin_post_cdat_test_token', [$this, 'handle_test_token']);
        add_action('admin_post_cdat_clear_logs', [$this, 'handle_clear_logs']);
        add_action('admin_post_cdat_probe', [$this, 'handle_probe']);
        add_action('admin_post_cdat_generate', [$this, 'handle_generate']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue($hook) {
        if (strpos((string) $hook, 'codedeal-at') === false) return;
        wp_enqueue_style('cdat-admin', CDAT_URL . 'assets/css/admin.css', [], CDAT_VERSION);
    }

    public function menu() {
        add_menu_page(
            'Accesstrade Sync',
            'Accesstrade',
            'manage_options',
            'codedeal-at',
            [$this, 'page'],
            'dashicons-update',
            58
        );
    }

    public function register() {
        register_setting('cdat_group', CDAT_OPT_KEY, [
            'sanitize_callback' => [$this, 'sanitize'],
        ]);
    }

    public function sanitize($input) {
        // Bắt đầu từ giá trị HIỆN CÓ trong DB (không phải defaults), để các field
        // không có trong $input vẫn giữ nguyên. Tránh xoá token khi save form khác.
        $clean = cdat_settings();
        $section = sanitize_text_field($input['_form_section'] ?? 'api');

        if ($section === 'api') {
            // Form chính: API + sync settings
            if (isset($input['api_base'])) $clean['api_base'] = trim((string) $input['api_base']);
            if (isset($input['token']))    $clean['token']    = trim((string) $input['token']);
            if (isset($input['sub_id']))   $clean['sub_id']   = trim((string) $input['sub_id']);
            $clean['campaigns'] = isset($input['campaigns']) && is_array($input['campaigns'])
                ? array_values(array_filter(array_map('sanitize_text_field', $input['campaigns'])))
                : [];
            $clean['sync_coupons']    = !empty($input['sync_coupons']) ? 1 : 0;
            $clean['sync_top']        = !empty($input['sync_top']) ? 1 : 0;
            $clean['sync_datafeed']   = !empty($input['sync_datafeed']) ? 1 : 0;
            $clean['sync_performance']= !empty($input['sync_performance']) ? 1 : 0;
            $clean['datafeed_limit']  = max(10, min(50, (int) ($input['datafeed_limit'] ?? 50)));
            $clean['top_orders_threshold']    = max(1, min(100, (int) ($input['top_orders_threshold'] ?? 3)));
            $clean['performance_window_days'] = max(1, min(180, (int) ($input['performance_window_days'] ?? 30)));
            $clean['cron_interval']  = in_array($input['cron_interval'] ?? '', ['cdat_15min', 'cdat_30min', 'hourly', 'cdat_4h', 'twicedaily', 'daily'])
                ? $input['cron_interval'] : 'hourly';
            $clean['auto_publish']   = !empty($input['auto_publish']) ? 1 : 0;
            $clean['feature_top']    = !empty($input['feature_top']) ? 1 : 0;
            $clean['featured_min_discount'] = max(0, min(99, (int) ($input['featured_min_discount'] ?? 0)));
        } elseif ($section === 'generator') {
            // Form generator: chỉ update gen_* fields
            $clean['gen_enabled']        = !empty($input['gen_enabled']) ? 1 : 0;
            $clean['gen_top_discount']   = !empty($input['gen_top_discount']) ? 1 : 0;
            $clean['gen_flash_sale']     = !empty($input['gen_flash_sale']) ? 1 : 0;
            $clean['gen_weekly_store']   = !empty($input['gen_weekly_store']) ? 1 : 0;
            $clean['gen_coupon_roundup'] = !empty($input['gen_coupon_roundup']) ? 1 : 0;
            $clean['gen_interval']       = in_array($input['gen_interval'] ?? '', ['daily', 'twicedaily', 'weekly'])
                ? $input['gen_interval'] : 'weekly';
        }
        return $clean;
    }

    public function handle_sync_now() {
        check_admin_referer('cdat_sync_now');
        if (!current_user_can('manage_options')) wp_die('Forbidden');
        $type = $_POST['type'] ?? 'all';
        switch ($type) {
            case 'campaigns':   $stats = ['campaigns'   => CDAT_Sync::sync_campaigns()]; break;
            case 'coupons':     $stats = ['coupons'     => CDAT_Sync::sync_coupons()]; break;
            case 'top':         $stats = ['top'         => CDAT_Sync::sync_top_products()]; break;
            case 'datafeed':    $stats = ['datafeed'    => CDAT_Sync::sync_datafeed()]; break;
            case 'performance': $stats = ['performance' => CDAT_Sync::sync_performance()]; break;
            default:            $stats = CDAT_Sync::run_all();
        }
        wp_safe_redirect(add_query_arg(['page' => 'codedeal-at', 'msg' => 'synced', 'stats' => urlencode(wp_json_encode($stats))], admin_url('admin.php')));
        exit;
    }

    public function handle_test_token() {
        check_admin_referer('cdat_test_token');
        if (!current_user_can('manage_options')) wp_die('Forbidden');
        $api = new CDAT_API();
        [$resp, $err] = $api->campaigns(['limit' => 1]);
        if ($err) {
            wp_safe_redirect(add_query_arg(['page' => 'codedeal-at', 'test' => 'fail', 'err' => urlencode($err)], admin_url('admin.php')));
        } else {
            wp_safe_redirect(add_query_arg(['page' => 'codedeal-at', 'test' => 'ok'], admin_url('admin.php')));
        }
        exit;
    }

    public function handle_clear_logs() {
        check_admin_referer('cdat_clear_logs');
        if (!current_user_can('manage_options')) wp_die('Forbidden');
        CDAT_Logger::clear();
        wp_safe_redirect(add_query_arg(['page' => 'codedeal-at', 'msg' => 'logs_cleared'], admin_url('admin.php')));
        exit;
    }

    public function handle_generate() {
        check_admin_referer('cdat_generate');
        if (!current_user_can('manage_options')) wp_die('Forbidden');
        $type  = sanitize_text_field($_POST['gen_type'] ?? 'all');
        $store = sanitize_text_field($_POST['gen_store'] ?? '');
        $stats = [];
        switch ($type) {
            case 'all':
                $stats = CDAT_Generator::run_all(); break;
            case 'top-discount':
                $id = CDAT_Generator::generate_top_discount();
                $stats = ['top_discount' => $id ? 1 : 0, 'post_id' => $id]; break;
            case 'flash-sale':
                $id = CDAT_Generator::generate_flash_sale();
                $stats = ['flash_sale' => $id ? 1 : 0, 'post_id' => $id]; break;
            case 'weekly-store':
                $id = CDAT_Generator::generate_weekly_store($store);
                $stats = ['weekly_store' => $id ? 1 : 0, 'post_id' => $id, 'store' => $store]; break;
            case 'coupon-roundup':
                $id = CDAT_Generator::generate_coupon_roundup($store);
                $stats = ['coupon_roundup' => $id ? 1 : 0, 'post_id' => $id, 'store' => $store]; break;
        }
        wp_safe_redirect(add_query_arg([
            'page' => 'codedeal-at',
            'msg'  => 'generated',
            'gstats' => urlencode(wp_json_encode($stats, JSON_UNESCAPED_UNICODE)),
        ], admin_url('admin.php')) . '#generator');
        exit;
    }

    public function handle_probe() {
        check_admin_referer('cdat_probe');
        if (!current_user_can('manage_options')) wp_die('Forbidden');
        $path  = sanitize_text_field($_POST['path'] ?? '/v1/campaigns');
        $query = sanitize_text_field($_POST['query'] ?? '');
        parse_str($query, $qarr);
        $api = new CDAT_API();
        $result = $api->probe($path, $qarr);
        set_transient('cdat_probe_result', $result, 60);
        wp_safe_redirect(add_query_arg(['page' => 'codedeal-at', 'msg' => 'probed'], admin_url('admin.php')) . '#probe');
        exit;
    }

    public function page() {
        $s = cdat_settings();
        $campaigns = (array) get_option('codedeal_at_campaigns_cache', []);
        $last_sync = get_option('codedeal_at_last_sync', 'Chưa có');
        $last_stats = get_option('codedeal_at_last_stats', []);
        $next_run  = wp_next_scheduled(CDAT_Cron::HOOK);
        $next_str  = $next_run ? get_date_from_gmt(date('Y-m-d H:i:s', $next_run), 'd/m/Y H:i') : '—';
        ?>
        <div class="wrap cdat-wrap">
            <h1>🔌 Accesstrade Sync — CodeDeal</h1>

            <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'synced'): ?>
                <div class="notice notice-success is-dismissible"><p>✅ Sync xong: <code><?php echo esc_html(urldecode($_GET['stats'] ?? '')); ?></code></p></div>
            <?php endif; ?>
            <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'logs_cleared'): ?>
                <div class="notice notice-success is-dismissible"><p>Đã xoá logs.</p></div>
            <?php endif; ?>
            <?php if (!empty($_GET['test']) && $_GET['test'] === 'ok'): ?>
                <div class="notice notice-success is-dismissible"><p>✓ Token hợp lệ — gọi API thành công.</p></div>
            <?php elseif (!empty($_GET['test']) && $_GET['test'] === 'fail'): ?>
                <div class="notice notice-error is-dismissible"><p>✗ Token KHÔNG hoạt động: <?php echo esc_html(urldecode($_GET['err'] ?? '')); ?></p></div>
            <?php endif; ?>

            <div class="cdat-grid">
                <div class="cdat-card">
                    <h2>API & Cấu hình</h2>
                    <form method="post" action="options.php">
                        <?php settings_fields('cdat_group'); ?>
                        <input type="hidden" name="<?php echo CDAT_OPT_KEY; ?>[_form_section]" value="api">
                        <table class="form-table">
                            <tr>
                                <th><label for="api_base">API base URL</label></th>
                                <td>
                                    <input type="url" name="<?php echo CDAT_OPT_KEY; ?>[api_base]" id="api_base" value="<?php echo esc_attr($s['api_base']); ?>" class="regular-text">
                                    <p class="description">Mặc định: <code>https://api.accesstrade.vn</code></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="token">AT API Key</label></th>
                                <td>
                                    <input type="text" name="<?php echo CDAT_OPT_KEY; ?>[token]" id="token" value="<?php echo esc_attr($s['token']); ?>" class="large-text" autocomplete="off" placeholder="Dán AT API Key tại đây">
                                    <p class="description">
                                        Lấy tại <a href="https://pub2.accesstrade.vn/" target="_blank">pub2.accesstrade.vn</a>:
                                        Avatar góc phải trên → <strong>Tài khoản của tôi</strong> → tab <strong>API</strong> → copy "AT API Key".<br>
                                        Hoặc vào trực tiếp: <a href="https://pub2.accesstrade.vn/account/api" target="_blank">pub2.accesstrade.vn/account/api</a>.
                                        Nếu chưa có key → click nút "Tạo API Key".
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="sub_id">Sub ID (tracking)</label></th>
                                <td>
                                    <input type="text" name="<?php echo CDAT_OPT_KEY; ?>[sub_id]" id="sub_id" value="<?php echo esc_attr($s['sub_id']); ?>" class="regular-text">
                                    <p class="description">Tuỳ chọn — gắn vào deeplink để track nguồn.</p>
                                </td>
                            </tr>
                        </table>

                        <h2>Loại data đồng bộ</h2>
                        <table class="form-table">
                            <tr>
                                <th>Tự động sync</th>
                                <td>
                                    <label><input type="checkbox" name="<?php echo CDAT_OPT_KEY; ?>[sync_coupons]" value="1" <?php checked($s['sync_coupons']); ?>> Coupons / Promotions</label><br>
                                    <label><input type="checkbox" name="<?php echo CDAT_OPT_KEY; ?>[sync_top]" value="1" <?php checked($s['sync_top']); ?>> Top selling products</label><br>
                                    <label><input type="checkbox" name="<?php echo CDAT_OPT_KEY; ?>[sync_datafeed]" value="1" <?php checked($s['sync_datafeed']); ?>> Datafeed (toàn bộ sản phẩm)</label><br>
                                    <label><input type="checkbox" name="<?php echo CDAT_OPT_KEY; ?>[sync_performance]" value="1" <?php checked($s['sync_performance'] ?? 0); ?>> Performance (orders thực tế từ <code>/v1/orders</code>) — auto-flag deal bán chạy</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Datafeed limit/campaign</label></th>
                                <td>
                                    <input type="number" min="10" max="50" name="<?php echo CDAT_OPT_KEY; ?>[datafeed_limit]" value="<?php echo esc_attr($s['datafeed_limit']); ?>">
                                    <p class="description">Accesstrade VN giới hạn ≤ <strong>50</strong>/lần gọi. Muốn nhiều hơn thì sync nhiều lần hoặc tăng tần suất cron.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Performance window</label></th>
                                <td>
                                    Auto-flag featured khi deal có ≥
                                    <input type="number" min="1" max="100" name="<?php echo CDAT_OPT_KEY; ?>[top_orders_threshold]" value="<?php echo (int) ($s['top_orders_threshold'] ?? 3); ?>" style="width:70px"> đơn
                                    trong vòng
                                    <input type="number" min="1" max="180" name="<?php echo CDAT_OPT_KEY; ?>[performance_window_days]" value="<?php echo (int) ($s['performance_window_days'] ?? 30); ?>" style="width:70px"> ngày.
                                    <p class="description">Pull <code>/v1/orders</code>, đếm đơn theo product_id, deal nào đạt ngưỡng → set <code>_cd_featured</code>. Yêu cầu account đã có conversion data.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Cron interval</label></th>
                                <td>
                                    <select name="<?php echo CDAT_OPT_KEY; ?>[cron_interval]">
                                        <?php foreach (['cdat_15min'=>'Mỗi 15 phút','cdat_30min'=>'Mỗi 30 phút','hourly'=>'Mỗi giờ','cdat_4h'=>'Mỗi 4 giờ','twicedaily'=>'Mỗi 12 giờ','daily'=>'Mỗi ngày'] as $k=>$v): ?>
                                            <option value="<?php echo $k; ?>" <?php selected($s['cron_interval'], $k); ?>><?php echo esc_html($v); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Hành vi</th>
                                <td>
                                    <label><input type="checkbox" name="<?php echo CDAT_OPT_KEY; ?>[auto_publish]" value="1" <?php checked($s['auto_publish']); ?>> Tự động publish (tắt = lưu draft)</label><br>
                                    <label><input type="checkbox" name="<?php echo CDAT_OPT_KEY; ?>[feature_top]" value="1" <?php checked($s['feature_top']); ?>> Top products = featured trên trang chủ</label><br>
                                    <label>Auto-flag featured khi discount ≥
                                        <input type="number" name="<?php echo CDAT_OPT_KEY; ?>[featured_min_discount]" value="<?php echo (int) ($s['featured_min_discount'] ?? 50); ?>" min="0" max="99" style="width:70px"> %
                                    </label>
                                    <p class="description">Đặt 0 để tắt. Deal có mức giảm ≥ ngưỡng này sẽ tự động xuất hiện ở slider "DEAL HOT".</p>
                                </td>
                            </tr>
                            <tr>
                                <th>Chỉ sync các campaign sau</th>
                                <td>
                                    <?php if (empty($campaigns)): ?>
                                        <em>Chưa có cache campaigns. Click "Sync Campaigns" bên dưới trước.</em>
                                    <?php else: ?>
                                        <select name="<?php echo CDAT_OPT_KEY; ?>[campaigns][]" multiple size="8" style="min-width:380px">
                                            <?php foreach ($campaigns as $c): ?>
                                                <option value="<?php echo esc_attr($c['id']); ?>" <?php echo in_array($c['id'], (array) $s['campaigns']) ? 'selected' : ''; ?>>
                                                    <?php echo esc_html($c['name'] . ' (' . $c['id'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="description">Để trống = sync tất cả campaign đã được duyệt.</p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>

                        <?php submit_button('Lưu cấu hình'); ?>
                    </form>
                </div>

                <div class="cdat-side">
                    <div class="cdat-card">
                        <h2>Trạng thái</h2>
                        <p><strong>Lần sync gần nhất:</strong><br><?php echo esc_html($last_sync); ?></p>
                        <?php if ($last_stats): ?>
                            <p><strong>Kết quả:</strong> <code><?php echo esc_html(wp_json_encode($last_stats)); ?></code></p>
                        <?php endif; ?>
                        <p><strong>Cron tiếp theo:</strong><br><?php echo esc_html($next_str); ?></p>
                    </div>

                    <div class="cdat-card">
                        <h2>Hành động nhanh</h2>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:8px">
                            <?php wp_nonce_field('cdat_test_token'); ?>
                            <input type="hidden" name="action" value="cdat_test_token">
                            <button class="button">🔍 Test token</button>
                        </form>

                        <?php foreach (['campaigns'=>'Sync Campaigns','coupons'=>'Sync Coupons','top'=>'Sync Top Products','datafeed'=>'Sync Datafeed','performance'=>'Sync Performance (orders)','all'=>'Sync TẤT CẢ'] as $t=>$label): ?>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:6px">
                                <?php wp_nonce_field('cdat_sync_now'); ?>
                                <input type="hidden" name="action" value="cdat_sync_now">
                                <input type="hidden" name="type" value="<?php echo esc_attr($t); ?>">
                                <button class="button <?php echo $t === 'all' ? 'button-primary' : ''; ?>" style="width:100%"><?php echo esc_html($label); ?></button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="cdat-card" id="generator" style="margin-top:20px">
                <h2>✍️ Auto-Generator — Tự sinh blog từ data Accesstrade</h2>
                <p>Mỗi tuần plugin tự generate blog posts gốc từ data deal/coupon đã sync về:</p>

                <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'generated'): ?>
                    <div class="notice notice-success"><p>✓ Generated: <code><?php echo esc_html(urldecode($_GET['gstats'] ?? '')); ?></code> — <a href="<?php echo esc_url(admin_url('edit.php?post_type=post')); ?>">xem bài đã tạo</a>.</p></div>
                <?php endif; ?>

                <form method="post" action="options.php">
                    <?php settings_fields('cdat_group'); ?>
                    <input type="hidden" name="<?php echo CDAT_OPT_KEY; ?>[_form_section]" value="generator">
                    <table class="form-table">
                        <tr>
                            <th>Bật tự động</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo CDAT_OPT_KEY; ?>[gen_enabled]" value="1" <?php checked($s['gen_enabled']); ?>> Bật cron auto-generate</label>
                            </td>
                        </tr>
                        <tr>
                            <th>Templates</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo CDAT_OPT_KEY; ?>[gen_top_discount]" value="1" <?php checked($s['gen_top_discount']); ?>>
                                    <strong>Top discount tuần</strong> — top 15 deal giảm % cao nhất, 1 bài/lần</label><br>
                                <label><input type="checkbox" name="<?php echo CDAT_OPT_KEY; ?>[gen_flash_sale]" value="1" <?php checked($s['gen_flash_sale']); ?>>
                                    <strong>Flash sale sắp hết hạn</strong> — deal kết thúc trong 7 ngày, 1 bài/lần</label><br>
                                <label><input type="checkbox" name="<?php echo CDAT_OPT_KEY; ?>[gen_weekly_store]" value="1" <?php checked($s['gen_weekly_store']); ?>>
                                    <strong>Top deal mỗi store</strong> — top 10 deal/store, sinh 6 bài (1/store)</label><br>
                                <label><input type="checkbox" name="<?php echo CDAT_OPT_KEY; ?>[gen_coupon_roundup]" value="1" <?php checked($s['gen_coupon_roundup']); ?>>
                                    <strong>Coupon roundup mỗi store</strong> — tổng hợp coupon active của store, sinh 6 bài (1/store)</label>
                            </td>
                        </tr>
                        <tr>
                            <th>Tần suất</th>
                            <td>
                                <select name="<?php echo CDAT_OPT_KEY; ?>[gen_interval]">
                                    <?php foreach (['daily'=>'Mỗi ngày','twicedaily'=>'Mỗi 12 giờ','weekly'=>'Mỗi tuần (khuyến nghị)'] as $k=>$v): ?>
                                        <option value="<?php echo $k; ?>" <?php selected($s['gen_interval'], $k); ?>><?php echo esc_html($v); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">Bài cùng key (ví dụ "top-discount-2026-W18") sẽ được <strong>cập nhật</strong> chứ không tạo mới — không lo trùng.</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Lưu cấu hình Generator'); ?>
                </form>

                <hr>
                <h3>Generate ngay (manual)</h3>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cdat-gen-form">
                    <?php wp_nonce_field('cdat_generate'); ?>
                    <input type="hidden" name="action" value="cdat_generate">
                    <label>Loại:
                        <select name="gen_type">
                            <option value="all">Tất cả templates đã bật</option>
                            <option value="top-discount">Top discount tuần</option>
                            <option value="flash-sale">Flash sale</option>
                            <option value="weekly-store">Top deal 1 store</option>
                            <option value="coupon-roundup">Coupon roundup 1 store</option>
                        </select>
                    </label>
                    <label>Store (nếu chọn weekly-store / coupon-roundup):
                        <select name="gen_store">
                            <option value="">— chọn store —</option>
                            <?php foreach (get_terms(['taxonomy'=>'store','hide_empty'=>false]) as $st): ?>
                                <option value="<?php echo esc_attr($st->slug); ?>"><?php echo esc_html($st->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="button button-primary">✨ Generate ngay</button>
                </form>
            </div>

            <div class="cdat-card" id="probe" style="margin-top:20px">
                <h2>🛠 Debug — Test API endpoint</h2>
                <p>Nếu sync gặp lỗi "Body không phải JSON" hoặc 404, dùng tool này để xem server trả về gì:</p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cdat-probe-form">
                    <?php wp_nonce_field('cdat_probe'); ?>
                    <input type="hidden" name="action" value="cdat_probe">
                    <label>Path: <input type="text" name="path" value="<?php echo esc_attr($_POST['path'] ?? '/v1/campaigns'); ?>" style="min-width:280px"></label>
                    <label>Query: <input type="text" name="query" value="<?php echo esc_attr($_POST['query'] ?? 'approval=1&limit=5'); ?>" style="min-width:280px"></label>
                    <button class="button button-primary">🔍 Gọi & xem raw</button>
                </form>
                <p class="description">Path mẫu: <code>/v1/campaigns</code> · <code>/v1/offers_informations</code> · <code>/v1/top_products</code> · <code>/v1/datafeeds</code></p>

                <?php $probe = get_transient('cdat_probe_result'); if ($probe): ?>
                    <div class="cdat-probe-result">
                        <p><strong>URL:</strong> <code><?php echo esc_html($probe['url']); ?></code></p>
                        <p><strong>HTTP code:</strong>
                            <span class="<?php echo $probe['code'] >= 200 && $probe['code'] < 300 ? 'cdat-ok' : 'cdat-err'; ?>">
                                <?php echo (int) $probe['code']; ?>
                            </span>
                            &nbsp; <strong>Content-Type:</strong> <code><?php echo esc_html($probe['ctype']); ?></code>
                        </p>
                        <p><strong>Body (raw, cắt 4000 ký tự đầu):</strong></p>
                        <textarea readonly rows="14" style="width:100%;font-family:Menlo,monospace;font-size:12px"><?php echo esc_textarea(mb_substr((string)$probe['body'], 0, 4000)); ?></textarea>
                    </div>
                <?php endif; ?>
            </div>

            <div class="cdat-card" style="margin-top:20px">
                <h2 style="display:flex;justify-content:space-between;align-items:center">
                    <span>📜 Logs gần đây</span>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin:0">
                        <?php wp_nonce_field('cdat_clear_logs'); ?>
                        <input type="hidden" name="action" value="cdat_clear_logs">
                        <button class="button button-small">Xoá logs</button>
                    </form>
                </h2>
                <table class="widefat striped">
                    <thead><tr><th>Thời gian</th><th>Level</th><th>Message</th><th>Context</th></tr></thead>
                    <tbody>
                        <?php $logs = CDAT_Logger::get(40); ?>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="4"><em>Chưa có log.</em></td></tr>
                        <?php else: foreach ($logs as $l): ?>
                            <tr class="cdat-log-<?php echo esc_attr($l['level']); ?>">
                                <td><?php echo esc_html($l['time']); ?></td>
                                <td><strong><?php echo esc_html(strtoupper($l['level'])); ?></strong></td>
                                <td><?php echo esc_html($l['message']); ?></td>
                                <td><code style="font-size:11px"><?php echo esc_html(wp_json_encode($l['context'], JSON_UNESCAPED_UNICODE)); ?></code></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}
