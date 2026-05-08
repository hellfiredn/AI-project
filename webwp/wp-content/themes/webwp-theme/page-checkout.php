<?php
get_header();
$course = webwp_sample_courses()[0];
?>

<section class="page-hero">
    <div class="container">
        <span class="page-kicker">Checkout</span>
        <h1 class="page-title">Complete your enrollment.</h1>
        <p class="page-lead">A simple checkout flow with cleaner information hierarchy and a strong order summary.</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <div class="checkout-layout">
            <div class="checkout-card">
                <h2>Billing details</h2>
                <form class="auth-form" action="#" method="post">
                    <label><span>Full name</span><input type="text" placeholder="Jane Cooper"></label>
                    <label><span>Email</span><input type="email" placeholder="jane@email.com"></label>
                    <label><span>Card number</span><input type="text" placeholder="1234 5678 9012 3456"></label>
                    <div class="checkout-row">
                        <label><span>Expiry</span><input type="text" placeholder="MM/YY"></label>
                        <label><span>CVC</span><input type="text" placeholder="123"></label>
                    </div>
                    <button type="submit" class="btn btn-primary auth-submit">Pay now</button>
                </form>
            </div>

            <aside class="checkout-card checkout-card--summary">
                <h2>Order summary</h2>
                <div class="order-item">
                    <img src="<?php echo esc_url( $course['image'] ); ?>" alt="<?php echo esc_attr( $course['title'] ); ?>">
                    <div>
                        <strong><?php echo esc_html( $course['title'] ); ?></strong>
                        <p><?php echo esc_html( $course['instructor'] ); ?></p>
                    </div>
                </div>
                <div class="summary-line"><span>Subtotal</span><strong><?php echo esc_html( $course['price'] ); ?></strong></div>
                <div class="summary-line"><span>Discount</span><strong>$0</strong></div>
                <div class="summary-line summary-line--total"><span>Total</span><strong><?php echo esc_html( $course['price'] ); ?></strong></div>
            </aside>
        </div>
    </div>
</section>

<?php get_footer();
