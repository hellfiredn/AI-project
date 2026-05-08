<?php
/**
 * Store taxonomy — Bootstrap.
 */
get_header();
$term  = get_queried_object();
$color = codedeal_store_color($term);
$desc  = term_description($term);
?>
<section class="cd-page-hero" style="--store-color:<?php echo esc_attr($color); ?>">
    <div class="container">
        <span class="badge mb-2" style="background:<?php echo esc_attr($color); ?>; color:#fff"><?php echo esc_html($term->name); ?></span>
        <h1 class="cd-page-title">Deal & mã giảm giá <?php echo esc_html($term->name); ?></h1>
        <?php if ($desc): ?><p class="text-muted mb-0"><?php echo wp_kses_post($desc); ?></p><?php endif; ?>
    </div>
</section>

<section class="cd-section">
    <div class="container">
        <h2 class="cd-section__title mb-3">Deal mới nhất</h2>
        <?php
        $deals = new WP_Query([
            'post_type' => 'deal',
            'posts_per_page' => 12,
            'tax_query' => [['taxonomy' => 'store', 'field' => 'slug', 'terms' => $term->slug]],
        ]);
        if ($deals->have_posts()): ?>
            <div class="row g-3">
                <?php while ($deals->have_posts()) { $deals->the_post();
                    echo '<div class="col-sm-6 col-lg-4 col-xl-3">';
                    codedeal_render_deal_card(get_the_ID());
                    echo '</div>';
                } ?>
            </div>
        <?php else: echo '<div class="alert alert-info">Chưa có deal nào.</div>'; endif;
        wp_reset_postdata(); ?>

        <h2 class="cd-section__title mt-5 mb-3">Mã giảm giá</h2>
        <?php
        $coupons = new WP_Query([
            'post_type' => 'coupon',
            'posts_per_page' => 8,
            'tax_query' => [['taxonomy' => 'store', 'field' => 'slug', 'terms' => $term->slug]],
        ]);
        if ($coupons->have_posts()): ?>
            <div class="row g-3">
                <?php while ($coupons->have_posts()) { $coupons->the_post();
                    echo '<div class="col-md-6 col-lg-4">';
                    codedeal_render_coupon_card(get_the_ID());
                    echo '</div>';
                } ?>
            </div>
        <?php else: echo '<div class="alert alert-info">Chưa có mã nào.</div>'; endif;
        wp_reset_postdata(); ?>
    </div>
</section>
<?php get_footer(); ?>
