<?php
$heading_prefix = webwp_field( 'heading_prefix', 'All-In-One' );
$heading_accent = webwp_field( 'heading_accent', 'Cloud Software.' );
$subtitle       = webwp_field( 'subtitle', 'TOTC is one powerful online software suite that combines all the tools needed to run a successful school or office.' );
$cards = get_field( 'cards' ) ?: [
    [ 'tone' => 'teal',   'icon_class' => 'bi-receipt',        'title' => 'Online Billing, Invoicing, &amp; Contracts', 'body' => 'Simple and secure control of your organization’s financial and legal transactions.' ],
    [ 'tone' => 'mint',   'icon_class' => 'bi-calendar-check', 'title' => 'Easy Scheduling &amp; Attendance Tracking',  'body' => 'Schedule and reserve classrooms at one campus or multiple campuses.' ],
    [ 'tone' => 'orange', 'icon_class' => 'bi-people-fill',    'title' => 'Customer Tracking',                           'body' => 'Automate and track emails to individuals or groups. TOTC’s built-in system helps organize your organization.' ],
];
?>
<section class="section pt-0">
    <div class="container">
        <div class="text-center mx-auto mb-5 gsap-reveal" style="max-width:780px;">
            <h2 class="display-6 fw-bold"><?php echo esc_html( $heading_prefix ); ?> <span class="text-primary-brand"><?php echo esc_html( $heading_accent ); ?></span></h2>
            <p class="mt-3"><?php echo esc_html( $subtitle ); ?></p>
        </div>
        <div class="row g-4">
            <?php foreach ( $cards as $c ) :
                $tone = $c['tone'] ?? 'teal';
                $icon = $c['icon_class'] ?? 'bi-star';
            ?>
                <div class="col-md-6 col-lg-4 gsap-reveal">
                    <div class="allinone-card allinone-card--<?php echo esc_attr( $tone ); ?>">
                        <span class="ico"><i class="bi <?php echo esc_attr( $icon ); ?>"></i></span>
                        <h3 class="fw-bold"><?php echo wp_kses_post( $c['title'] ?? '' ); ?></h3>
                        <p class="mb-0"><?php echo wp_kses_post( $c['body'] ?? '' ); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
