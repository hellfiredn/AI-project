<?php
/**
 * Template Name: So sánh giá
 */
get_header();
$query = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
$store_filter = isset($_GET['stores']) ? array_filter(array_map('sanitize_title', explode(',', $_GET['stores']))) : [];

$results = []; $best = null;
if ($query !== '') {
    $args = ['post_type' => 'deal', 's' => $query, 'posts_per_page' => 30];
    if ($store_filter) $args['tax_query'] = [['taxonomy' => 'store', 'field' => 'slug', 'terms' => $store_filter]];
    $q = new WP_Query($args);
    while ($q->have_posts()) {
        $q->the_post();
        $pid = get_the_ID();
        $st  = codedeal_get_first_store($pid);
        $price_new = (int) get_post_meta($pid, '_cd_price_new', true);
        $price_old = (int) get_post_meta($pid, '_cd_price_old', true);
        $url       = get_post_meta($pid, '_cd_affiliate_url', true) ?: get_permalink($pid);
        if ($price_new <= 0) continue;
        $row = ['pid'=>$pid,'title'=>get_the_title(),'image'=>codedeal_deal_image($pid),'store'=>$st,'price_new'=>$price_new,'price_old'=>$price_old,'discount'=>codedeal_discount_percent($price_old,$price_new),'url'=>$url];
        $results[] = $row;
        if ($best === null || $row['price_new'] < $best['price_new']) $best = $row;
    }
    wp_reset_postdata();
}
$all_stores = get_terms(['taxonomy' => 'store', 'hide_empty' => false]);
?>

<section class="cd-page-hero">
    <div class="container">
        <h1 class="cd-page-title"><i class="bi bi-bar-chart-steps text-primary"></i> So sánh giá</h1>
        <p class="text-muted mb-0">Nhập tên sản phẩm — chúng tôi tìm các phiên bản trên Shopee, Lazada, Tiki, FPT Shop, CellphoneS… và sắp xếp theo giá rẻ nhất.</p>
    </div>
</section>

<section class="cd-section">
    <div class="container">
        <form class="card border-0 shadow-sm p-4 mb-4" method="get">
            <div class="input-group input-group-lg mb-3">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" class="form-control" name="q" placeholder="VD: iPhone 15 128GB" value="<?php echo esc_attr($query); ?>" required>
                <button class="btn btn-primary" type="submit">So sánh ngay <i class="bi bi-arrow-right"></i></button>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="text-muted small">Cửa hàng:</span>
                <?php foreach ($all_stores as $st):
                    $color = codedeal_store_color($st);
                    $checked = in_array($st->slug, $store_filter) ? 'checked' : ''; ?>
                    <label class="cd-compare-stores">
                        <input type="checkbox" name="stores[]" value="<?php echo esc_attr($st->slug); ?>" <?php echo $checked; ?>>
                        <span class="cd-store-chip" style="--c:<?php echo esc_attr($color); ?>"><?php echo esc_html($st->name); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </form>

        <?php if ($query === ''): ?>
            <div class="card border-0 shadow-sm p-5 text-center text-muted">
                <p class="mb-0">👆 Thử tìm: <a href="?q=iPhone 15">iPhone 15</a> · <a href="?q=tai nghe">Tai nghe</a> · <a href="?q=robot hut bui">Robot hút bụi</a></p>
            </div>
        <?php elseif (empty($results)): ?>
            <div class="alert alert-info">Không tìm thấy sản phẩm nào với từ khoá "<strong><?php echo esc_html($query); ?></strong>".</div>
        <?php else:
            usort($results, fn($a,$b) => $a['price_new'] <=> $b['price_new']); ?>

            <div class="cd-compare-best mb-4">
                <div class="text-uppercase fw-semibold opacity-75 small mb-1">🏆 Giá rẻ nhất hiện tại</div>
                <div class="cd-compare-best__price"><?php echo codedeal_format_price($best['price_new']); ?></div>
                <div class="mb-3">tại <strong><?php echo $best['store'] ? esc_html($best['store']->name) : 'cửa hàng'; ?></strong></div>
                <a class="btn btn-lg" style="background:#fff;color:var(--cd-primary)" href="<?php echo esc_url($best['url']); ?>" target="_blank" rel="nofollow sponsored noopener">
                    Mua ngay tại đây <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 cd-compare-table align-middle">
                        <thead style="background:var(--cd-primary-50)">
                            <tr>
                                <th>Sản phẩm</th><th>Cửa hàng</th><th>Giá hiện tại</th><th>Giảm</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($results as $r):
                            $is_best = $r === $best;
                            $color = codedeal_store_color($r['store']); ?>
                            <tr class="<?php echo $is_best ? 'is-best' : ''; ?>">
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img src="<?php echo esc_url($r['image']); ?>" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:8px">
                                        <a href="<?php echo esc_url(get_permalink($r['pid'])); ?>" class="text-decoration-none text-dark fw-semibold"><?php echo esc_html($r['title']); ?></a>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($r['store']): ?>
                                        <span class="cd-store-chip" style="--c:<?php echo esc_attr($color); ?>;background:<?php echo esc_attr($color); ?>;color:#fff;border-color:<?php echo esc_attr($color); ?>"><?php echo esc_html($r['store']->name); ?></span>
                                    <?php else: echo '—'; endif; ?>
                                </td>
                                <td>
                                    <div class="cd-price-new fs-6"><?php echo codedeal_format_price($r['price_new']); ?></div>
                                    <?php if ($r['price_old'] && $r['price_old'] > $r['price_new']): ?>
                                        <div class="cd-price-old"><?php echo codedeal_format_price($r['price_old']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['discount'] > 0): ?>
                                        <span class="cd-badge-discount">-<?php echo $r['discount']; ?>%</span>
                                    <?php else: echo '—'; endif; ?>
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="<?php echo esc_url($r['url']); ?>" target="_blank" rel="nofollow sponsored noopener">Mua <i class="bi bi-arrow-right"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
