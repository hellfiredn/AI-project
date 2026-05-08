<?php
/**
 * Archive — All coupons (Bootstrap).
 */
get_header(); ?>
<section class="cd-page-hero">
    <div class="container">
        <h1 class="cd-page-title"><i class="bi bi-ticket-perforated text-primary"></i> Tất cả mã giảm giá</h1>
        <p class="text-muted mb-0">Mã coupon mới nhất từ các sàn TMĐT & cửa hàng lớn — click "Copy" để sử dụng.</p>
    </div>
</section>
<section class="cd-section">
    <div class="container">
        <?php if (have_posts()): ?>
            <div class="row g-3">
                <?php while (have_posts()): the_post(); ?>
                    <div class="col-md-6 col-lg-4"><?php codedeal_render_coupon_card(get_the_ID()); ?></div>
                <?php endwhile; ?>
            </div>
            <nav class="d-flex justify-content-center mt-4">
                <?php the_posts_pagination(['prev_text' => '<i class="bi bi-arrow-left"></i>', 'next_text' => '<i class="bi bi-arrow-right"></i>', 'type' => 'list']); ?>
            </nav>
        <?php else: ?>
            <div class="alert alert-info">Chưa có mã nào.</div>
        <?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
