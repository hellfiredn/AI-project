<?php
/**
 * Accesstrade Vietnam API client.
 * Docs: https://developers.accesstrade.vn/api-publisher-vietnamese
 *
 * Auth: Header  Authorization: Token <ACCESS_TOKEN>
 * Base: https://api.accesstrade.vn
 *
 * Tất cả method trả về [data, error]. error=null nếu OK.
 */
if (!defined('ABSPATH')) exit;

class CDAT_API {

    protected string $base;
    protected string $token;

    public function __construct(?string $token = null, ?string $base = null) {
        $s = cdat_settings();
        $this->token = $token ?? (string) $s['token'];
        $this->base  = rtrim($base ?? (string) $s['api_base'], '/');
    }

    public function has_token(): bool { return $this->token !== ''; }

    /* ------------- Endpoints ------------- */

    /**
     * Accesstrade VN giới hạn limit tối đa = 50 cho mọi endpoint listing.
     * Mọi limit truyền vào sẽ được cap về 50.
     */
    const MAX_LIMIT = 50;

    /** GET /v1/campaigns */
    public function campaigns(array $args = []): array {
        // approval = 1 → chỉ campaign mình đã được duyệt; 0 → tất cả
        $defaults = ['approval' => 1, 'limit' => 50, 'page' => 1];
        $args = array_merge($defaults, $args);
        $args['limit'] = min((int) $args['limit'], self::MAX_LIMIT);
        return $this->get('/v1/campaigns', $args);
    }

    /** GET /v1/offers_informations  (mã giảm giá / khuyến mãi) */
    public function promotions(array $args = []): array {
        $defaults = ['limit' => 50, 'page' => 1];
        $args = array_merge($defaults, $args);
        $args['limit'] = min((int) $args['limit'], self::MAX_LIMIT);
        return $this->get('/v1/offers_informations', $args);
    }

    /** GET /v1/top_products */
    public function top_products(array $args = []): array {
        $defaults = ['limit' => 30, 'page' => 1];
        $args = array_merge($defaults, $args);
        $args['limit'] = min((int) $args['limit'], self::MAX_LIMIT);
        return $this->get('/v1/top_products', $args);
    }

    /** GET /v1/datafeeds */
    public function datafeeds(array $args = []): array {
        $defaults = ['limit' => 50, 'page' => 1];
        $args = array_merge($defaults, $args);
        $args['limit'] = min((int) $args['limit'], self::MAX_LIMIT);
        return $this->get('/v1/datafeeds', $args);
    }

    /** GET /v1/offers_informations */
    public function offers(array $args = []): array {
        $defaults = ['limit' => 50, 'page' => 1];
        $args = array_merge($defaults, $args);
        $args['limit'] = min((int) $args['limit'], self::MAX_LIMIT);
        return $this->get('/v1/offers_informations', $args);
    }

    /**
     * GET /v1/orders — đơn hàng (Order v2).
     * @param array $args ['start_time'=>'YYYY-MM-DD HH:mm:ss', 'end_time'=>'...', 'limit'=>50, 'page'=>1]
     */
    public function orders(array $args = []): array {
        $defaults = ['limit' => 50, 'page' => 1];
        $args = array_merge($defaults, $args);
        $args['limit'] = min((int) $args['limit'], self::MAX_LIMIT);
        return $this->get('/v1/orders', $args);
    }

    /**
     * GET /v1/transactions — giao dịch (commission).
     * @param array $args ['start_time'=>'YYYY-MM-DD HH:mm:ss', 'end_time'=>'...', 'limit'=>50, 'page'=>1]
     */
    public function transactions(array $args = []): array {
        $defaults = ['limit' => 50, 'page' => 1];
        $args = array_merge($defaults, $args);
        $args['limit'] = min((int) $args['limit'], self::MAX_LIMIT);
        return $this->get('/v1/transactions', $args);
    }

    /** POST /v1/deeplink — generate affiliate deeplink */
    public function deeplink(string $campaign, string $url, ?string $sub_id = null): array {
        $payload = [
            'campaign' => $campaign,
            'urls'     => [$url],
        ];
        if ($sub_id) $payload['sub_id'] = $sub_id;
        return $this->post('/v1/deeplink/' . rawurlencode($campaign), $payload);
    }

    /* ------------- HTTP core ------------- */

    protected function get(string $path, array $query = []): array {
        $url = $this->base . $path;
        if ($query) $url .= '?' . http_build_query($query);
        return $this->request('GET', $url);
    }

    protected function post(string $path, array $body = []): array {
        return $this->request('POST', $this->base . $path, $body);
    }

    protected function request(string $method, string $url, ?array $body = null): array {
        if (!$this->has_token()) {
            return [null, 'Chưa cấu hình AT API Key. Vào Accesstrade → Cấu hình để dán.'];
        }
        $args = [
            'method'  => $method,
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Token ' . $this->token,
                'Accept'        => 'application/json',
                'User-Agent'    => 'CodeDeal-AT-Sync/' . CDAT_VERSION,
            ],
        ];
        if ($body !== null) {
            $args['headers']['Content-Type'] = 'application/json';
            $args['body'] = wp_json_encode($body);
        }

        // Debug: log URL (token bị che bớt) — chỉ khi WP_DEBUG bật
        if (defined('WP_DEBUG') && WP_DEBUG) {
            CDAT_Logger::info('API call', ['url' => $url, 'method' => $method]);
        }

        $resp = wp_remote_request($url, $args);
        if (is_wp_error($resp)) {
            return [null, 'HTTP error: ' . $resp->get_error_message()];
        }
        $code = wp_remote_retrieve_response_code($resp);
        $raw  = wp_remote_retrieve_body($resp);
        $ctype = wp_remote_retrieve_header($resp, 'content-type');
        $data = json_decode($raw, true);

        if ($code >= 400) {
            $msg = is_array($data) ? ($data['message'] ?? $data['error'] ?? '') : '';
            if (!$msg) {
                $msg = trim(wp_strip_all_tags($raw));
            }
            return [null, "HTTP $code: " . self::truncate((string) $msg, 220) . " | URL: $url | CT: $ctype"];
        }

        // Accesstrade đôi khi trả về JSON-string (double encoded) — decode 1 lần nữa
        if (is_string($data)) {
            $maybe = json_decode($data, true);
            if (is_array($maybe)) $data = $maybe;
        }

        if (!is_array($data)) {
            $preview = self::truncate(trim(wp_strip_all_tags($raw)), 220);
            return [null, "Body không phải JSON (CT: $ctype). Raw: $preview | URL: $url"];
        }

        // 200 OK nhưng API báo lỗi mềm dạng {"message": "..."} mà không có data
        if (isset($data['message']) && !isset($data['data']) && !isset($data['result']) && !array_is_list($data)) {
            return [null, 'API soft-error: ' . self::truncate((string) $data['message'], 220) . " | URL: $url"];
        }

        return [$data, null];
    }

    protected static function truncate(string $s, int $len): string {
        $s = preg_replace('/\s+/', ' ', $s);
        return mb_strlen($s) > $len ? mb_substr($s, 0, $len) . '…' : $s;
    }

    /**
     * Public probe: gọi 1 endpoint trả về raw để admin debug.
     * @return array ['code'=>int,'ctype'=>string,'body'=>string]
     */
    public function probe(string $path, array $query = []): array {
        $url = $this->base . $path;
        if ($query) $url .= '?' . http_build_query($query);
        $resp = wp_remote_get($url, [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Token ' . $this->token,
                'Accept'        => 'application/json',
                'User-Agent'    => 'CodeDeal-AT-Sync/' . CDAT_VERSION,
            ],
        ]);
        if (is_wp_error($resp)) return ['code' => 0, 'ctype' => '', 'body' => $resp->get_error_message(), 'url' => $url];
        return [
            'code'  => wp_remote_retrieve_response_code($resp),
            'ctype' => wp_remote_retrieve_header($resp, 'content-type'),
            'body'  => wp_remote_retrieve_body($resp),
            'url'   => $url,
        ];
    }

    /* ------------- Helper: extract row list from a response ------------- */

    /**
     * Accesstrade trả về dạng { status, total, data: [...] } hoặc { data: [...] }.
     * Hàm này trích mảng row chính ra cho dễ duyệt.
     */
    public static function rows($response): array {
        if (!is_array($response)) return [];
        if (isset($response['data']) && is_array($response['data'])) return $response['data'];
        if (isset($response['result']) && is_array($response['result'])) return $response['result'];
        if (array_is_list($response)) return $response;
        return [];
    }
}
