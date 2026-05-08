<?php
/**
 * Block: totc/hero
 * Fields: heading_prefix, heading_highlight, heading_suffix, lead,
 *         btn_primary_text, btn_primary_url, btn_play_text,
 *         hero_image, float_cards (repeater: icon_class, tone, title, subtitle, button_text, button_url)
 */
$prefix    = webwp_field( 'heading_prefix', 'Studying' );
$highlight = webwp_field( 'heading_highlight', '' ); // optional second highlight
$suffix    = webwp_field( 'heading_suffix', 'Online is<br>now much easier' );
$lead      = webwp_field( 'lead', 'TOTC is an interesting platform that will teach you in more an interactive way.' );
$btn_txt   = webwp_field( 'btn_primary_text', 'Join for free' );
$btn_url   = webwp_field( 'btn_primary_url', '#' );
$play_txt  = webwp_field( 'btn_play_text', 'Watch how it works' );
$hero_img  = webwp_image_url( get_field( 'hero_image' ), 'hero-girl.png' );
$cards     = get_field( 'float_cards' ) ?: [];
?>
<section class="hero <?php echo esc_attr( $block['className'] ?? '' ); ?>">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <h1>
                    <span class="brush"><?php echo esc_html( $prefix ); ?></span>
                    <?php echo ' ' . wp_kses_post( $suffix ); ?>
                </h1>
                <p class="lead hero-lead mt-3"><?php echo esc_html( $lead ); ?></p>
                <div class="d-flex flex-wrap gap-3 mt-4 align-items-center hero-cta">
                    <a href="<?php echo esc_url( $btn_url ); ?>" class="btn btn-white px-4 py-2 fs-6"><?php echo esc_html( $btn_txt ); ?></a>
                    <span class="d-inline-flex align-items-center gap-2">
                        <button type="button" class="cta-play" aria-label="Play"><i class="bi bi-play-fill"></i></button>
                        <span><?php echo esc_html( $play_txt ); ?></span>
                    </span>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-media hero-visual">
                    <div class="girl">
                        <img src="<?php echo esc_url( $hero_img ); ?>" alt="">
                    </div>
                    <?php
                    $positions = [ 'float-card--250k', 'float-card--congrat', 'float-card--class' ];
                    foreach ( $cards as $i => $c ) :
                        $pos   = $positions[ $i ] ?? '';
                        $tone  = $c['tone'] ?? 'teal';
                        $icon  = $c['icon_class'] ?? 'bi-journal-bookmark-fill';
                        $btn_t = $c['button_text'] ?? '';
                        $btn_u = $c['button_url']  ?? '#';
                    ?>
                    <div class="float-card <?php echo esc_attr( $pos ); ?>">
                        <span class="ico <?php echo esc_attr( $tone ); ?>"><i class="bi <?php echo esc_attr( $icon ); ?>"></i></span>
                        <div class="flex-body">
                            <div class="t-title"><?php echo esc_html( $c['title'] ?? '' ); ?></div>
                            <div class="t-sub"><?php echo esc_html( $c['subtitle'] ?? '' ); ?></div>
                            <?php if ( $btn_t ) : ?>
                                <a href="<?php echo esc_url( $btn_u ); ?>" class="btn"><?php echo esc_html( $btn_t ); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<svg class="hero-curve" viewBox="0 0 1440 140" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path fill="#49BBBD" d="M0,0 H1440 V140 C1120,-8 320,26 0,140 Z"/>
</svg>
