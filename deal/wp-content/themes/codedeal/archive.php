<?php
/**
 * Generic archive (categories/tags/blog) — Bootstrap.
 */
get_header(); ?>
<section class="cd-page-hero">
    <div class="container">
        <h1 class="cd-page-title"><?php the_archive_title(); ?></h1>
        <?php the_archive_description('<p class="text-muted mb-0">', '</p>'); ?>
    </div>
</section>
<section class="cd-section">
    <div class="container">
        <?php if (have_posts()): ?>
            <div class="row g-3">
                <?php while (have_posts()): the_post(); ?>
                    <div class="col-md-6 col-lg-4"><?php codedeal_render_blog_card(get_the_ID()); ?></div>
                <?php endwhile; ?>
            </div>
            <nav class="d-flex justify-content-center mt-4">
                <?php the_posts_pagination(['prev_text' => '<i class="bi bi-arrow-left"></i>', 'next_text' => '<i class="bi bi-arrow-right"></i>', 'type' => 'list']); ?>
            </nav>
        <?php else: ?>
            <div class="alert alert-info">Chưa có nội dung nào.</div>
        <?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
