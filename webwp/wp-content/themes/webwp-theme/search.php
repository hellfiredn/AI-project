<?php
get_header();
$query_text = get_search_query();
?>

<section class="page-hero">
    <div class="container">
        <span class="page-kicker">Search</span>
        <h1 class="page-title"><?php echo $query_text ? esc_html( 'Results for “' . $query_text . '”' ) : 'Search learning resources'; ?></h1>
        <p class="page-lead">A bright search results page in the same family as the other public views.</p>
        <form class="catalog-search search-form-hero" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
            <i class="bi bi-search"></i>
            <input type="search" name="s" value="<?php echo esc_attr( $query_text ); ?>" placeholder="Search articles, courses, or topics">
        </form>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <div class="search-layout">
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="search-result-card">
                        <span class="badge-chip badge-chip--soft"><?php echo esc_html( get_post_type() ); ?></span>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p><?php echo esc_html( get_the_excerpt() ); ?></p>
                    </article>
                <?php endwhile; ?>
            <?php else : ?>
                <?php foreach ( webwp_sample_courses() as $course ) : ?>
                    <article class="search-result-card">
                        <span class="badge-chip badge-chip--soft"><?php echo esc_html( $course['category'] ); ?></span>
                        <h2><a href="<?php echo esc_url( webwp_page_url( 'course-detail' ) ); ?>"><?php echo esc_html( $course['title'] ); ?></a></h2>
                        <p><?php echo esc_html( $course['instructor'] . ' • ' . $course['lessons'] . ' • ' . $course['duration'] ); ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer();
