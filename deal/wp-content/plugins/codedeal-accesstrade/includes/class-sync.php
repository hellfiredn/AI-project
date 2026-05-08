<?php
/**
 * Sync engine: gọi API → map → upsert.
 */
if (!defined('ABSPATH')) exit;

class CDAT_Sync {

    public static function run_all(): array {
        $stats = ['campaigns' => 0, 'coupons' => 0, 'top' => 0, 'datafeed' => 0, 'performance' => null, 'errors' => []];

        $sync_settings = cdat_settings();
        if (!empty($sync_settings['campaigns']) && is_array($sync_settings['campaigns'])) {
            $campaigns = $sync_settings['campaigns'];
        } else {
            // sync campaigns trước, lấy ID
            $stats['campaigns'] = self::sync_campaigns();
            $campaigns = (array) $sync_settings['campaigns']; // sau khi sync user có thể chọn
            if (empty($campaigns)) $campaigns = self::all_active_campaign_ids();
        }

        if (!empty($sync_settings['sync_coupons'])) {
            $stats['coupons'] = self::sync_coupons($campaigns);
        }
        if (!empty($sync_settings['sync_top'])) {
            $stats['top'] = self::sync_top_products($campaigns);
        }
        if (!empty($sync_settings['sync_datafeed'])) {
            $stats['datafeed'] = self::sync_datafeed($campaigns, (int) $sync_settings['datafeed_limit']);
        }
        if (!empty($sync_settings['sync_performance'])) {
            $stats['performance'] = self::sync_performance((int) ($sync_settings['performance_window_days'] ?? 30));
        }

        update_option('codedeal_at_last_sync', current_time('mysql'));
        update_option('codedeal_at_last_stats', $stats, false);
        CDAT_Logger::info('Sync complete', $stats);

        return $stats;
    }

    /* ---------------- Campaigns ---------------- */

    public static function sync_campaigns(): int {
        $api = new CDAT_API();
        if (!$api->has_token()) {
            CDAT_Logger::warn('Sync campaigns skipped — no AT API Key');
            return 0;
        }

        // Bước 1: Lấy campaign ĐÃ DUYỆT
        [$resp, $err] = $api->campaigns(['approval' => 1, 'limit' => 50]);
        if ($err) { CDAT_Logger::error('Campaigns fetch failed (approval=1)', ['err' => $err]); return 0; }
        $rows_approved = CDAT_API::rows($resp);
        CDAT_Logger::info('Campaigns approved fetched', ['count' => count($rows_approved)]);

        // Bước 2: Nếu chưa duyệt cái nào, fallback lấy TẤT CẢ campaign sẵn có
        $rows = $rows_approved;
        $approved_ids = array_filter(array_map(fn($c) => (string) ($c['id'] ?? $c['campaign_id'] ?? ''), $rows_approved));

        if (empty($rows)) {
            CDAT_Logger::warn('Account chưa duyệt campaign nào → fallback fetch all campaigns');
            [$resp_all, $err_all] = $api->campaigns(['approval' => 0, 'limit' => 50]);
            if (!$err_all) {
                $rows = CDAT_API::rows($resp_all);
                CDAT_Logger::info('All campaigns fetched (chưa duyệt)', ['count' => count($rows)]);
            }
        }

        $cached = [];
        foreach ($rows as $c) {
            $cid = (string) ($c['id'] ?? $c['campaign_id'] ?? '');
            if ($cid === '') continue;
            $cached[$cid] = [
                'id'        => $cid,
                'name'      => (string) ($c['name'] ?? $c['campaign'] ?? $cid),
                'merchant'  => (string) ($c['merchant'] ?? $c['domain'] ?? ''),
                'logo'      => (string) ($c['logo'] ?? ''),
                'approved'  => in_array($cid, $approved_ids, true),
            ];
        }
        update_option('codedeal_at_campaigns_cache', $cached, false);

        if (empty($cached)) {
            CDAT_Logger::warn('Không có campaign nào — kiểm tra AT API Key + đăng ký chiến dịch ở pub2.accesstrade.vn');
        }
        return count($cached);
    }

    /** Trả về chỉ những campaign đã được duyệt (để dùng cho coupon/top sync). */
    public static function approved_campaign_ids(): array {
        $cache = (array) get_option('codedeal_at_campaigns_cache', []);
        $ids = [];
        foreach ($cache as $c) {
            if (!empty($c['approved'])) $ids[] = $c['id'];
        }
        return $ids;
    }

    public static function all_active_campaign_ids(): array {
        $cache = (array) get_option('codedeal_at_campaigns_cache', []);
        return array_keys($cache);
    }

    public static function campaign_name(string $id): string {
        $cache = (array) get_option('codedeal_at_campaigns_cache', []);
        return $cache[$id]['name'] ?? $id;
    }

    /* ---------------- Coupons ---------------- */

    public static function sync_coupons(array $campaign_ids = []): int {
        $api = new CDAT_API();
        if (!$api->has_token()) return 0;
        $s = cdat_settings();
        $count = 0;
        $with_code = 0;
        $click_to_claim = 0;

        // Ưu tiên approved campaigns; nếu chưa duyệt cái nào thì thử all campaigns + cuối cùng fallback no-filter
        if (empty($campaign_ids)) $campaign_ids = self::approved_campaign_ids();
        if (empty($campaign_ids)) $campaign_ids = self::all_active_campaign_ids();
        $tried_global = false;
        if (empty($campaign_ids)) { $campaign_ids = [null]; $tried_global = true; }

        $api_calls = 0;
        $api_rows  = 0;
        foreach ($campaign_ids as $cid) {
            $args = ['limit' => 50, 'page' => 1];
            if ($cid) $args['campaign'] = $cid;
            [$resp, $err] = $api->promotions($args);
            $api_calls++;
            if ($err) { CDAT_Logger::error('Promotions fetch failed', ['cid' => $cid, 'err' => $err]); continue; }
            $rows = CDAT_API::rows($resp);
            $api_rows += count($rows);

            foreach ($rows as $row) {
                $ids = CDAT_Mapper::upsert_coupon_or_iterate($row, [
                    'auto_publish'  => !empty($s['auto_publish']),
                    'campaign_name' => $cid ? self::campaign_name((string) $cid) : '',
                ]);
                foreach ($ids as $id) {
                    $count++;
                    $code = get_post_meta($id, '_cd_code', true);
                    if ($code) $with_code++; else $click_to_claim++;
                }
            }
        }
        CDAT_Logger::info('Coupons synced', [
            'total' => $count, 'with_code' => $with_code, 'click_to_claim' => $click_to_claim,
            'api_calls' => $api_calls, 'api_rows' => $api_rows, 'global_fetch' => $tried_global,
        ]);
        if ($count === 0) {
            CDAT_Logger::warn('Sync coupons = 0 — possible reasons: (1) chưa đăng ký chiến dịch nào ở pub2; (2) chiến dịch chưa duyệt; (3) chiến dịch đã duyệt không có promo đang hoạt động.');
        }
        return $count;
    }

    /* ---------------- Top products ---------------- */

    public static function sync_top_products(array $campaign_ids = []): int {
        $api = new CDAT_API();
        if (!$api->has_token()) return 0;
        $s = cdat_settings();
        $count = 0;
        $api_rows = 0;

        if (empty($campaign_ids)) $campaign_ids = self::approved_campaign_ids();
        if (empty($campaign_ids)) $campaign_ids = self::all_active_campaign_ids();
        if (empty($campaign_ids)) $campaign_ids = [null];

        foreach ($campaign_ids as $cid) {
            $args = ['limit' => 30];
            if ($cid) $args['campaign'] = $cid;
            [$resp, $err] = $api->top_products($args);
            if ($err) { CDAT_Logger::error('Top products fetch failed', ['cid' => $cid, 'err' => $err]); continue; }
            $rows = CDAT_API::rows($resp);
            $api_rows += count($rows);
            foreach ($rows as $row) {
                $id = CDAT_Mapper::upsert_deal($row, [
                    'auto_publish'  => !empty($s['auto_publish']),
                    'featured'      => !empty($s['feature_top']),
                    'source'        => 'top_products',
                    'featured_min_discount' => (int) ($s['featured_min_discount'] ?? 0),
                    'campaign_name' => $cid ? self::campaign_name((string) $cid) : '',
                ]);
                if ($id) $count++;
            }
        }
        CDAT_Logger::info('Top products synced', ['count' => $count, 'api_rows' => $api_rows]);
        if ($count === 0 && $api_rows === 0) {
            CDAT_Logger::warn('Top products = 0 rows từ API — campaign chưa duyệt hoặc chưa có top products.');
        }
        return $count;
    }

    /* ---------------- Diagnose ---------------- */

    /**
     * Chẩn đoán account: token, campaigns approved/total, sample data.
     * @return array
     */
    public static function diagnose(): array {
        $api = new CDAT_API();
        $out = ['steps' => []];

        $out['steps'][] = ['step' => 'Token configured', 'ok' => $api->has_token()];
        if (!$api->has_token()) return $out;

        // Approved campaigns
        [$r, $err] = $api->campaigns(['approval' => 1, 'limit' => 50]);
        $approved = $err ? [] : CDAT_API::rows($r);
        $out['steps'][] = ['step' => 'Approved campaigns', 'count' => count($approved), 'err' => $err];

        // All campaigns
        [$r2, $err2] = $api->campaigns(['approval' => 0, 'limit' => 50]);
        $all = $err2 ? [] : CDAT_API::rows($r2);
        $out['steps'][] = ['step' => 'All available campaigns', 'count' => count($all), 'err' => $err2];

        // Try fetch promotions for first approved campaign
        if (!empty($approved)) {
            $first = $approved[0];
            $cid = (string) ($first['id'] ?? $first['campaign_id'] ?? '');
            if ($cid) {
                [$rp, $errp] = $api->promotions(['campaign' => $cid, 'limit' => 5]);
                $rows = $errp ? [] : CDAT_API::rows($rp);
                $sample_codes = 0;
                foreach ($rows as $row) {
                    if (!empty($row['coupons'])) $sample_codes += count($row['coupons']);
                    if (!empty($row['code']) || !empty($row['coupon'])) $sample_codes++;
                }
                $out['steps'][] = [
                    'step' => "Promotions for campaign '{$first['name']}' (id=$cid)",
                    'rows' => count($rows), 'codes_found' => $sample_codes, 'err' => $errp,
                ];
                if (!empty($rows)) $out['sample_promotion'] = $rows[0]; // for inspection
            }
        }
        return $out;
    }

    /* ---------------- Performance (Orders) ---------------- */

    /**
     * Pull orders trong N ngày qua, aggregate theo product_id, ghi vào meta deal,
     * auto-flag _cd_featured cho deal có ≥ threshold đơn (approved + pending).
     *
     * @param int $days Số ngày lookback (default 30)
     * @return array stats
     */
    public static function sync_performance(int $days = 30): array {
        $api = new CDAT_API();
        $stats = ['orders' => 0, 'matched' => 0, 'flagged' => 0, 'unmatched' => 0];
        if (!$api->has_token()) return $stats;

        $s = cdat_settings();
        $threshold = max(1, (int) ($s['top_orders_threshold'] ?? 3));
        $end_time   = current_time('mysql');
        $start_time = date('Y-m-d H:i:s', strtotime("-{$days} days", current_time('timestamp')));

        // Pull tất cả pages (giới hạn 10 page = 500 orders để tránh quá tải)
        $page = 1;
        $max_pages = 10;
        $rows_all = [];
        while ($page <= $max_pages) {
            [$resp, $err] = $api->orders([
                'start_time' => $start_time,
                'end_time'   => $end_time,
                'limit'      => 50,
                'page'       => $page,
            ]);
            if ($err) {
                CDAT_Logger::error('Orders fetch failed', ['page' => $page, 'err' => $err]);
                break;
            }
            $rows = CDAT_API::rows($resp);
            if (empty($rows)) break;
            $rows_all = array_merge($rows_all, $rows);
            if (count($rows) < 50) break; // last page
            $page++;
        }
        $stats['orders'] = count($rows_all);
        if (empty($rows_all)) {
            CDAT_Logger::warn('Performance sync: 0 orders trả về — có thể chưa có đơn hoặc endpoint khác format. Dùng probe để kiểm tra.');
            return $stats;
        }

        // Aggregate theo product_id
        $agg = []; // product_id => ['count' => N, 'amount' => sum, 'merchants' => set]
        foreach ($rows_all as $o) {
            $pid = (string) (
                $o['product_id'] ?? $o['sku']        ??
                $o['item_id']    ?? $o['product_sku']?? ''
            );
            if ($pid === '') continue;
            if (!isset($agg[$pid])) $agg[$pid] = ['count' => 0, 'amount' => 0, 'merchants' => []];
            $agg[$pid]['count']++;
            $agg[$pid]['amount'] += (float) (
                $o['sales_amount'] ?? $o['amount'] ?? $o['order_amount'] ?? 0
            );
            $m = (string) ($o['merchant'] ?? $o['campaign'] ?? '');
            if ($m) $agg[$pid]['merchants'][$m] = true;
        }

        if (empty($agg)) {
            CDAT_Logger::warn('Performance sync: orders không có product_id field — kiểm tra response shape qua probe /v1/orders.');
            return $stats;
        }

        // Map ngược về deal qua _cd_at_uid (đã set khi sync top_products / datafeed)
        foreach ($agg as $pid => $data) {
            $deals = get_posts([
                'post_type'      => 'deal',
                'meta_key'       => '_cd_at_uid',
                'meta_value'     => $pid,
                'posts_per_page' => 1,
                'post_status'    => 'any',
            ]);
            if (empty($deals)) {
                $stats['unmatched']++;
                continue;
            }
            $deal_id = $deals[0]->ID;
            $stats['matched']++;
            update_post_meta($deal_id, '_cd_orders_count', (int) $data['count']);
            update_post_meta($deal_id, '_cd_orders_amount', (float) $data['amount']);
            update_post_meta($deal_id, '_cd_orders_synced_at', current_time('mysql'));

            if ($data['count'] >= $threshold) {
                update_post_meta($deal_id, '_cd_featured', '1');
                $stats['flagged']++;
            }
        }

        CDAT_Logger::info('Performance synced', $stats + ['days' => $days, 'threshold' => $threshold]);
        update_option('codedeal_at_last_perf_stats', $stats, false);
        return $stats;
    }

    /* ---------------- Datafeed ---------------- */

    public static function sync_datafeed(array $campaign_ids = [], int $limit_per_campaign = 50): int {
        $api = new CDAT_API();
        if (!$api->has_token()) return 0;
        $s = cdat_settings();
        $count = 0;

        if (empty($campaign_ids)) $campaign_ids = self::all_active_campaign_ids();
        if (empty($campaign_ids)) $campaign_ids = [null];

        foreach ($campaign_ids as $cid) {
            $args = ['limit' => $limit_per_campaign];
            if ($cid) $args['campaign'] = $cid;
            [$resp, $err] = $api->datafeeds($args);
            if ($err) { CDAT_Logger::error('Datafeed fetch failed', ['cid' => $cid, 'err' => $err]); continue; }
            foreach (CDAT_API::rows($resp) as $row) {
                $id = CDAT_Mapper::upsert_deal($row, [
                    'auto_publish'  => !empty($s['auto_publish']),
                    'source'        => 'datafeed',
                    'featured_min_discount' => (int) ($s['featured_min_discount'] ?? 0),
                    'campaign_name' => $cid ? self::campaign_name((string) $cid) : '',
                ]);
                if ($id) $count++;
            }
        }
        CDAT_Logger::info('Datafeed synced', ['count' => $count]);
        return $count;
    }
}
