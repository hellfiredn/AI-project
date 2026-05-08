<?php
/**
 * Archive — All deals with Bootstrap filter sidebar.
 */
get_header();

$f_stores   = isset($_GET['store']) ? array_filter(array_map('sanitize_title', explode(',', $_GET['store']))) : [];
$f_cats     = isset($_GET['cat'])   ? array_filter(array_map('sanitize_title', explode(',', $_GET['cat']))) : [];
$f_min_p    = isset($_GET['min_price']) ? (int) $_GET['min_price'] : 0;
$f_max_p    = isset($_GET['max_price']) ? (int) $_GET['max_price'] : 0;
$f_min_disc = isset($_GET['min_discount']) ? (int) $_GET['min_discount'] : 0;
$f_orderby  = $_GET['orderby'] ?? 'newest';
$f_s        = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

$paged = max(1, get_query_var('paged') ? get_query_var('paged') : (int) ($_GET['page_no'] ?? 1));
$args = ['post_type' => 'deal', 'posts_per_page' => 16, 'paged' => $paged];

if ($f_s) $args['s'] = $f_s;
$tax = [];
if ($f_stores) $tax[] = ['taxonomy' => 'store', 'field' => 'slug', 'terms' => $f_stores];
if ($f_cats)   $tax[] = ['taxonomy' => 'category', 'field' => 'slug', 'terms' => $f_cats];
if ($tax) { $tax['relation'] = 'AND'; $args['tax_query'] = $tax; }
$meta = [];
if ($f_min_p) $meta[] = ['key' => '_cd_price_new', 'value' => $f_min_p, 'type' => 'NUMERIC', 'compare' => '>='];
if ($f_max_p) $meta[] = ['key' => '_cd_price_new', 'value' => $f_max_p, 'type' => 'NUMERIC', 'compare' => '<='];
if ($meta) { $meta['relation'] = 'AND'; $args['meta_query'] = $meta; }

switch ($f_orderby) {
    case 'price_asc':     $args['meta_key'] = '_cd_price_new'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'ASC';  break;
    case 'price_desc':    $args['meta_key'] = '_cd_price_new'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; break;
    case 'discount_desc': $args['meta_key'] = '_cd_price_old'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; break;
    default:              $args['orderby'] = 'date'; $args['order'] = 'DESC';
}

$q = new WP_Query($args);
$all_stores = get_terms(['taxonomy' => 'store', 'hide_empty' => false, 'number' => 30]);
$all_cats   = get_terms(['taxonomy' => 'category', 'hide_empty' => true, 'number' => 30]);
$has_filter = $f_stores || $f_cats || $f_min_p || $f_max_p || $f_min_disc || $f_s;
?>

<section class="cd-page-hero">
    <div class="container">
        <h1 class="cd-page-title"><i class="bi bi-fire text-danger"></i> Tất cả deal</h1>
        <p class="text-muted mb-0">Tìm thấy <strong><?php echo $q->found_posts; ?></strong> deal phù hợp.
            <?php if ($has_filter): ?> · <a href="<?php echo esc_url(home_url('/deal/')); ?>">Xoá bộ lọc</a><?php endif; ?>
        </p>
    </div>
</section>

<section class="cd-section">
    <div class="container">
        <div class="row g-4">

            <aside class="col-lg-3">
                <button class="btn btn-outline-primary w-100 d-lg-none mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#cdFilterOffcanvas">
                    <i class="bi bi-funnel"></i> Mở bộ lọc
                </button>
                <div class="d-none d-lg-block sticky-lg-top" style="top:90px">
                    <?php cd_render_filter_form($f_s, $f_stores, $f_cats, $f_min_p, $f_max_p, $f_min_disc, $f_orderby, $all_stores, $all_cats); ?>
                </div>
                <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="cdFilterOffcanvas">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title">Bộ lọc deal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                    </div>
                    <div class="offcanvas-body">
                        <?php cd_render_filter_form($f_s, $f_stores, $f_cats, $f_min_p, $f_max_p, $f_min_disc, $f_orderby, $all_stores, $all_cats); ?>
                    </div>
                </div>
            </aside>

            <div class="col-lg-9">
                <?php if ($q->have_posts()):
                    $shown = 0; ?>
                    <div class="row g-3">
                        <?php while ($q->have_posts()): $q->the_post();
                            if ($f_min_disc > 0) {
                                $po = (int) get_post_meta(get_the_ID(), '_cd_price_old', true);
                                $pn = (int) get_post_meta(get_the_ID(), '_cd_price_new', true);
                                if (codedeal_discount_percent($po, $pn) < $f_min_disc) continue;
                            }
                            $shown++; ?>
                            <div class="col-sm-6 col-xl-4"><?php codedeal_render_deal_card(get_the_ID()); ?></div>
                        <?php endwhile; ?>
                    </div>
                    <?php if (!$shown): ?>
                        <div class="alert alert-info mt-3">Không có deal nào khớp bộ lọc — thử nới rộng tiêu chí.</div>
                    <?php endif; ?>
                    <nav class="d-flex justify-content-center mt-4">
                        <?php echo paginate_links(['total' => $q->max_num_pages, 'current' => $paged, 'prev_text' => '<i class="bi bi-arrow-left"></i>', 'next_text' => '<i class="bi bi-arrow-right"></i>', 'type' => 'list', 'class' => 'pagination']); ?>
                    </nav>
                <?php else: ?>
                    <div class="alert alert-info">Không có deal nào khớp bộ lọc.</div>
                <?php endif; wp_reset_postdata(); ?>
            </div>

        </div>
    </div>
</section>

<?php
function cd_render_filter_form($f_s, $f_stores, $f_cats, $f_min_p, $f_max_p, $f_min_disc, $f_orderby, $all_stores, $all_cats) { ?>
    <form method="get" class="cd-filter">
        <div class="cd-filter__block">
            <h4>Tìm theo từ khoá</h4>
            <input type="search" name="s" value="<?php echo esc_attr($f_s); ?>" placeholder="VD: tai nghe, iphone…" class="form-control form-control-sm">
        </div>
        <div class="cd-filter__block">
            <h4>Cửa hàng</h4>
            <ul class="cd-filter__list">
                <?php foreach ($all_stores as $st):
                    $color = codedeal_store_color($st);
                    $checked = in_array($st->slug, $f_stores) ? 'checked' : ''; ?>
                    <li>
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input me-1" name="store[]" value="<?php echo esc_attr($st->slug); ?>" <?php echo $checked; ?>>
                            <span class="cd-filter__dot" style="background:<?php echo esc_attr($color); ?>"></span>
                            <?php echo esc_html($st->name); ?>
                            <small>(<?php echo (int) $st->count; ?>)</small>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if ($all_cats && !is_wp_error($all_cats)): ?>
        <div class="cd-filter__block">
            <h4>Danh mục</h4>
            <ul class="cd-filter__list">
                <?php foreach ($all_cats as $c):
                    $checked = in_array($c->slug, $f_cats) ? 'checked' : ''; ?>
                    <li>
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input me-1" name="cat[]" value="<?php echo esc_attr($c->slug); ?>" <?php echo $checked; ?>>
                            <?php echo esc_html($c->name); ?>
                            <small>(<?php echo (int) $c->count; ?>)</small>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <div class="cd-filter__block">
            <h4>Khoảng giá (đ)</h4>
            <div class="input-group input-group-sm mb-2">
                <input type="number" class="form-control" name="min_price" placeholder="Từ" value="<?php echo $f_min_p ?: ''; ?>">
                <span class="input-group-text">—</span>
                <input type="number" class="form-control" name="max_price" placeholder="Đến" value="<?php echo $f_max_p ?: ''; ?>">
            </div>
            <div class="cd-filter__price-quick">
                <button type="button" data-min="0" data-max="500000">&lt; 500K</button>
                <button type="button" data-min="500000" data-max="2000000">500K–2M</button>
                <button type="button" data-min="2000000" data-max="10000000">2M–10M</button>
                <button type="button" data-min="10000000" data-max="0">&gt; 10M</button>
            </div>
        </div>
        <div class="cd-filter__block">
            <h4>Giảm tối thiểu</h4>
            <select name="min_discount" class="form-select form-select-sm">
                <?php foreach ([0=>'Tất cả',10=>'Từ 10%',20=>'Từ 20%',30=>'Từ 30%',50=>'Từ 50%',70=>'Từ 70%'] as $v=>$lbl): ?>
                    <option value="<?php echo $v; ?>" <?php selected($f_min_disc, $v); ?>><?php echo $lbl; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="cd-filter__block">
            <h4>Sắp xếp theo</h4>
            <select name="orderby" class="form-select form-select-sm">
                <?php foreach (['newest'=>'Mới nhất','price_asc'=>'Giá thấp → cao','price_desc'=>'Giá cao → thấp','discount_desc'=>'Giảm % cao'] as $v=>$lbl): ?>
                    <option value="<?php echo $v; ?>" <?php selected($f_orderby, $v); ?>><?php echo $lbl; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-fill" type="submit">Lọc deal</button>
            <a class="btn btn-light-primary" href="<?php echo esc_url(home_url('/deal/')); ?>">Reset</a>
        </div>
    </form>
<?php } ?>

<script>
document.querySelectorAll('.cd-filter__price-quick button').forEach(btn => {
    btn.addEventListener('click', () => {
        const form = btn.closest('form');
        form.querySelector('[name=min_price]').value = btn.dataset.min !== '0' ? btn.dataset.min : '';
        form.querySelector('[name=max_price]').value = btn.dataset.max !== '0' ? btn.dataset.max : '';
        form.submit();
    });
});
</script>

<?php get_footer(); ?>
