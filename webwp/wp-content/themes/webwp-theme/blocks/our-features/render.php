<?php
$heading_prefix = webwp_field( 'heading_prefix', 'Our' );
$heading_accent = webwp_field( 'heading_accent', 'Features' );
$subtitle       = webwp_field( 'subtitle', 'This very extraordinary feature, can make learning activities more efficient.' );
$cta_text       = webwp_field( 'cta_text', 'See more features' );
$cta_url        = webwp_field( 'cta_url', '#' );
$rows = get_field( 'rows' ) ?: [
    [ 'title_html' => 'A <span class="text-accent">user interface</span> designed<br>for the classroom', 'body' => 'Teachers can easily see all students and class data at one time. TA’s and presenters can be moved to the front of the class.', 'image' => null, 'flip' => false, 'dark_bg' => true,  'fallback' => 'feat-ui.png' ],
    [ 'title_html' => '<span class="text-accent">Tools</span> For Teachers<br>And Learners',             'body' => 'Class has a dynamic set of teaching tools built to be deployed and used during class.', 'image' => null, 'flip' => true,  'dark_bg' => false, 'fallback' => 'feat-tools.png' ],
    [ 'title_html' => 'Assessments,<br><span class="text-accent">Quizzes, Tests</span>',                 'body' => 'Easily launch live assignments, quizzes, and tests. Results are automatically graded.', 'image' => null, 'flip' => false, 'dark_bg' => false, 'fallback' => 'feat-quiz.png' ],
    [ 'title_html' => '<span class="text-accent">Class Management</span><br>Tools for Educators',        'body' => 'Class provides tools to help run and manage the class such as Class Roster, Attendance and Gradebook.', 'image' => null, 'flip' => true,  'dark_bg' => false, 'fallback' => 'feat-gradebook.png' ],
    [ 'title_html' => '<span class="text-accent">One-on-One</span><br>Discussions',                       'body' => 'Teachers and teacher assistants can talk with students privately without leaving the Zoom environment.', 'image' => null, 'flip' => false, 'dark_bg' => false, 'fallback' => 'feat-121.png' ],
];
?>
<section class="features-section">
    <div class="container">
        <div class="text-center mx-auto mb-5 gsap-reveal" style="max-width:720px;">
            <h2 class="display-6 fw-bold"><?php echo esc_html( $heading_prefix ); ?> <span class="text-accent"><?php echo esc_html( $heading_accent ); ?></span></h2>
            <p><?php echo esc_html( $subtitle ); ?></p>
        </div>

        <?php foreach ( $rows as $r ) :
            $flip = ! empty( $r['flip'] );
            $dark = ! empty( $r['dark_bg'] );
            $left_order  = $flip ? 'order-lg-2' : '';
            $right_order = $flip ? 'order-lg-1' : '';
            $ill_cls = $dark ? 'feature-illustration feature-illustration--dark' : 'feature-illustration';
            $src = webwp_image_url( $r['image'] ?? null, $r['fallback'] ?? '' );
        ?>
        <div class="row feature-row align-items-center g-4">
            <div class="col-lg-6 <?php echo esc_attr( $left_order ); ?> gsap-reveal-right">
                <h3 class="display-6 fw-bold"><?php echo wp_kses_post( $r['title_html'] ?? '' ); ?></h3>
                <p class="mt-3"><?php echo wp_kses_post( $r['body'] ?? '' ); ?></p>
            </div>
            <div class="col-lg-6 <?php echo esc_attr( $right_order ); ?> gsap-reveal-left">
                <div class="<?php echo esc_attr( $ill_cls ); ?>">
                    <img src="<?php echo esc_url( $src ); ?>" alt="">
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if ( $cta_text ) : ?>
        <div class="text-center mt-5 gsap-reveal">
            <a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn-outline-primary px-4"><?php echo esc_html( $cta_text ); ?></a>
        </div>
        <?php endif; ?>
    </div>
</section>
