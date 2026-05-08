<?php
/**
 * Lightweight in-DB logger for sync runs.
 * Lưu 100 entry gần nhất trong wp_options để không cần migrate DB.
 */
if (!defined('ABSPATH')) exit;

class CDAT_Logger {
    const MAX = 100;

    public static function log($level, $message, $context = []) {
        $logs = get_option(CDAT_LOG_KEY, []);
        if (!is_array($logs)) $logs = [];
        $logs[] = [
            'time'    => current_time('mysql'),
            'level'   => $level,
            'message' => $message,
            'context' => $context,
        ];
        if (count($logs) > self::MAX) {
            $logs = array_slice($logs, -self::MAX);
        }
        update_option(CDAT_LOG_KEY, $logs, false);
        // Also write to PHP log
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('[CDAT][%s] %s %s', $level, $message, json_encode($context, JSON_UNESCAPED_UNICODE)));
        }
    }

    public static function info($msg, $ctx = [])  { self::log('info', $msg, $ctx); }
    public static function warn($msg, $ctx = [])  { self::log('warn', $msg, $ctx); }
    public static function error($msg, $ctx = []) { self::log('error', $msg, $ctx); }

    public static function get($limit = 50) {
        $logs = get_option(CDAT_LOG_KEY, []);
        if (!is_array($logs)) return [];
        return array_slice(array_reverse($logs), 0, $limit);
    }

    public static function clear() {
        delete_option(CDAT_LOG_KEY);
    }
}
