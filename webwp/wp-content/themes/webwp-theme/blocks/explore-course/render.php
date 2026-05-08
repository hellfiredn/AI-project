<?php
/**
 * Explore Course block — pill accordion.
 * Click a pill to expand (shows course detail). Only one open per row.
 */
$heading  = webwp_field( 'heading',  'Explore Course' );
$subtitle = webwp_field( 'subtitle', 'Ut sed eros finibus, placerat orci id, dapibus.' );

$categories = get_field( 'categories' ) ?: [
    [ 'title' => 'Lorem Ipsum',         'icon_class' => 'bi-book' ],
    [ 'title' => 'Quisque a Consequat', 'icon_class' => 'bi-shield' ],
    [ 'title' => 'Aenean Facilisis',    'icon_class' => 'bi-person' ],
];

/* Pills — each pill has a compact label + an expanded course detail. */
$pills = get_field( 'pills' );
if ( ! is_array( $pills ) || empty( $pills ) ) {
    $pills = [
        [ 'label' => 'Ut Sed Eros',         'detail_title' => 'Integer id Orc Sed Ante Tincidunt', 'detail_body' => 'Cras convallis lacus orci, tristique tincidunt magna fringilla at faucibus vel.', 'price' => '$ 450', 'url' => '#', 'image' => null ],
        [ 'label' => 'Curabitur Egestas',   'detail_title' => 'Sed Ac Risus Volutpat Nulla',        'detail_body' => 'Mauris interdum, neque nec cursus ornare, massa odio blandit massa.',        'price' => '$ 320', 'url' => '#', 'image' => null ],
        [ 'label' => 'Quisque Consequat',   'detail_title' => 'Fusce Sagittis Mauris Eget Lorem',   'detail_body' => 'Donec non commodo neque, nec vulputate orci. Sed id lectus id nibh.',        'price' => '$ 540', 'url' => '#', 'image' => null ],
        [ 'label' => 'Cras Convallis',      'detail_title' => 'Nam Vitae Diam Eget Augue Fringilla','detail_body' => 'Ut aliquam leo a purus luctus, nec commodo urna faucibus aliquam.',         'price' => '$ 210', 'url' => '#', 'image' => null ],
        [ 'label' => 'Vestibulum faucibus', 'detail_title' => 'Proin Tempor Justo Eu Nisi',         'detail_body' => 'In non ipsum et enim eleifend scelerisque vitae vel lacus.',                'price' => '$ 380', 'url' => '#', 'image' => null ],
        [ 'label' => 'Ut Sed Eros',         'detail_title' => 'Aliquam Erat Volutpat Nunc',         'detail_body' => 'Praesent euismod, mauris sed condimentum ornare, est justo venenatis dui.',   'price' => '$ 290', 'url' => '#', 'image' => null ],
        [ 'label' => 'Vestibulum faucibus', 'detail_title' => 'Curabitur Nec Ligula Ut Tellus',     'detail_body' => 'Nunc volutpat, dolor nec sagittis cursus, felis nibh viverra mauris.',       'price' => '$ 415', 'url' => '#', 'image' => null ],
    ];
}
?>
<section class="explore">
    <div class="container">
        <div class="mb-4 gsap-reveal">
            <h2 class="display-6 fw-bold mb-1"><?php echo esc_html( $heading ); ?></h2>
            <p class="text-dark"><?php echo esc_html( $subtitle ); ?></p>
        </div>

        <?php foreach ( $categories as $ci => $cat ) : ?>
        <div class="my-4 gsap-reveal">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold" style="color:var(--color-dark)">
                    <i class="bi <?php echo esc_attr( $cat['icon_class'] ?? 'bi-book' ); ?> me-2"></i>
                    <?php echo esc_html( $cat['title'] ?? '' ); ?>
                </span>
                <a href="#" class="fw-semibold text-decoration-none" style="color:var(--color-dark)">
                    SEE ALL <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="explore-row" data-explore-row>
                <?php foreach ( $pills as $pi => $p ) :
                    $is_open = ( $pi === $ci ); // rotate the initially-open pill per row
                    $img_url = webwp_image_url( $p['image'] ?? null, 'quiz-italy.png' );
                ?>
                    <div class="pill-card pill-card--<?php echo ( $pi % 7 ) + 1; ?> <?php echo $is_open ? 'is-open' : ''; ?>"
                         data-pill tabindex="0" role="button"
                         aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
                        <span class="pill-label"><?php echo esc_html( $p['label'] ?? '' ); ?></span>

                        <div class="pill-detail">
                            <img src="<?php echo esc_url( $img_url ); ?>" alt="">
                            <div class="pill-detail__body">
                                <h6 class="fw-bold mb-1"><?php echo esc_html( $p['detail_title'] ?? '' ); ?></h6>
                                <p class="small mb-2"><?php echo esc_html( $p['detail_body'] ?? '' ); ?></p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fw-bold"><?php echo esc_html( $p['price'] ?? '' ); ?></span>
                                    <a href="<?php echo esc_url( $p['url'] ?? '#' ); ?>" class="btn btn-outline-primary btn-sm px-3">EXPLORE</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
