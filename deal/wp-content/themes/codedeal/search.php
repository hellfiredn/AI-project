<?php get_header(); ?>
<section class="cd-page-hero">
    <div class="container">
        <h1 class="cd-page-title">Kết quả tìm kiếm cho: "<?php echo esc_html(get_search_query()); ?>"</h1>
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
        <?php else: ?>
            <div class="alert alert-info">Không tìm thấy kết quả phù hợp.</div>
        <?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
