<?php
get_header();
$plans = webwp_membership_plans();
?>

<section class="page-hero">
    <div class="container">
        <span class="page-kicker">Membership</span>
        <h1 class="page-title">Choose the plan that fits your classroom or team.</h1>
        <p class="page-lead">A pricing page in the same soft, bright style as the rest of the public experience.</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <div class="plan-grid">
            <?php foreach ( $plans as $plan ) : ?>
                <article class="plan-card <?php echo $plan['featured'] ? 'plan-card--featured' : ''; ?>">
                    <span class="badge-chip badge-chip--soft"><?php echo $plan['featured'] ? 'Most popular' : 'Plan'; ?></span>
                    <h2><?php echo esc_html( $plan['name'] ); ?></h2>
                    <div class="plan-price"><?php echo esc_html( $plan['price'] ); ?><small><?php echo esc_html( $plan['period'] ); ?></small></div>
                    <ul class="lesson-list">
                        <?php foreach ( $plan['features'] as $feature ) : ?>
                            <li><i class="bi bi-check2-circle"></i><span><?php echo esc_html( $feature ); ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?php echo esc_url( webwp_page_url( 'checkout' ) ); ?>" class="btn <?php echo $plan['featured'] ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">Choose plan</a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php get_footer();
