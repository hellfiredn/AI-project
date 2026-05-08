<?php
/**
 * Single Coupon — Bootstrap.
 */
get_header();
while (have_posts()): the_post();
$post_id = get_the_ID();
$code    = get_post_meta($post_id, '_cd_code', true);
$url     = get_post_meta($post_id, '_cd_affiliate_url', true) ?: get_permalink($post_id);
$expires = get_post_meta($post_id, '_cd_expires_at', true);
$min     = (int) get_post_meta($post_id, '_cd_min_order', true);
$store   = codedeal_get_first_store($post_id);
$color   = codedeal_store_color($store);
$days_left = codedeal_days_left($expires);
?>
<section class="cd-section" style="--store-color:<?php echo esc_attr($color); ?>">
    <div class="container">
        <div class="mx-auto text-center" style="max-width:720px">
            <?php if ($store): ?>
                <a class="cd-badge-store mb-3 d-inline-block" style="background:<?php echo esc_attr($color); ?>" href="<?php echo esc_url(get_term_link($store)); ?>"><?php echo esc_html($store->name); ?></a>
            <?php endif; ?>
            <h1 class="mb-3"><?php the_title(); ?></h1>
            <div class="cd-prose mb-3"><?php the_content(); ?></div>
            <?php if ($min > 0): ?><p><i class="bi bi-cart"></i> Đơn tối thiểu: <strong><?php echo codedeal_format_price($min); ?></strong></p><?php endif; ?>
            <?php if ($days_left !== null): ?><p><i class="bi bi-clock"></i> Còn <strong><?php echo max($days_left, 0); ?> ngày</strong></p><?php endif; ?>

            <div class="card border-0 shadow-sm p-4 mt-4">
                <?php if ($code): ?>
                    <button class="cd-coupon-big__code" data-code="<?php echo esc_attr($code); ?>" type="button">
                        <span><?php echo esc_html($code); ?></span>
                        <small>Nhấn để copy</small>
                    </button>
                    <a class="btn btn-primary btn-lg mt-3" href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow sponsored noopener">Đến cửa hàng & dán mã <i class="bi bi-arrow-right"></i></a>
                <?php else: ?>
                    <div class="cd-coupon-big__notice">
                        <i class="bi bi-gift fs-1 text-primary"></i>
                        <p class="mb-0 mt-2"><strong>Ưu đãi tự động</strong> — không cần nhập mã. Click bên dưới để được giảm trực tiếp tại cửa hàng.</p>
                    </div>
                    <a class="btn btn-primary btn-lg mt-3" href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow sponsored noopener"><i class="bi bi-cart"></i> Nhận ưu đãi ngay <i class="bi bi-arrow-right"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endwhile; get_footer(); ?>
