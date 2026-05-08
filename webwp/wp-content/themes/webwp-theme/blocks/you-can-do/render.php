<?php
$heading_prefix = webwp_field( 'heading_prefix', 'Everything you can do in a physical classroom,' );
$heading_accent = webwp_field( 'heading_accent', 'you can do with TOTC' );
$description    = webwp_field( 'description', 'TOTC’s school management software helps traditional and online schools manage scheduling, attendance, payments and virtual classrooms all in one secure cloud-based system.' );
$link_text      = webwp_field( 'link_text', 'Learn more' );
$link_url       = webwp_field( 'link_url', '#' );
$video_image    = webwp_image_url( get_field( 'video_image' ), 'teacher-lesson.png' );
$video_url      = webwp_field( 'video_url', '' );
?>
<section class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 gsap-reveal-left">
                <h2 class="display-6 fw-bold">
                    <?php echo esc_html( $heading_prefix ); ?>
                    <span class="text-primary-brand"><?php echo esc_html( $heading_accent ); ?></span>
                </h2>
                <p class="mt-3"><?php echo esc_html( $description ); ?></p>
                <a href="<?php echo esc_url( $link_url ); ?>" class="link-underline"><?php echo esc_html( $link_text ); ?></a>
            </div>
            <div class="col-lg-6 gsap-reveal-right">
                <div class="video-panel">
                    <img src="<?php echo esc_url( $video_image ); ?>" alt="classroom">
                    <button type="button" class="play-btn" aria-label="Play video"<?php echo $video_url ? ' data-video="' . esc_attr( $video_url ) . '"' : ''; ?>>
                        <i class="bi bi-play-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
