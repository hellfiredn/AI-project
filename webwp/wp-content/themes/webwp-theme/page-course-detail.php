<?php
get_header();
$course = webwp_sample_courses()[0];
$curriculum = [
    'Introduction to interface systems',
    'Research workflows and student journey mapping',
    'Designing components for online teaching',
    'Live critique session and final handoff',
];
?>

<section class="page-hero">
    <div class="container course-detail-hero">
        <div>
            <span class="page-kicker"><?php echo esc_html( $course['category'] ); ?></span>
            <h1 class="page-title"><?php echo esc_html( $course['title'] ); ?></h1>
            <p class="page-lead">A premium class page inspired by the Figma file&rsquo;s cleaner public course experience.</p>
            <div class="course-meta">
                <span><i class="bi bi-star-fill"></i> <?php echo esc_html( $course['rating'] ); ?></span>
                <span><?php echo esc_html( $course['lessons'] ); ?></span>
                <span><?php echo esc_html( $course['duration'] ); ?></span>
            </div>
        </div>
        <div class="course-hero-card">
            <img src="<?php echo esc_url( $course['image'] ); ?>" alt="<?php echo esc_attr( $course['title'] ); ?>">
        </div>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <div class="course-detail-layout">
            <div class="course-overview">
                <h2>About this course</h2>
                <p>This course is built for educators, product teams, and community operators who want a smoother online classroom experience. It combines interface design, collaboration tooling, and teaching workflow design.</p>

                <h3>What you will learn</h3>
                <ul class="lesson-list">
                    <?php foreach ( $curriculum as $lesson ) : ?>
                        <li><i class="bi bi-check2-circle"></i><span><?php echo esc_html( $lesson ); ?></span></li>
                    <?php endforeach; ?>
                </ul>

                <div class="content-card">
                    <h3>Instructor</h3>
                    <p><strong><?php echo esc_html( $course['instructor'] ); ?></strong></p>
                    <p>Product educator focused on modern learning systems, remote workshop formats, and course UX.</p>
                </div>
            </div>

            <aside class="price-card">
                <strong class="price-card__value"><?php echo esc_html( $course['price'] ); ?></strong>
                <p>Includes lifetime access, downloadable resources, and community Q&amp;A.</p>
                <a class="btn btn-primary w-100" href="<?php echo esc_url( webwp_page_url( 'checkout' ) ); ?>">Enroll now</a>
                <a class="btn btn-outline-primary w-100" href="<?php echo esc_url( webwp_page_url( 'membership' ) ); ?>">Compare plans</a>
            </aside>
        </div>
    </div>
</section>

<?php get_footer();
