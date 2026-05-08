<?php
get_header();

$query = new WP_Query(
    [
        'post_type'      => 'post',
        'posts_per_page' => 7,
    ]
);

$articles = [];
if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
        $query->the_post();
        $articles[] = [
            'title'   => get_the_title(),
            'excerpt' => get_the_excerpt(),
            'image'   => get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: webwp_img( 'tools-hero.png' ),
            'tag'     => get_the_category() ? get_the_category()[0]->name : 'News',
            'author'  => get_the_author(),
            'url'     => get_permalink(),
            'date'    => get_the_date(),
        ];
    }
    wp_reset_postdata();
} else {
    foreach ( webwp_fallback_articles() as $article ) {
        $article['url']  = '#';
        $article['date'] = 'April 23, 2026';
        $articles[] = $article;
    }
}

$featured = array_shift( $articles );
?>

<section class="page-hero">
    <div class="container">
        <span class="page-kicker">Blog</span>
        <h1 class="page-title">Latest ideas, product notes, and learning resources.</h1>
        <p class="page-lead">A cleaner editorial layout for the community side of the platform, built in the same visual language as the landing page.</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <div class="blog-shell">
            <article class="blog-feature">
                <div class="blog-feature__media">
                    <img src="<?php echo esc_url( $featured['image'] ); ?>" alt="<?php echo esc_attr( $featured['title'] ); ?>">
                </div>
                <div class="blog-feature__body">
                    <span class="badge-chip"><?php echo esc_html( $featured['tag'] ); ?></span>
                    <h2><?php echo esc_html( $featured['title'] ); ?></h2>
                    <p><?php echo esc_html( $featured['excerpt'] ); ?></p>
                    <div class="meta-line"><?php echo esc_html( $featured['author'] . ' • ' . $featured['date'] ); ?></div>
                    <a class="btn btn-outline-primary" href="<?php echo esc_url( $featured['url'] ); ?>">Read article</a>
                </div>
            </article>

            <div class="blog-grid">
                <?php foreach ( $articles as $article ) : ?>
                    <article class="blog-card">
                        <img src="<?php echo esc_url( $article['image'] ); ?>" alt="<?php echo esc_attr( $article['title'] ); ?>">
                        <div class="blog-card__body">
                            <span class="badge-chip badge-chip--soft"><?php echo esc_html( $article['tag'] ); ?></span>
                            <h3><a href="<?php echo esc_url( $article['url'] ); ?>"><?php echo esc_html( $article['title'] ); ?></a></h3>
                            <p><?php echo esc_html( $article['excerpt'] ); ?></p>
                            <div class="meta-line"><?php echo esc_html( $article['author'] . ' • ' . $article['date'] ); ?></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer();
