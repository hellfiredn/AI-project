<?php
/**
 * Single blog post — Bootstrap.
 */
get_header();
while (have_posts()): the_post(); ?>
<article class="cd-section">
    <div class="container">
        <div class="mx-auto" style="max-width:760px">
            <header class="mb-4">
                <span class="text-muted small text-uppercase"><?php echo get_the_date(); ?> · <?php the_category(', '); ?></span>
                <h1 class="mt-2"><?php the_title(); ?></h1>
            </header>
            <?php if (has_post_thumbnail()): ?>
                <div class="rounded overflow-hidden mb-4"><?php the_post_thumbnail('deal-hero', ['class' => 'img-fluid']); ?></div>
            <?php endif; ?>
            <div class="cd-prose"><?php the_content(); ?></div>
        </div>
    </div>
</article>
<?php endwhile; get_footer(); ?>
