<?php
/**
 * Footer — Bootstrap grid.
 */
if (!defined('ABSPATH')) exit; ?>
</main>

<footer class="cd-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <a class="cd-logo" href="<?php echo esc_url(home_url('/')); ?>">
                    <span class="cd-logo__mark">TGG</span>
                    <span class="cd-logo__text">Tạp Hóa <span>Giảm Giá</span></span>
                </a>
                <p class="cd-footer__about">Tạp Hóa Giảm Giá tổng hợp deal hot, mã giảm giá và mẹo mua sắm thông minh, giúp bạn tiết kiệm khi mua online tại Shopee, Lazada, Tiki, TikTok Shop và nhiều cửa hàng khác.</p>
            </div>
            <div class="col-lg-2 col-md-6">
                <h4>Khám phá</h4>
                <ul>
                    <li><a href="<?php echo esc_url(home_url('/deal/')); ?>">Tất cả deal</a></li>
                    <li><a href="<?php echo esc_url(home_url('/coupon/')); ?>">Mã giảm giá</a></li>
                    <li><a href="<?php echo esc_url(home_url('/so-sanh-gia/')); ?>">So sánh giá</a></li>
                    <li><a href="<?php echo esc_url(home_url('/blog/')); ?>">Blog mua sắm</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h4>Cửa hàng nổi bật</h4>
                <ul>
                    <?php
                    $stores = get_terms(['taxonomy' => 'store', 'number' => 6, 'hide_empty' => false]);
                    if (!is_wp_error($stores)) foreach ($stores as $s) {
                        echo '<li><a href="' . esc_url(get_term_link($s)) . '">' . esc_html($s->name) . '</a></li>';
                    }
                    ?>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h4>Nhận deal mỗi tuần</h4>
                <p class="cd-footer__about">Để lại email — chúng tôi gửi top deal nóng nhất mỗi sáng thứ 2.</p>
                <form class="cd-newsletter mt-3">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="email@cua.ban" required>
                        <button class="btn btn-primary" type="submit">Đăng ký</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="cd-footer__bar d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span>© <?php echo date('Y'); ?> Tạp Hóa Giảm Giá. Nội dung có thể chứa liên kết tiếp thị liên kết.</span>
            <span>
                <a href="<?php echo esc_url(home_url('/chinh-sach/')); ?>">Chính sách affiliate</a> ·
                <a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ</a>
            </span>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php wp_footer(); ?>
</body>
</html>
