<?php
/**
 * WP-Cron schedules.
 */
if (!defined('ABSPATH')) exit;

class CDAT_Cron {

    const HOOK     = 'codedeal_at_sync_event';
    const HOOK_GEN = 'codedeal_at_gen_event';

    public function __construct() {
        add_filter('cron_schedules', [$this, 'add_intervals']);
        add_action(self::HOOK,     ['CDAT_Sync', 'run_all']);
        add_action(self::HOOK_GEN, [$this, 'run_generator']);
        add_action('update_option_' . CDAT_OPT_KEY, [$this, 'reschedule'], 10, 2);
    }

    public function add_intervals($s) {
        $s['cdat_15min'] = ['interval' => 900,    'display' => 'Mỗi 15 phút (CodeDeal AT)'];
        $s['cdat_30min'] = ['interval' => 1800,   'display' => 'Mỗi 30 phút (CodeDeal AT)'];
        $s['cdat_4h']    = ['interval' => 14400,  'display' => 'Mỗi 4 giờ (CodeDeal AT)'];
        $s['weekly']     = ['interval' => 604800, 'display' => 'Mỗi tuần (CodeDeal AT)'];
        return $s;
    }

    public static function activate() {
        if (!wp_next_scheduled(self::HOOK)) {
            wp_schedule_event(time() + 60, 'hourly', self::HOOK);
        }
        if (!wp_next_scheduled(self::HOOK_GEN)) {
            wp_schedule_event(time() + 300, 'weekly', self::HOOK_GEN);
        }
    }

    public static function deactivate() {
        foreach ([self::HOOK, self::HOOK_GEN] as $hook) {
            $ts = wp_next_scheduled($hook);
            if ($ts) wp_unschedule_event($ts, $hook);
        }
    }

    /** Wrapper: chỉ chạy khi gen_enabled = true */
    public function run_generator() {
        $s = cdat_settings();
        if (empty($s['gen_enabled'])) return;
        CDAT_Generator::run_all();
    }

    /** Khi user đổi cron_interval / gen_interval trong setting, schedule lại */
    public function reschedule($old, $new) {
        $old = is_array($old) ? $old : [];
        $new = is_array($new) ? $new : [];

        $old_sync = $old['cron_interval'] ?? '';
        $new_sync = $new['cron_interval'] ?? 'hourly';
        if ($old_sync !== $new_sync) {
            $ts = wp_next_scheduled(self::HOOK);
            if ($ts) wp_unschedule_event($ts, self::HOOK);
            wp_schedule_event(time() + 60, $new_sync, self::HOOK);
            CDAT_Logger::info('Sync cron rescheduled', ['interval' => $new_sync]);
        }

        $old_gen = $old['gen_interval'] ?? '';
        $new_gen = $new['gen_interval'] ?? 'weekly';
        if ($old_gen !== $new_gen) {
            $ts = wp_next_scheduled(self::HOOK_GEN);
            if ($ts) wp_unschedule_event($ts, self::HOOK_GEN);
            wp_schedule_event(time() + 300, $new_gen, self::HOOK_GEN);
            CDAT_Logger::info('Generator cron rescheduled', ['interval' => $new_gen]);
        }
    }
}
