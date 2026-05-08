<?php
/**
 * WP-CLI commands.
 *
 * Examples:
 *   wp codedeal-at sync                  # sync tất cả
 *   wp codedeal-at sync coupons          # chỉ coupon
 *   wp codedeal-at sync top              # chỉ top products
 *   wp codedeal-at sync datafeed         # chỉ datafeed
 *   wp codedeal-at campaigns             # liệt kê campaign đã sync
 *   wp codedeal-at logs --limit=20       # xem log gần nhất
 */
if (!defined('ABSPATH')) exit;

class CDAT_CLI {

    /**
     * Run a sync.
     *
     * ## OPTIONS
     * [<type>]
     * : Loại sync (all|campaigns|coupons|top|datafeed|performance). Mặc định: all
     *
     * [--days=<n>]
     * : Window cho 'performance' (default 30 ngày)
     *
     * ## EXAMPLES
     *   wp codedeal-at sync
     *   wp codedeal-at sync coupons
     *   wp codedeal-at sync performance --days=14
     */
    public function sync($args, $assoc) {
        $type = $args[0] ?? 'all';
        switch ($type) {
            case 'all':         $stats = CDAT_Sync::run_all(); break;
            case 'campaigns':   $stats = ['campaigns'   => CDAT_Sync::sync_campaigns()]; break;
            case 'coupons':     $stats = ['coupons'     => CDAT_Sync::sync_coupons()]; break;
            case 'top':         $stats = ['top'         => CDAT_Sync::sync_top_products()]; break;
            case 'datafeed':    $stats = ['datafeed'    => CDAT_Sync::sync_datafeed()]; break;
            case 'performance': $stats = ['performance' => CDAT_Sync::sync_performance((int) ($assoc['days'] ?? 30))]; break;
            default: WP_CLI::error("Unknown type: $type");
        }
        WP_CLI::success('Done: ' . wp_json_encode($stats, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Diagnose tài khoản: token + campaigns + thử fetch 1 promotion.
     *
     * ## EXAMPLES
     *   wp codedeal-at diagnose
     */
    public function diagnose($args, $assoc) {
        $r = CDAT_Sync::diagnose();
        WP_CLI::log("");
        WP_CLI::log("=== AccessTrade Diagnose ===");
        foreach ($r['steps'] as $step) {
            $name = $step['step'];
            $val = '';
            if (isset($step['ok']))    $val = $step['ok'] ? '✓' : '✗';
            if (isset($step['count'])) $val = $step['count'] . ' items';
            if (isset($step['rows']))  $val = $step['rows'] . ' rows / ' . ($step['codes_found'] ?? 0) . ' codes';
            if (!empty($step['err']))  $val .= ' | ERR: ' . $step['err'];
            WP_CLI::log(sprintf('  • %-50s %s', $name, $val));
        }
        if (!empty($r['sample_promotion'])) {
            WP_CLI::log("");
            WP_CLI::log("=== Sample promotion (1 record) ===");
            WP_CLI::log(wp_json_encode($r['sample_promotion'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    /** Liệt kê campaigns đã cache. */
    public function campaigns($args, $assoc) {
        $cache = (array) get_option('codedeal_at_campaigns_cache', []);
        if (empty($cache)) WP_CLI::warning('Chưa có campaign nào cache. Chạy: wp codedeal-at sync campaigns');
        $rows = [];
        foreach ($cache as $c) $rows[] = ['id' => $c['id'], 'name' => $c['name'], 'merchant' => $c['merchant']];
        WP_CLI\Utils\format_items('table', $rows, ['id', 'name', 'merchant']);
    }

    /**
     * Generate blog posts từ data đã sync.
     *
     * ## OPTIONS
     * [<type>]
     * : Loại generator (all|top-discount|flash-sale|weekly-store|coupon-roundup). Mặc định: all
     *
     * [--store=<slug>]
     * : Bắt buộc khi type = weekly-store hoặc coupon-roundup
     *
     * ## EXAMPLES
     *   wp codedeal-at generate
     *   wp codedeal-at generate top-discount
     *   wp codedeal-at generate flash-sale
     *   wp codedeal-at generate weekly-store --store=shopee
     *   wp codedeal-at generate coupon-roundup --store=lazada
     */
    public function generate($args, $assoc) {
        $type  = $args[0] ?? 'all';
        $store = $assoc['store'] ?? '';
        switch ($type) {
            case 'all':
                $stats = CDAT_Generator::run_all();
                WP_CLI::success('Generated: ' . wp_json_encode($stats, JSON_UNESCAPED_UNICODE));
                break;
            case 'top-discount':
                $id = CDAT_Generator::generate_top_discount();
                $id ? WP_CLI::success("Created post #$id") : WP_CLI::warning('Không đủ data deals.');
                break;
            case 'flash-sale':
                $id = CDAT_Generator::generate_flash_sale();
                $id ? WP_CLI::success("Created post #$id") : WP_CLI::warning('Không có flash sale phù hợp.');
                break;
            case 'weekly-store':
                if (!$store) WP_CLI::error('Cần --store=<slug>');
                $id = CDAT_Generator::generate_weekly_store($store);
                $id ? WP_CLI::success("Created post #$id") : WP_CLI::warning('Store không tồn tại hoặc thiếu data.');
                break;
            case 'coupon-roundup':
                if (!$store) WP_CLI::error('Cần --store=<slug>');
                $id = CDAT_Generator::generate_coupon_roundup($store);
                $id ? WP_CLI::success("Created post #$id") : WP_CLI::warning('Store không tồn tại hoặc thiếu coupons.');
                break;
            default:
                WP_CLI::error("Unknown type: $type");
        }
    }

    /** Xem log gần nhất. */
    public function logs($args, $assoc) {
        $limit = (int) ($assoc['limit'] ?? 20);
        $logs = CDAT_Logger::get($limit);
        if (empty($logs)) { WP_CLI::log('No logs yet.'); return; }
        foreach ($logs as $l) {
            WP_CLI::log(sprintf('[%s][%s] %s %s',
                $l['time'], $l['level'], $l['message'],
                $l['context'] ? wp_json_encode($l['context'], JSON_UNESCAPED_UNICODE) : ''
            ));
        }
    }
}
