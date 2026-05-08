<?php
$heading = webwp_field( 'heading', 'Our Success' );
$subtitle = webwp_field( 'subtitle', 'Ornare id fames interdum porttitor nulla turpis etiam. Diam vitae sollicitudin at nec nam et pharetra gravida. Adipiscing a quis ultrices eu ornare tristique vel nisl orci.' );
$stats = get_field( 'stats' ) ?: [
    [ 'number' => '15', 'suffix' => 'K+', 'label' => 'Students' ],
    [ 'number' => '75', 'suffix' => ' %', 'label' => 'Total success' ],
    [ 'number' => '35', 'suffix' => '',   'label' => 'Main questions' ],
    [ 'number' => '26', 'suffix' => '',   'label' => 'Chief experts' ],
    [ 'number' => '16', 'suffix' => '',   'label' => 'Years of experience' ],
];
?>
<section class="section">
    <div class="container">
        <div class="text-center mx-auto mb-5 gsap-reveal" style="max-width:780px;">
            <h2 class="display-5 fw-bold"><?php echo esc_html( $heading ); ?></h2>
            <p class="mt-3"><?php echo esc_html( $subtitle ); ?></p>
        </div>
        <div class="row text-center g-4 gsap-reveal">
            <?php foreach ( $stats as $s ) :
                $num = $s['number'] ?? '0';
                $suf = $s['suffix'] ?? '';
                $lab = $s['label']  ?? '';
            ?>
            <div class="col-6 col-md">
                <div class="success-num" data-count="<?php echo esc_attr( $num ); ?>" data-suffix="<?php echo esc_attr( $suf ); ?>"><?php echo esc_html( $num . $suf ); ?></div>
                <div class="success-label"><?php echo esc_html( $lab ); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
