<?php if ( ! defined( 'ABSPATH' ) ) exit;
$is_hero = is_front_page();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if ( $is_hero ) : ?>
<div class="hero-wrap">
<?php endif; ?>

<header class="site-header <?php echo $is_hero ? 'on-hero' : ''; ?>">
    <nav class="navbar navbar-expand-lg py-3">
        <div class="container">
            <a class="navbar-brand logo-wordmark fw-bold" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
                    <span class="logo-diamond" aria-hidden="true"><span>TOTC</span></span>
                <?php endif; ?>
            </a>

            <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse"
                    data-bs-target="#primaryNav" aria-controls="primaryNav"
                    aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-2"></i>
            </button>

            <div class="collapse navbar-collapse" id="primaryNav">
                <?php
                if ( has_nav_menu( 'primary' ) ) {
                    wp_nav_menu( [
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'navbar-nav mx-auto gap-lg-4',
                        'fallback_cb'    => false,
                        'walker'         => new WebWP_Bootstrap_Nav_Walker(),
                    ] );
                } else {
                    echo '<ul class="navbar-nav mx-auto gap-lg-4">'
                        . '<li class="nav-item"><a class="nav-link active" href="' . esc_url( home_url( '/' ) ) . '">Home</a></li>'
                        . '<li class="nav-item"><a class="nav-link" href="' . esc_url( webwp_page_url( 'course' ) ) . '">Courses</a></li>'
                        . '<li class="nav-item"><a class="nav-link" href="#">Careers</a></li>'
                        . '<li class="nav-item"><a class="nav-link" href="' . esc_url( webwp_page_url( 'blog' ) ) . '">Blog</a></li>'
                        . '<li class="nav-item"><a class="nav-link" href="#">About Us</a></li>'
                        . '</ul>';
                }
                ?>

                <div class="header-cta d-flex align-items-center gap-2">
                    <?php if ( $is_hero ) : ?>
                        <a href="<?php echo esc_url( webwp_page_url( 'login' ) ); ?>" class="btn btn-white rounded-pill px-4">Login</a>
                        <a href="<?php echo esc_url( webwp_page_url( 'register' ) ); ?>" class="btn btn-outline-white rounded-pill px-4">Sign Up</a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( webwp_page_url( 'login' ) ); ?>" class="btn btn-link text-decoration-none fw-semibold">Login</a>
                        <a href="<?php echo esc_url( webwp_page_url( 'register' ) ); ?>" class="btn btn-primary rounded-pill px-4">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<?php if ( ! $is_hero ) : ?>
<main id="site-main">
<?php endif; ?>
