<?php
/**
 * Fallback template.
 */
get_header(); ?>

<section class="py-5">
    <div class="container">
        <?php if ( have_posts() ) : ?>
            <div class="row g-4">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium_large', [ 'class' => 'card-img-top' ] ); ?></a>
                            <?php endif; ?>
                            <div class="card-body">
                                <h3 class="h5 card-title"><a class="text-decoration-none text-dark" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <div class="card-text small text-muted"><?php the_excerpt(); ?></div>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            <div class="mt-5"><?php the_posts_pagination(); ?></div>
        <?php else : ?>
            <p class="text-muted">No posts yet.</p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer();
