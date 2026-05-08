<?php
/**
 * Plugin Name: WebWP Google Analytics
 * Description: Adds Google Tag Manager and Google Analytics 4 from Settings > WebWP Analytics.
 * Version: 1.0.0
 * Author: WebWP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const WEBWP_ANALYTICS_OPTION = 'webwp_analytics_options';

function webwp_analytics_default_options(): array {
    return [
        'gtm_id' => '',
        'ga_id'  => '',
    ];
}

function webwp_analytics_get_options(): array {
    $options = get_option( WEBWP_ANALYTICS_OPTION, [] );

    if ( ! is_array( $options ) ) {
        $options = [];
    }

    $options = wp_parse_args( $options, webwp_analytics_default_options() );

    if ( defined( 'WEBWP_GTM_ID' ) ) {
        $options['gtm_id'] = webwp_analytics_normalize_id( WEBWP_GTM_ID, '/^GTM-[A-Z0-9]+$/' );
    }

    if ( defined( 'WEBWP_GA_MEASUREMENT_ID' ) ) {
        $options['ga_id'] = webwp_analytics_normalize_id( WEBWP_GA_MEASUREMENT_ID, '/^G-[A-Z0-9]+$/' );
    }

    return $options;
}

function webwp_analytics_normalize_id( $value, string $pattern ): string {
    $value = strtoupper( trim( (string) $value ) );
    $value = preg_replace( '/\s+/', '', $value );

    if ( '' === $value ) {
        return '';
    }

    return preg_match( $pattern, $value ) ? $value : '';
}

function webwp_analytics_sanitize_options( $input ): array {
    $input   = is_array( $input ) ? $input : [];
    $current = webwp_analytics_get_options();
    $output  = webwp_analytics_default_options();

    $raw_gtm_id       = isset( $input['gtm_id'] ) ? (string) $input['gtm_id'] : '';
    $raw_ga_id        = isset( $input['ga_id'] ) ? (string) $input['ga_id'] : '';
    $output['gtm_id'] = webwp_analytics_normalize_id( $raw_gtm_id, '/^GTM-[A-Z0-9]+$/' );
    $output['ga_id']  = webwp_analytics_normalize_id( $raw_ga_id, '/^G-[A-Z0-9]+$/' );

    if ( '' !== trim( $raw_gtm_id ) && '' === $output['gtm_id'] ) {
        $output['gtm_id'] = $current['gtm_id'];
        add_settings_error(
            WEBWP_ANALYTICS_OPTION,
            'invalid_gtm_id',
            __( 'GTM ID must look like GTM-XXXXXXX.', 'webwp' ),
            'error'
        );
    }

    if ( '' !== trim( $raw_ga_id ) && '' === $output['ga_id'] ) {
        $output['ga_id'] = $current['ga_id'];
        add_settings_error(
            WEBWP_ANALYTICS_OPTION,
            'invalid_ga_id',
            __( 'GA4 Measurement ID must look like G-XXXXXXXXXX.', 'webwp' ),
            'error'
        );
    }

    return $output;
}

add_action( 'admin_init', function (): void {
    register_setting(
        'webwp_analytics',
        WEBWP_ANALYTICS_OPTION,
        [
            'type'              => 'array',
            'sanitize_callback' => 'webwp_analytics_sanitize_options',
            'default'           => webwp_analytics_default_options(),
        ]
    );

    add_settings_section(
        'webwp_analytics_google',
        __( 'Google tracking', 'webwp' ),
        function (): void {
            echo '<p>' . esc_html__( 'Enter your Google Tag Manager container ID and, only if you are not firing GA4 from GTM, your direct GA4 Measurement ID.', 'webwp' ) . '</p>';
        },
        'webwp-analytics'
    );

    add_settings_field(
        'webwp_analytics_gtm_id',
        __( 'GTM Container ID', 'webwp' ),
        'webwp_analytics_render_gtm_field',
        'webwp-analytics',
        'webwp_analytics_google'
    );

    add_settings_field(
        'webwp_analytics_ga_id',
        __( 'GA4 Measurement ID', 'webwp' ),
        'webwp_analytics_render_ga_field',
        'webwp-analytics',
        'webwp_analytics_google'
    );
} );

add_action( 'admin_menu', function (): void {
    add_options_page(
        __( 'WebWP Analytics', 'webwp' ),
        __( 'WebWP Analytics', 'webwp' ),
        'manage_options',
        'webwp-analytics',
        'webwp_analytics_render_settings_page'
    );
} );

function webwp_analytics_render_gtm_field(): void {
    $options = webwp_analytics_get_options();
    ?>
    <input
        type="text"
        class="regular-text"
        id="webwp_analytics_gtm_id"
        name="<?php echo esc_attr( WEBWP_ANALYTICS_OPTION ); ?>[gtm_id]"
        value="<?php echo esc_attr( $options['gtm_id'] ); ?>"
        placeholder="GTM-XXXXXXX"
        autocomplete="off"
    >
    <p class="description"><?php esc_html_e( 'Added to wp_head() and wp_body_open().', 'webwp' ); ?></p>
    <?php
}

function webwp_analytics_render_ga_field(): void {
    $options = webwp_analytics_get_options();
    ?>
    <input
        type="text"
        class="regular-text"
        id="webwp_analytics_ga_id"
        name="<?php echo esc_attr( WEBWP_ANALYTICS_OPTION ); ?>[ga_id]"
        value="<?php echo esc_attr( $options['ga_id'] ); ?>"
        placeholder="G-XXXXXXXXXX"
        autocomplete="off"
    >
    <p class="description"><?php esc_html_e( 'Leave empty if GA4 is configured inside Google Tag Manager to avoid duplicate page views.', 'webwp' ); ?></p>
    <?php
}

function webwp_analytics_render_settings_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'WebWP Analytics', 'webwp' ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'webwp_analytics' );
            do_settings_sections( 'webwp-analytics' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function webwp_analytics_should_output(): bool {
    return ! is_admin() && ! is_feed() && ! wp_doing_ajax() && ! is_preview();
}

add_action( 'wp_head', 'webwp_analytics_print_gtm_head', 0 );
function webwp_analytics_print_gtm_head(): void {
    if ( ! webwp_analytics_should_output() ) {
        return;
    }

    $gtm_id = webwp_analytics_get_options()['gtm_id'];

    if ( '' === $gtm_id ) {
        return;
    }
    ?>
    <!-- Google Tag Manager -->
    <script>
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo esc_js( $gtm_id ); ?>');
    </script>
    <!-- End Google Tag Manager -->
    <?php
}

add_action( 'wp_body_open', 'webwp_analytics_print_gtm_body', 0 );
function webwp_analytics_print_gtm_body(): void {
    if ( ! webwp_analytics_should_output() ) {
        return;
    }

    $gtm_id = webwp_analytics_get_options()['gtm_id'];

    if ( '' === $gtm_id ) {
        return;
    }
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="<?php echo esc_url( 'https://www.googletagmanager.com/ns.html?id=' . rawurlencode( $gtm_id ) ); ?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}

add_action( 'wp_head', 'webwp_analytics_print_ga4', 1 );
function webwp_analytics_print_ga4(): void {
    if ( ! webwp_analytics_should_output() ) {
        return;
    }

    $ga_id = webwp_analytics_get_options()['ga_id'];

    if ( '' === $ga_id ) {
        return;
    }
    ?>
    <!-- Google Analytics 4 -->
    <script async src="<?php echo esc_url( 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $ga_id ) ); ?>"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?php echo esc_js( $ga_id ); ?>');
    </script>
    <!-- End Google Analytics 4 -->
    <?php
}
