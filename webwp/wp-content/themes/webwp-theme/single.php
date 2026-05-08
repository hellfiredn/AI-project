<?php
get_header();
$related = new WP_Query(
    [
        'post_type'      => 'post',
        'posts_per_page' => 3,
        'post__not_in'   => [ get_the_ID() ],
    ]
);
?>

<?php while ( have_posts() ) : the_post(); ?>
    <section class="page-hero">
        <div class="container">
            <span class="page-kicker"><?php echo get_the_category() ? esc_html( get_the_category()[0]->name ) : 'Article'; ?></span>
            <h1 class="page-title"><?php the_title(); ?></h1>
            <p class="page-lead"><?php echo esc_html( get_the_date() . ' • ' . get_the_author() ); ?></p>
        </div>
    </section>

    <section class="page-section">
        <div class="container article-layout">
            <article <?php post_class( 'article-card' ); ?>>
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="article-hero"><?php the_post_thumbnail( 'large', [ 'class' => 'w-100' ] ); ?></div>
                <?php endif; ?>
                <div class="entry-content"><?php the_content(); ?></div>
            </article>

            <aside class="article-sidebar">
                <div class="article-widget">
                    <h2>More stories</h2>
                    <?php if ( $related->have_posts() ) : ?>
                        <?php while ( $related->have_posts() ) : $related->the_post(); ?>
                            <article class="mini-post">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                <span><?php echo esc_html( get_the_date() ); ?></span>
                            </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    <?php else : ?>
                        <?php foreach ( webwp_fallback_articles() as $article ) : ?>
                            <article class="mini-post">
                                <a href="#"><?php echo esc_html( $article['title'] ); ?></a>
                                <span><?php echo esc_html( $article['author'] ); ?></span>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </section>
<?php endwhile; ?>

<?php get_footer();
