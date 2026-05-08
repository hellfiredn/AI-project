<?php
$heading_prefix = webwp_field( 'heading_prefix', 'What is' );
$heading_accent = webwp_field( 'heading_accent', 'TOTC?' );
$description    = webwp_field( 'description', 'TOTC is a platform that allows educators to create online classes whereby they can store the course materials online; manage assignments, quizzes and exams; monitor due dates; grade results and provide students with feedback all in one place.' );
$cards = get_field( 'cards' ) ?: [
    [ 'image' => null, 'label' => 'FOR INSTRUCTORS', 'button_text' => 'Start a class today', 'button_url' => '#', 'button_style' => 'btn-outline-white', 'fallback' => 'instructors.png' ],
    [ 'image' => null, 'label' => 'FOR STUDENTS',    'button_text' => 'Enter access code',   'button_url' => '#', 'button_style' => 'btn-primary',      'fallback' => 'students.png' ],
];
?>
<section class="section pt-0">
    <div class="container">
        <div class="text-center mx-auto mb-5 gsap-reveal" style="max-width:780px;">
            <h2 class="display-6 fw-bold"><?php echo esc_html( $heading_prefix ); ?> <span class="text-accent"><?php echo esc_html( $heading_accent ); ?></span></h2>
            <p class="mt-3"><?php echo esc_html( $description ); ?></p>
        </div>

        <div class="row g-4">
            <?php foreach ( $cards as $i => $c ) :
                $side = ( $i === 0 ) ? 'gsap-reveal-left' : 'gsap-reveal-right';
                $style = $c['button_style'] ?? 'btn-primary';
                $img   = webwp_image_url( $c['image'] ?? null, $c['fallback'] ?? '' );
            ?>
                <div class="col-md-6 <?php echo esc_attr( $side ); ?>">
                    <div class="totc-card">
                        <img src="<?php echo esc_url( $img ); ?>" alt="">
                        <div class="overlay">
                            <div class="label"><?php echo esc_html( $c['label'] ?? '' ); ?></div>
                            <a href="<?php echo esc_url( $c['button_url'] ?? '#' ); ?>" class="btn <?php echo esc_attr( $style ); ?>"><?php echo esc_html( $c['button_text'] ?? '' ); ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
