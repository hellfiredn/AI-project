<?php
/**
 * Template tags & shortcuts — Bootstrap 5 markup.
 */
if (!defined('ABSPATH')) exit;

function codedeal_render_deal_card($post_id) {
    $price_old = (int) get_post_meta($post_id, '_cd_price_old', true);
    $price_new = (int) get_post_meta($post_id, '_cd_price_new', true);
    $url       = get_post_meta($post_id, '_cd_affiliate_url', true) ?: get_permalink($post_id);
    $expires   = get_post_meta($post_id, '_cd_expires_at', true);
    $store     = codedeal_get_first_store($post_id);
    $color     = codedeal_store_color($store);
    $img       = codedeal_deal_image($post_id);
    $discount  = codedeal_discount_percent($price_old, $price_new);
    $days_left = codedeal_days_left($expires);
    $title     = get_the_title($post_id);
    ?>
    <article class="cd-card-deal">
        <a class="cd-card-deal__image" href="<?php echo esc_url(get_permalink($post_id)); ?>">
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
            <?php if ($discount > 0): ?>
                <span class="cd-badge-discount">-<?php echo $discount; ?>%</span>
            <?php endif; ?>
            <?php if ($store): ?>
                <span class="cd-badge-store" style="background:<?php echo esc_attr($color); ?>">
                    <?php echo esc_html($store->name); ?>
                </span>
            <?php endif; ?>
        </a>
        <div class="cd-card-deal__body">
            <h3 class="cd-card-deal__title">
                <a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html($title); ?></a>
            </h3>
            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                <?php if ($price_new): ?>
                    <span class="cd-price-new"><?php echo codedeal_format_price($price_new); ?></span>
                <?php endif; ?>
                <?php if ($price_old && $price_old > $price_new): ?>
                    <span class="cd-price-old"><?php echo codedeal_format_price($price_old); ?></span>
                <?php endif; ?>
            </div>
            <?php if ($days_left !== null && $days_left >= 0): ?>
                <div><span class="cd-meta-pill"><i class="bi bi-clock"></i> Còn <?php echo $days_left; ?> ngày</span></div>
            <?php endif; ?>
            <a class="btn btn-primary mt-auto" href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow sponsored noopener">
                Lấy deal ngay <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </article>
    <?php
}

function codedeal_render_coupon_card($post_id) {
    $code    = get_post_meta($post_id, '_cd_code', true);
    $url     = get_post_meta($post_id, '_cd_affiliate_url', true) ?: get_permalink($post_id);
    $expires = get_post_meta($post_id, '_cd_expires_at', true);
    $min     = (int) get_post_meta($post_id, '_cd_min_order', true);
    $store   = codedeal_get_first_store($post_id);
    $color   = codedeal_store_color($store);
    $days_left = codedeal_days_left($expires);
    ?>
    <article class="cd-card-coupon" style="--store-color:<?php echo esc_attr($color); ?>">
        <div class="cd-coupon__store">
            <?php if ($store): ?>
                <span class="cd-coupon__pill"><?php echo esc_html($store->name); ?></span>
            <?php endif; ?>
            <?php if ($days_left !== null): ?>
                <span class="cd-coupon__days"><i class="bi bi-clock"></i> Còn <?php echo max($days_left, 0); ?> ngày</span>
            <?php endif; ?>
        </div>
        <h3 class="cd-coupon__title">
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a>
        </h3>
        <p class="cd-coupon__desc"><?php echo esc_html(wp_trim_words(get_the_excerpt($post_id) ?: wp_strip_all_tags(get_the_content(null, false, $post_id)), 22)); ?></p>
        <?php if ($min > 0): ?>
            <p class="cd-coupon__min"><i class="bi bi-cart"></i> Đơn tối thiểu: <strong><?php echo codedeal_format_price($min); ?></strong></p>
        <?php endif; ?>
        <div class="cd-coupon__action <?php echo !$code ? 'cd-coupon__action--full' : ''; ?>">
            <?php if ($code): ?>
                <button class="cd-coupon__code" data-code="<?php echo esc_attr($code); ?>" type="button">
                    <span class="cd-coupon__code-text"><?php echo esc_html($code); ?></span>
                    <span class="cd-coupon__copy">Copy</span>
                </button>
                <a class="btn btn-light-primary" href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow sponsored noopener">
                    Đến cửa hàng <i class="bi bi-arrow-right"></i>
                </a>
            <?php else: ?>
                <a class="btn btn-primary" href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow sponsored noopener">
                    <i class="bi bi-gift"></i> Nhận ưu đãi tại cửa hàng <i class="bi bi-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </article>
    <?php
}

function codedeal_section_title($title, $subtitle = '', $more_url = '', $more_label = 'Xem tất cả') {
    ?>
    <header class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
        <div>
            <h2 class="cd-section__title"><?php echo esc_html($title); ?></h2>
            <?php if ($subtitle): ?>
                <p class="cd-section__sub"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>
        </div>
        <?php if ($more_url): ?>
            <a class="cd-section__more" href="<?php echo esc_url($more_url); ?>">
                <?php echo esc_html($more_label); ?> <i class="bi bi-arrow-right"></i>
            </a>
        <?php endif; ?>
    </header>
    <?php
}

function codedeal_render_store_logos($limit = 8) {
    $terms = get_terms(['taxonomy' => 'store', 'hide_empty' => false, 'number' => $limit]);
    if (empty($terms) || is_wp_error($terms)) return;
    echo '<div class="row g-3">';
    foreach ($terms as $t) {
        $color = codedeal_store_color($t);
        echo '<div class="col-6 col-sm-4 col-md-3 col-lg-2">';
        echo '<a class="cd-store-tile" style="--store-color:' . esc_attr($color) . '" href="' . esc_url(get_term_link($t)) . '">';
        echo '<span class="cd-store-tile__name">' . esc_html($t->name) . '</span>';
        echo '<span class="cd-store-tile__count">' . (int) $t->count . ' deal</span>';
        echo '</a></div>';
    }
    echo '</div>';
}

function codedeal_render_blog_card($post_id) {
    ?>
    <article class="cd-card-blog">
        <a class="cd-card-blog__image" href="<?php echo esc_url(get_permalink($post_id)); ?>">
            <?php if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, 'deal-card');
            } else {
                echo '<img src="https://picsum.photos/seed/blog' . esc_attr($post_id) . '/600/400" alt="">';
            } ?>
        </a>
        <div class="cd-card-blog__body">
            <span class="cd-card-blog__date"><?php echo get_the_date('', $post_id); ?></span>
            <h3><a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
            <p><?php echo esc_html(wp_trim_words(get_the_excerpt($post_id) ?: get_post_field('post_content', $post_id), 22)); ?></p>
            <a class="cd-readmore" href="<?php echo esc_url(get_permalink($post_id)); ?>">Đọc tiếp <i class="bi bi-arrow-right"></i></a>
        </div>
    </article>
    <?php
}
