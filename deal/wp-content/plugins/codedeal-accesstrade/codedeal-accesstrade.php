<?php
/**
 * Plugin Name:       CodeDeal — Accesstrade Sync
 * Plugin URI:        https://codedeal.local
 * Description:       Tự động đồng bộ campaigns, top products, datafeed và promotions từ Accesstrade Vietnam (api.accesstrade.vn) vào CPT Deal & Coupon của theme CodeDeal.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Billy + Claude
 * License:           GPL v2 or later
 * Text Domain:       codedeal-at
 */

if (!defined('ABSPATH')) exit;

define('CDAT_VERSION', '1.0.0');
define('CDAT_FILE',    __FILE__);
define('CDAT_DIR',     plugin_dir_path(__FILE__));
define('CDAT_URL',     plugin_dir_url(__FILE__));
define('CDAT_OPT_KEY', 'codedeal_at_settings');
define('CDAT_LOG_KEY', 'codedeal_at_logs');

require_once CDAT_DIR . 'includes/class-logger.php';
require_once CDAT_DIR . 'includes/class-api.php';
require_once CDAT_DIR . 'includes/class-mapper.php';
require_once CDAT_DIR . 'includes/class-sync.php';
require_once CDAT_DIR . 'includes/class-cron.php';
require_once CDAT_DIR . 'includes/class-generator.php';
require_once CDAT_DIR . 'includes/class-admin.php';
require_once CDAT_DIR . 'includes/class-cli.php';

register_activation_hook(__FILE__, ['CDAT_Cron', 'activate']);
register_deactivation_hook(__FILE__, ['CDAT_Cron', 'deactivate']);

// Đăng ký WP-CLI command NGAY khi plugin load (không phải plugins_loaded)
// vì wp-cli cần command sẵn sàng trước hook plugins_loaded.
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('codedeal-at', 'CDAT_CLI');
}

add_action('plugins_loaded', function () {
    new CDAT_Admin();
    new CDAT_Cron();
});

/* Convenience getter */
function cdat_settings() {
    $defaults = [
        'api_base'   => 'https://api.accesstrade.vn',
        'token'      => '',
        'sub_id'     => '',
        'campaigns'  => [], // array of campaign_id strings to sync
        'sync_coupons'    => 1,
        'sync_top'        => 1,
        'sync_datafeed'   => 0,
        'sync_performance'=> 1,    // pull /v1/orders để auto-flag deal bán chạy thật
        'datafeed_limit'  => 50,
        'top_orders_threshold' => 3,  // ≥ N đơn trong window → auto featured
        'performance_window_days' => 30,
        'cron_interval'   => 'hourly', // hourly | twicedaily | daily
        'auto_publish'    => 1,
        'feature_top'     => 1,
        // Auto-flag _cd_featured khi discount % >= ngưỡng này (0 = tắt)
        'featured_min_discount' => 50,
        // Auto-generator
        'gen_enabled'        => 0,
        'gen_top_discount'   => 1,
        'gen_flash_sale'     => 1,
        'gen_weekly_store'   => 1,
        'gen_coupon_roundup' => 1,
        'gen_interval'       => 'weekly', // daily | twicedaily | weekly
    ];
    $opt = get_option(CDAT_OPT_KEY, []);
    return array_merge($defaults, is_array($opt) ? $opt : []);
}
