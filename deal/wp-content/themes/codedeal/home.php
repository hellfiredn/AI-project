<?php
/**
 * Home — Blog page (khi WordPress dùng "Posts page" trong Settings → Reading).
 * Bootstrap 5 layout giống archive.
 */
get_header();
$blog_page = get_option('page_for_posts');
$title = $blog_page ? get_the_title($blog_page) : 'Blog';
?>
<section class="cd-page-hero">
    <div class="container">
        <h1 class="cd-page-title"><i class="bi bi-journal-text text-primary"></i> <?php echo esc_html($title); ?></h1>
        <p class="text-muted mb-0">Bài viết mới nhất về deal, mã giảm giá, mẹo mua sắm thông minh.</p>
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
            <div class="alert alert-info">Chưa có bài viết nào. Vào Accesstrade → Generator để tự sinh blog từ data sync.</div>
        <?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
