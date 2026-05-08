<?php get_header(); ?>
<section class="cd-section text-center" style="padding:80px 0">
    <div class="container">
        <h1 style="font-size:120px; color:var(--cd-primary); margin:0">404</h1>
        <p class="lead text-muted mb-4">Ối, deal này đã hết hạn hoặc trang không tồn tại.</p>
        <a class="btn btn-primary btn-lg" href="<?php echo esc_url(home_url('/')); ?>">
            <i class="bi bi-house"></i> Về trang chủ
        </a>
    </div>
</section>
<?php get_footer(); ?>
