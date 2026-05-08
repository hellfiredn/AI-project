<?php
/**
 * Single Deal — Bootstrap.
 */
get_header();
while (have_posts()): the_post();
$post_id = get_the_ID();
$price_old = (int) get_post_meta($post_id, '_cd_price_old', true);
$price_new = (int) get_post_meta($post_id, '_cd_price_new', true);
$url       = get_post_meta($post_id, '_cd_affiliate_url', true) ?: get_permalink($post_id);
$expires   = get_post_meta($post_id, '_cd_expires_at', true);
$store     = codedeal_get_first_store($post_id);
$color     = codedeal_store_color($store);
$img       = codedeal_deal_image($post_id, 'deal-hero');
$discount  = codedeal_discount_percent($price_old, $price_new);
$days_left = codedeal_days_left($expires);
?>
<section class="cd-section">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-lg-6">
                <div class="cd-single-deal__media">
                    <img src="<?php echo esc_url($img); ?>" alt="<?php the_title_attribute(); ?>">
                    <?php if ($discount > 0): ?><span class="cd-badge-discount">-<?php echo $discount; ?>%</span><?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <?php if ($store): ?>
                    <a class="cd-badge-store mb-2 d-inline-block" style="background:<?php echo esc_attr($color); ?>" href="<?php echo esc_url(get_term_link($store)); ?>"><?php echo esc_html($store->name); ?></a>
                <?php endif; ?>
                <h1 class="mb-3"><?php the_title(); ?></h1>
                <div class="d-flex align-items-baseline gap-3 mb-3 flex-wrap">
                    <span class="cd-price-new" style="font-size:32px"><?php echo codedeal_format_price($price_new); ?></span>
                    <?php if ($price_old > $price_new): ?>
                        <span class="cd-price-old"><?php echo codedeal_format_price($price_old); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($days_left !== null && $days_left >= 0): ?>
                    <p class="cd-single-deal__expiry mb-3"><i class="bi bi-clock"></i> Deal kết thúc trong <strong><?php echo $days_left; ?> ngày</strong></p>
                <?php endif; ?>
                <a class="btn btn-primary btn-lg w-100 mb-3" href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow sponsored noopener">
                    Mua ngay tại <?php echo $store ? esc_html($store->name) : 'cửa hàng'; ?> <i class="bi bi-arrow-right"></i>
                </a>
                <p class="cd-disclaimer mb-3"><i class="bi bi-info-circle"></i> Bài viết có chứa link tiếp thị liên kết. Khi bạn mua qua link, Tạp Hóa Giảm Giá có thể nhận hoa hồng nhỏ mà bạn không phải trả thêm chi phí.</p>
                <div class="cd-prose"><?php the_content(); ?></div>
            </div>
        </div>
    </div>
</section>
<?php endwhile; get_footer(); ?>
