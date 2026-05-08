<?php
/**
 * Fallback index — Bootstrap.
 */
get_header(); ?>
<section class="cd-section">
    <div class="container">
        <h1 class="mb-4"><?php
            if (is_home()) echo 'Blog';
            elseif (is_search()) echo 'Kết quả tìm kiếm: ' . get_search_query();
            elseif (is_archive()) the_archive_title();
            else single_post_title();
        ?></h1>
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
            <div class="alert alert-info">Không có bài viết nào.</div>
        <?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
