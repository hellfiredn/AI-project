<?php
/**
 * Header — Bootstrap 5 navbar.
 */
if (!defined('ABSPATH')) exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="cd-skip" href="#cd-main">Tới nội dung chính</a>

<div class="cd-topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <span><i class="bi bi-rocket-takeoff me-1"></i> Tạp Hóa Giảm Giá — săn deal thật, tiết kiệm mỗi ngày</span>
        <span class="d-none d-md-inline">Thứ <?php echo date_i18n('N · d/m/Y'); ?></span>
    </div>
</div>

<header class="cd-navbar sticky-top">
    <nav class="navbar navbar-expand-lg py-0">
        <div class="container">
            <a class="cd-logo navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
                <span class="cd-logo__mark">TGG</span>
                <span class="cd-logo__text">Tạp Hóa <span>Giảm Giá</span></span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#cdNavbar" aria-controls="cdNavbar" aria-expanded="false" aria-label="Toggle">
                <i class="bi bi-list fs-3"></i>
            </button>

            <div class="collapse navbar-collapse" id="cdNavbar">
                <form role="search" method="get" class="cd-search-form mx-lg-auto my-3 my-lg-0" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="search" name="s" placeholder="Tìm sản phẩm, mã giảm giá, cửa hàng…" value="<?php echo get_search_query(); ?>" />
                    <button type="submit" aria-label="Tìm">
                        <i class="bi bi-search"></i>
                    </button>
                </form>

                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'navbar-nav ms-auto',
                    'add_li_class'   => 'nav-item',
                    'fallback_cb'    => function () {
                        echo '<ul class="navbar-nav ms-auto">';
                        $items = [
                            'Trang chủ'   => home_url('/'),
                            'Deal hot'    => home_url('/deal/'),
                            'Mã giảm giá' => home_url('/coupon/'),
                            'So sánh giá' => home_url('/so-sanh-gia/'),
                            'Blog'        => home_url('/blog/'),
                        ];
                        foreach ($items as $label => $url) {
                            echo '<li class="nav-item"><a class="nav-link" href="' . esc_url($url) . '">' . esc_html($label) . '</a></li>';
                        }
                        echo '</ul>';
                    },
                ]);
                ?>
            </div>
        </div>
    </nav>
</header>

<main id="cd-main" class="cd-main">
