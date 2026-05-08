<?php
$eyebrow  = webwp_field( 'eyebrow', 'TESTIMONIAL' );
$heading  = webwp_field( 'heading', 'What They Say?' );
$p1       = webwp_field( 'p1', 'TOTC has got more than 100k positive ratings from our users around the world.' );
$p2       = webwp_field( 'p2', 'Some of the students and teachers were greatly helped by the Skilline.' );
$p3       = webwp_field( 'p3', 'Are you too? Please give your assessment.' );
$cta_text = webwp_field( 'cta_text', 'Write your assessment' );
$cta_url  = webwp_field( 'cta_url', '#' );
$photo    = webwp_image_url( get_field( 'photo' ), 'testimonial.png' );
$quote    = webwp_field( 'quote', 'Thank you so much for your help. It’s exactly what I’ve been looking for. You won’t regret it. It really saves me time and effort. TOTC is exactly what our business has been lacking.' );
$author   = webwp_field( 'author', 'Gloria Rose' );
$reviews  = webwp_field( 'reviews_label', '12 reviews at Yelp' );
$stars    = (int) webwp_field( 'stars_count', 5 );
?>
<section class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5 gsap-reveal-left">
                <span class="eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
                <h2 class="display-5 fw-bold mt-3"><?php echo esc_html( $heading ); ?></h2>
                <p class="fs-5"><?php echo esc_html( $p1 ); ?></p>
                <p><?php echo esc_html( $p2 ); ?></p>
                <p><?php echo esc_html( $p3 ); ?></p>
                <a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn-outline-primary px-4 mt-2">
                    <?php echo esc_html( $cta_text ); ?> <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="col-lg-7 gsap-reveal-right">
                <div class="testimonial-img">
                    <div class="frame">
                        <img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $author ); ?>">
                    </div>
                    <div class="quote-card">
                        <p class="mb-3" style="color:var(--color-ink)">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="fw-bold" style="color:var(--color-dark)"><?php echo esc_html( $author ); ?></div>
                            <div class="text-end">
                                <div class="stars small"><?php for ( $i = 0; $i < $stars; $i++ ) echo '<i class="bi bi-star-fill"></i>'; ?></div>
                                <div class="small" style="color:var(--color-muted)"><?php echo esc_html( $reviews ); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
