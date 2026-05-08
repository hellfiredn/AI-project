<?php
/**
 * Front page — Bootstrap 5.
 */
get_header();

$featured = new WP_Query([
    'post_type' => 'deal',
    'posts_per_page' => 5,
    'meta_query' => [['key' => '_cd_featured', 'value' => '1']],
]);
$slider_deals = $featured->have_posts() ? $featured->posts : [];

$today = current_time('Y-m-d');
$next_week = date('Y-m-d', strtotime('+7 days'));
$flash = new WP_Query([
    'post_type'      => 'deal',
    'posts_per_page' => 4,
    'meta_query'     => [
        'relation' => 'AND',
        ['key' => '_cd_expires_at', 'value' => $today, 'compare' => '>='],
        ['key' => '_cd_expires_at', 'value' => $next_week, 'compare' => '<='],
    ],
    'meta_key'       => '_cd_expires_at',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
]);
?>

<section class="cd-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="cd-hero__chip"><i class="bi bi-fire"></i> Deal mới mỗi ngày</span>
                <h1>Mã giảm giá & deal tốt<br><span>cho người mua sắm thông minh</span></h1>
                <p class="cd-hero__lead">Tạp Hóa Giảm Giá tổng hợp deal flash sale, mã coupon, freeship và mẹo mua rẻ từ Shopee, Lazada, Tiki, TikTok Shop, FPT Shop, Booking.com.</p>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <a class="btn btn-primary btn-lg" href="<?php echo esc_url(home_url('/deal/')); ?>">Xem deal hot ngay</a>
                    <a class="btn btn-outline-primary btn-lg" href="<?php echo esc_url(home_url('/coupon/')); ?>">Lấy mã giảm giá</a>
                </div>
                <div class="cd-hero__stats row text-start g-3">
                    <div class="col-4"><strong><?php echo (int) wp_count_posts('deal')->publish; ?></strong><small>deal đang hoạt động</small></div>
                    <div class="col-4"><strong><?php echo (int) wp_count_posts('coupon')->publish; ?></strong><small>mã giảm giá</small></div>
                    <div class="col-4"><strong><?php echo count(get_terms(['taxonomy'=>'store','hide_empty'=>false])); ?></strong><small>cửa hàng</small></div>
                </div>
            </div>

            <?php if (!empty($slider_deals)): ?>
                <div class="col-lg-6">
                    <div id="cdHeroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                        <div class="carousel-indicators">
                            <?php foreach ($slider_deals as $i => $_): ?>
                                <button type="button" data-bs-target="#cdHeroCarousel" data-bs-slide-to="<?php echo $i; ?>" <?php echo $i === 0 ? 'class="active" aria-current="true"' : ''; ?> aria-label="Slide <?php echo $i+1; ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-inner">
                            <?php foreach ($slider_deals as $idx => $sd):
                                $img = codedeal_deal_image($sd->ID, 'deal-hero');
                                $store = codedeal_get_first_store($sd->ID);
                                $color = codedeal_store_color($store);
                                $price_old = (int) get_post_meta($sd->ID, '_cd_price_old', true);
                                $price_new = (int) get_post_meta($sd->ID, '_cd_price_new', true);
                                $discount  = codedeal_discount_percent($price_old, $price_new);
                                $url       = get_post_meta($sd->ID, '_cd_affiliate_url', true) ?: get_permalink($sd->ID); ?>
                                <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                                    <div class="cd-hero__deal-card">
                                        <span class="cd-hero__badge">DEAL HOT</span>
                                        <img src="<?php echo esc_url($img); ?>" alt="">
                                        <div class="cd-hero__deal-body">
                                            <?php if ($store): ?>
                                                <span class="cd-badge-store" style="background:<?php echo esc_attr($color); ?>"><?php echo esc_html($store->name); ?></span>
                                            <?php endif; ?>
                                            <h3><?php echo esc_html($sd->post_title); ?></h3>
                                            <div class="d-flex align-items-baseline gap-2 flex-wrap mb-3">
                                                <span class="cd-price-new"><?php echo codedeal_format_price($price_new); ?></span>
                                                <?php if ($price_old > $price_new): ?>
                                                    <span class="cd-price-old"><?php echo codedeal_format_price($price_old); ?></span>
                                                    <span class="cd-badge-discount">-<?php echo $discount; ?>%</span>
                                                <?php endif; ?>
                                            </div>
                                            <a class="btn btn-primary w-100" href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow sponsored noopener">
                                                Mua ngay <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#cdHeroCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#cdHeroCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="cd-brand-showcase" aria-label="Banner Tạp Hóa Giảm Giá">
    <div class="container">
        <img src="<?php echo esc_url(CODEDEAL_URI . '/assets/brand/tgg-website-banner.png'); ?>" alt="Tạp Hóa Giảm Giá - săn deal thật, tiết kiệm mỗi ngày">
    </div>
</section>

<?php if ($flash->have_posts()): ?>
<section class="cd-flash">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <span class="cd-flash__bolt"><i class="bi bi-lightning-charge-fill"></i></span>
                <div>
                    <h2>Flash Sale — Sắp hết giờ</h2>
                    <p class="text-muted mb-0">Các deal kết thúc trong 7 ngày tới — nhanh tay kẻo lỡ.</p>
                </div>
            </div>
            <a class="cd-section__more" href="<?php echo esc_url(home_url('/deal/?orderby=newest')); ?>">Tất cả deal <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-3">
            <?php while ($flash->have_posts()): $flash->the_post();
                $pid = get_the_ID();
                $exp = get_post_meta($pid, '_cd_expires_at', true);
                $price_old = (int) get_post_meta($pid, '_cd_price_old', true);
                $price_new = (int) get_post_meta($pid, '_cd_price_new', true);
                $discount  = codedeal_discount_percent($price_old, $price_new);
                $url       = get_post_meta($pid, '_cd_affiliate_url', true) ?: get_permalink($pid);
                $img       = codedeal_deal_image($pid);
                $store     = codedeal_get_first_store($pid);
                $color     = codedeal_store_color($store);
                $end_ts    = $exp ? strtotime($exp . ' 23:59:59') * 1000 : 0;
                ?>
                <div class="col-sm-6 col-lg-3">
                    <article class="cd-card-deal cd-card-deal--flash">
                        <a class="cd-card-deal__image" href="<?php echo esc_url(get_permalink($pid)); ?>">
                            <img src="<?php echo esc_url($img); ?>" alt="">
                            <?php if ($discount > 0): ?>
                                <span class="cd-badge-discount">-<?php echo $discount; ?>%</span>
                            <?php endif; ?>
                            <?php if ($store): ?>
                                <span class="cd-badge-store" style="background:<?php echo esc_attr($color); ?>"><?php echo esc_html($store->name); ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="cd-card-deal__body">
                            <h3 class="cd-card-deal__title"><a href="<?php echo esc_url(get_permalink($pid)); ?>"><?php the_title(); ?></a></h3>
                            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                <span class="cd-price-new"><?php echo codedeal_format_price($price_new); ?></span>
                                <?php if ($price_old > $price_new): ?>
                                    <span class="cd-price-old"><?php echo codedeal_format_price($price_old); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($end_ts): ?>
                                <div class="cd-countdown" data-end="<?php echo $end_ts; ?>">
                                    <span class="cd-countdown__lbl">Hết hạn sau:</span>
                                    <span class="cd-countdown__box"><b data-d>--</b><small>ngày</small></span>
                                    <span class="cd-countdown__box"><b data-h>--</b><small>giờ</small></span>
                                    <span class="cd-countdown__box"><b data-m>--</b><small>phút</small></span>
                                    <span class="cd-countdown__box"><b data-s>--</b><small>giây</small></span>
                                </div>
                            <?php endif; ?>
                            <a class="btn btn-primary mt-auto" href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow sponsored noopener">
                                Săn ngay <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="cd-promo-band__col">
                    <span class="cd-promo-band__icon"><i class="bi bi-bar-chart-steps text-primary"></i></span>
                    <div>
                        <strong>So sánh giá thông minh</strong>
                        <span>Cùng 1 sản phẩm trên 5+ sàn — biết ngay đâu rẻ nhất.</span>
                    </div>
                    <a class="btn btn-light-primary" href="<?php echo esc_url(home_url('/so-sanh-gia/')); ?>">Mở công cụ <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="cd-promo-band__col">
                    <span class="cd-promo-band__icon"><i class="bi bi-funnel text-primary"></i></span>
                    <div>
                        <strong>Lọc deal nâng cao</strong>
                        <span>Lọc theo cửa hàng, danh mục, mức giảm, khoảng giá.</span>
                    </div>
                    <a class="btn btn-light-primary" href="<?php echo esc_url(home_url('/deal/')); ?>">Lọc deal <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cd-section cd-section--stores">
    <div class="container">
        <?php codedeal_section_title('Cửa hàng phổ biến', 'Click vào logo để xem tất cả deal & mã của cửa hàng'); ?>
        <?php codedeal_render_store_logos(8); ?>
    </div>
</section>

<?php
// Top bán chạy — pull từ deal có _cd_at_source = 'top_products' (sync từ Accesstrade /v1/top_products)
$top_q = new WP_Query([
    'post_type'      => 'deal',
    'posts_per_page' => 8,
    'meta_query'     => [['key' => '_cd_at_source', 'value' => 'top_products']],
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>
<?php if ($top_q->have_posts()): ?>
<section class="cd-section bg-white border-top">
    <div class="container">
        <?php codedeal_section_title('🏆 Top bán chạy', 'Sản phẩm được mua nhiều nhất tuần này — dữ liệu từ Accesstrade', home_url('/deal/?orderby=top'), 'Xem tất cả'); ?>
        <div class="row g-3">
            <?php while ($top_q->have_posts()): $top_q->the_post(); ?>
                <div class="col-sm-6 col-lg-3">
                    <?php codedeal_render_deal_card(get_the_ID()); ?>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="cd-section">
    <div class="container">
        <?php codedeal_section_title('🔥 Deal nổi bật', 'Deal được auto-flag theo mức giảm giá hoặc do biên tập chọn', home_url('/deal/'), 'Tất cả deal'); ?>
        <div class="row g-3">
            <?php
            // Featured = _cd_featured = '1' (auto-flag theo source/discount, hoặc tick tay trong admin)
            $deals = new WP_Query([
                'post_type'      => 'deal',
                'posts_per_page' => 8,
                'meta_query'     => [['key' => '_cd_featured', 'value' => '1']],
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]);
            // Fallback: nếu chưa có deal nào được flag (mới cài plugin) → hiển thị 8 deal mới nhất
            if (!$deals->have_posts()) {
                wp_reset_postdata();
                $deals = new WP_Query(['post_type' => 'deal', 'posts_per_page' => 8]);
            }
            while ($deals->have_posts()) {
                $deals->the_post();
                echo '<div class="col-sm-6 col-lg-3">';
                codedeal_render_deal_card(get_the_ID());
                echo '</div>';
            }
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>

<section class="cd-section bg-white border-top">
    <div class="container">
        <?php codedeal_section_title('💳 Mã giảm giá mới nhất', 'Click "Copy" để dán vào ô mã giảm giá khi thanh toán', home_url('/coupon/'), 'Tất cả mã'); ?>
        <div class="row g-3">
            <?php
            $coupons = new WP_Query(['post_type' => 'coupon', 'posts_per_page' => 6]);
            while ($coupons->have_posts()) {
                $coupons->the_post();
                echo '<div class="col-md-6 col-lg-4">';
                codedeal_render_coupon_card(get_the_ID());
                echo '</div>';
            }
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>

<section class="cd-section">
    <div class="container">
        <?php codedeal_section_title('📚 Blog mua sắm thông minh', 'Hướng dẫn, so sánh, review từ Tạp Hóa Giảm Giá', home_url('/blog/'), 'Xem blog'); ?>
        <div class="row g-3">
            <?php
            $posts_q = new WP_Query(['post_type' => 'post', 'posts_per_page' => 3]);
            while ($posts_q->have_posts()) {
                $posts_q->the_post();
                echo '<div class="col-md-6 col-lg-4">';
                codedeal_render_blog_card(get_the_ID());
                echo '</div>';
            }
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>

<section class="cd-cta-band">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2>Theo dõi deal mới mỗi ngày</h2>
                <p>Nhận bài tổng hợp mã giảm giá, deal đáng mua và mẹo tiết kiệm khi mua online.</p>
            </div>
            <a class="btn btn-lg" style="background:#fff;color:var(--cd-primary)" href="https://www.facebook.com/profile.php?id=61588847056636" target="_blank" rel="noopener">
                Theo dõi fanpage <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<?php get_footer(); ?>
