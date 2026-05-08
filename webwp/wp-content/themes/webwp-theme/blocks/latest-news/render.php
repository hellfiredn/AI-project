<?php
$heading  = webwp_field( 'heading', 'Latest News and Resources' );
$subtitle = webwp_field( 'subtitle', 'See the developments that have occurred to TOTC in the world.' );

$f_image = webwp_image_url( get_field( 'featured_image' ), 'tools-hero.png' );
$f_tag   = webwp_field( 'featured_tag', 'NEWS' );
$f_title = webwp_field( 'featured_title', 'Class adds $30 million to its balance sheet for a Zoom-friendly edtech solution' );
$f_body  = webwp_field( 'featured_body',  'Class, launched less than a year ago by Blackboard co-founder Michael Chasen, integrates exclusively with Zoom...' );

$items = get_field( 'items' ) ?: [
    [ 'tag_text' => 'PRESS RELEASE', 'tag_style' => 'press', 'title' => 'Class Technologies Inc. Closes $30 Million Series A Financing to Meet High Demand', 'body' => 'Class Technologies Inc., the company that created Class,...', 'image' => null, 'fallback' => 'avatar-3.png' ],
    [ 'tag_text' => 'NEWS',          'tag_style' => 'news',  'title' => 'Zoom\'s earliest investors are betting millions on a better Zoom for schools', 'body' => 'Zoom was never created to be a consumer product...', 'image' => null, 'fallback' => 'avatar-4.png' ],
    [ 'tag_text' => 'NEWS',          'tag_style' => 'news',  'title' => 'Former Blackboard CEO Raises $16M to Bring LMS Features to Zoom Classrooms', 'body' => 'This year, investors have reaped big financial returns from betting on Zoom...', 'image' => null, 'fallback' => 'avatar-1.png' ],
];
?>
<section class="section pt-5">
    <div class="container">
        <div class="mb-5 gsap-reveal text-center mx-auto" style="max-width:820px;">
            <h2 class="display-6 fw-bold"><?php echo esc_html( $heading ); ?></h2>
            <p><?php echo esc_html( $subtitle ); ?></p>
        </div>

        <div class="row g-4">
            <div class="col-lg-6 gsap-reveal">
                <article class="news-featured">
                    <img src="<?php echo esc_url( $f_image ); ?>" alt="">
                    <span class="tag-news"><?php echo esc_html( $f_tag ); ?></span>
                    <div class="bottom">
                        <h4 class="fw-bold"><?php echo esc_html( $f_title ); ?></h4>
                        <p class="small mb-0"><?php echo esc_html( $f_body ); ?></p>
                    </div>
                </article>
            </div>
            <div class="col-lg-6">
                <?php foreach ( $items as $n ) :
                    $src = webwp_image_url( $n['image'] ?? null, $n['fallback'] ?? '' );
                ?>
                    <article class="news-item gsap-reveal">
                        <div class="thumb">
                            <img src="<?php echo esc_url( $src ); ?>" alt="">
                            <span class="tag <?php echo esc_attr( $n['tag_style'] ?? 'news' ); ?>"><?php echo esc_html( $n['tag_text'] ?? '' ); ?></span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold" style="font-size:1.05rem;"><?php echo esc_html( $n['title'] ?? '' ); ?></h5>
                            <p class="small mb-0"><?php echo esc_html( $n['body'] ?? '' ); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
