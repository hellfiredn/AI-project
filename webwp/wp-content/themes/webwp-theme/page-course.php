<?php
get_header();
$courses = webwp_sample_courses();
?>

<section class="page-hero">
    <div class="container">
        <span class="page-kicker">Courses</span>
        <h1 class="page-title">Browse modern online classes built for interactive learning.</h1>
        <p class="page-lead">The catalog view follows the same clean typography, soft cards, and bright accent system from the Figma set.</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <div class="catalog-toolbar">
            <form class="catalog-search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
                <i class="bi bi-search"></i>
                <input type="search" name="s" placeholder="Search courses, authors, or topics">
            </form>
            <div class="catalog-pills">
                <span class="catalog-pill is-active">All</span>
                <span class="catalog-pill">Design</span>
                <span class="catalog-pill">Teaching</span>
                <span class="catalog-pill">Assessment</span>
                <span class="catalog-pill">Operations</span>
            </div>
        </div>

        <div class="course-grid">
            <?php foreach ( $courses as $course ) : ?>
                <article class="course-card">
                    <a class="course-card__media" href="<?php echo esc_url( webwp_page_url( 'course-detail' ) ); ?>">
                        <img src="<?php echo esc_url( $course['image'] ); ?>" alt="<?php echo esc_attr( $course['title'] ); ?>">
                    </a>
                    <div class="course-card__body">
                        <span class="badge-chip badge-chip--soft"><?php echo esc_html( $course['category'] ); ?></span>
                        <h3><a href="<?php echo esc_url( webwp_page_url( 'course-detail' ) ); ?>"><?php echo esc_html( $course['title'] ); ?></a></h3>
                        <div class="meta-line"><?php echo esc_html( 'By ' . $course['instructor'] ); ?></div>
                        <div class="course-meta">
                            <span><i class="bi bi-star-fill"></i> <?php echo esc_html( $course['rating'] ); ?></span>
                            <span><?php echo esc_html( $course['lessons'] ); ?></span>
                            <span><?php echo esc_html( $course['duration'] ); ?></span>
                        </div>
                        <div class="course-card__footer">
                            <strong><?php echo esc_html( $course['price'] ); ?></strong>
                            <a href="<?php echo esc_url( webwp_page_url( 'course-detail' ) ); ?>" class="btn btn-outline-primary btn-sm">View detail</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php get_footer();
