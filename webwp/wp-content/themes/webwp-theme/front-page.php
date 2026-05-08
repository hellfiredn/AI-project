<?php
/**
 * TOTC front page — aligned to Figma (file Xl5oCGPLs5u7rI0poQ8Fyg, frame Landing 10:358).
 */
$img = WEBWP_URI . '/assets/images';
get_header(); ?>

<!-- ============ HERO (inside .hero-wrap teal opened in header) ============ -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <h1>
                    <span class="brush">Studying</span> Online is<br>
                    now much easier
                </h1>
                <p class="lead hero-lead mt-3">
                    TOTC is an interesting platform that will teach you in more an interactive way
                </p>
                <div class="d-flex flex-wrap gap-3 mt-4 align-items-center hero-cta">
                    <a href="#" class="btn btn-white px-4 py-2 fs-6">Join for free</a>
                    <span class="d-inline-flex align-items-center gap-2">
                        <button type="button" class="cta-play" aria-label="Play">
                            <i class="bi bi-play-fill"></i>
                        </button>
                        <span>Watch how it works</span>
                    </span>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="hero-media hero-visual">
                    <div class="girl">
                        <img src="<?php echo esc_url( $img . '/hero-girl.png' ); ?>" alt="student">
                    </div>

                    <div class="float-card float-card--250k">
                        <span class="ico orange"><i class="bi bi-journal-bookmark-fill"></i></span>
                        <div>
                            <div class="t-title">250k</div>
                            <div class="t-sub">Assisted Student</div>
                        </div>
                    </div>

                    <div class="float-card float-card--congrat">
                        <span class="ico teal"><i class="bi bi-envelope-fill"></i></span>
                        <div>
                            <div class="t-title">Congratulations</div>
                            <div class="t-sub">Your admission completed</div>
                        </div>
                    </div>

                    <div class="float-card float-card--class">
                        <span class="ico purple"><i class="bi bi-calendar-event-fill"></i></span>
                        <div>
                            <div class="t-title">User Experience Class</div>
                            <div class="t-sub">Today at 12.00 PM</div>
                            <a href="#" class="btn">Join Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<svg class="hero-curve" viewBox="0 0 1440 140" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path fill="#49BBBD" d="M0,0 H1440 V140 C1120,-8 320,26 0,140 Z"/>
</svg>
</div><!-- /.hero-wrap -->

<main id="site-main">

<!-- ============ OUR SUCCESS ============ -->
<section class="section">
    <div class="container">
        <div class="text-center mx-auto mb-5 gsap-reveal section-intro" style="max-width:780px;">
            <h2 class="display-5 fw-bold">Our Success</h2>
            <p class="mt-3">
                Ornare id fames interdum porttitor nulla turpis etiam. Diam vitae sollicitudin at nec
                nam et pharetra gravida. Adipiscing a quis ultrices eu ornare tristique vel nisl orci.
            </p>
        </div>

        <div class="row text-center g-4 gsap-reveal">
            <div class="col-6 col-md"><div class="success-num" data-count="15" data-suffix="K+">15K+</div><div class="success-label">Students</div></div>
            <div class="col-6 col-md"><div class="success-num" data-count="75" data-suffix=" %">75 %</div><div class="success-label">Total success</div></div>
            <div class="col-6 col-md"><div class="success-num" data-count="35">35</div><div class="success-label">Main questions</div></div>
            <div class="col-6 col-md"><div class="success-num" data-count="26">26</div><div class="success-label">Chief experts</div></div>
            <div class="col-6 col-md"><div class="success-num" data-count="16">16</div><div class="success-label">Years of experience</div></div>
        </div>
    </div>
</section>

<!-- ============ ALL-IN-ONE ============ -->
<section class="section pt-0">
    <div class="container">
        <div class="text-center mx-auto mb-5 gsap-reveal" style="max-width:780px;">
            <h2 class="display-6 fw-bold">All-In-One <span class="text-primary-brand">Cloud Software.</span></h2>
            <p class="mt-3">
                TOTC is one powerful online software suite that combines all the tools needed to run a
                successful school or office.
            </p>
        </div>

        <div class="row g-4 allinone-grid">
            <div class="col-md-6 col-lg-4 gsap-reveal">
                <div class="allinone-card allinone-card--teal">
                    <span class="ico"><i class="bi bi-receipt"></i></span>
                    <h3 class="fw-bold">Online Billing, Invoicing, &amp; Contracts</h3>
                    <p class="mb-0">Simple and secure control of your organization&rsquo;s financial and legal transactions. Send customized invoices and contracts.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 gsap-reveal">
                <div class="allinone-card allinone-card--mint">
                    <span class="ico"><i class="bi bi-calendar-check"></i></span>
                    <h3 class="fw-bold">Easy Scheduling &amp; Attendance Tracking</h3>
                    <p class="mb-0">Schedule and reserve classrooms at one campus or multiple campuses. Keep detailed records of student attendance.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 gsap-reveal">
                <div class="allinone-card allinone-card--orange">
                    <span class="ico"><i class="bi bi-people-fill"></i></span>
                    <h3 class="fw-bold">Customer Tracking</h3>
                    <p class="mb-0">Automate and track emails to individuals or groups. TOTC&rsquo;s built-in system helps organize your organization.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ WHAT IS TOTC ============ -->
<section class="section pt-0">
    <div class="container">
        <div class="text-center mx-auto mb-5 gsap-reveal" style="max-width:780px;">
            <h2 class="display-6 fw-bold">What is <span class="text-accent">TOTC?</span></h2>
            <p class="mt-3">
                TOTC is a platform that allows educators to create online classes whereby they can store the
                course materials online; manage assignments, quizzes and exams; monitor due dates; grade
                results and provide students with feedback all in one place.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 gsap-reveal-left">
                <div class="totc-card">
                    <img src="<?php echo esc_url( $img . '/instructors.png' ); ?>" alt="For instructors">
                    <div class="overlay">
                        <div class="label">FOR INSTRUCTORS</div>
                        <a href="#" class="btn btn-outline-white">Start a class today</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 gsap-reveal-right">
                <div class="totc-card">
                    <img src="<?php echo esc_url( $img . '/students.png' ); ?>" alt="For students">
                    <div class="overlay">
                        <div class="label">FOR STUDENTS</div>
                        <a href="#" class="btn btn-primary px-4">Enter access code</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ YOU CAN DO WITH TOTC ============ -->
<section class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 gsap-reveal-left">
                <h2 class="display-6 fw-bold">
                    Everything you can do in a physical classroom,
                    <span class="text-primary-brand">you can do with TOTC</span>
                </h2>
                <p class="mt-3">
                    TOTC&rsquo;s school management software helps traditional and online schools manage
                    scheduling, attendance, payments and virtual classrooms all in one secure cloud-based system.
                </p>
                <a href="#" class="link-underline">Learn more</a>
            </div>
            <div class="col-lg-6 gsap-reveal-right">
                <div class="video-panel">
                    <img src="<?php echo esc_url( $img . '/teacher-lesson.png' ); ?>" alt="classroom">
                    <button type="button" class="play-btn" aria-label="Play video">
                        <i class="bi bi-play-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ OUR FEATURES ============ -->
<section class="features-section">
    <div class="container">
        <div class="text-center mx-auto mb-5 gsap-reveal" style="max-width:720px;">
            <h2 class="display-6 fw-bold">Our <span class="text-accent">Features</span></h2>
            <p>This very extraordinary feature, can make learning activities more efficient.</p>
        </div>

        <?php
        $features = [
            [
                'title' => 'A <span class="text-accent">user interface</span> designed<br>for the classroom',
                'body' => 'Teachers can easily see all students and class data at one time. TA&rsquo;s and presenters can be moved to the front of the class. Teachers don&rsquo;t get lost in the grid view and have a dedicated Podium space.',
                'img' => 'feat-ui.png',
                'flip' => false,
            ],
            [
                'title' => '<span class="text-accent">Tools</span> For Teachers<br>And Learners',
                'body' => 'Class has a dynamic set of teaching tools built to be deployed and used during class. Teachers can hand out assignments in real-time for students to complete and submit.',
                'img' => 'feat-tools.png',
                'flip' => true,
            ],
            [
                'title' => 'Assessments,<br><span class="text-accent">Quizzes, Tests</span>',
                'body' => 'Easily launch live assignments, quizzes, and tests. Student results are automatically entered in the gradebook.',
                'img' => 'feat-quiz.png',
                'flip' => false,
            ],
            [
                'title' => '<span class="text-accent">Class Management</span><br>Tools for Educators',
                'body' => 'Class provides tools to help run and manage the class such as Class Roster, Attendance, and even a Gradebook with a one-click export.',
                'img' => 'feat-gradebook.png',
                'flip' => true,
            ],
            [
                'title' => '<span class="text-accent">One-on-One</span><br>Discussions',
                'body' => 'Teachers and teacher assistants can talk with students privately without leaving the Zoom environment.',
                'img' => 'feat-121.png',
                'flip' => false,
            ],
        ];
        foreach ( $features as $f ) :
            $left_order  = $f['flip'] ? 'order-lg-2' : '';
            $right_order = $f['flip'] ? 'order-lg-1' : '';
        ?>
        <div class="row feature-row align-items-center g-4">
            <div class="col-lg-6 <?php echo esc_attr( $left_order ); ?> gsap-reveal-right">
                <h3 class="display-6 fw-bold"><?php echo $f['title']; ?></h3>
                <p class="mt-3"><?php echo $f['body']; ?></p>
            </div>
            <div class="col-lg-6 <?php echo esc_attr( $right_order ); ?> gsap-reveal-left">
                <?php $ill_cls = ( $f['img'] === 'feat-ui.png' ) ? 'feature-illustration feature-illustration--dark' : 'feature-illustration'; ?>
                <div class="<?php echo esc_attr( $ill_cls ); ?>">
                    <img src="<?php echo esc_url( $img . '/' . $f['img'] ); ?>" alt="">
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="text-center mt-5 gsap-reveal">
            <a href="#" class="btn btn-outline-primary px-4">See more features</a>
        </div>
    </div>
</section>

<!-- ============ EXPLORE COURSE ============ -->
<section class="explore">
    <div class="container">
        <div class="explore-shell">
        <div class="mb-4 gsap-reveal">
            <h2 class="display-6 fw-bold mb-1">Explore Course</h2>
            <p class="text-dark">Ut sed eros finibus, placerat orci id, dapibus.</p>
        </div>

        <?php
        $rows = [
            [ 'icon' => 'bi-book',    'title' => 'Lorem Ipsum' ],
            [ 'icon' => 'bi-shield',  'title' => 'Quisque a Consequat' ],
            [ 'icon' => 'bi-person',  'title' => 'Aenean Facilisis' ],
        ];
        $pills = [
            [ 'label' => 'Ut Sed Eros',         'detail_title' => 'Integer id Orc Sed Ante Tincidunt', 'detail_body' => 'Cras convallis lacus orci, tristique tincidunt magna fringilla at faucibus vel.', 'price' => '$ 450' ],
            [ 'label' => 'Curabitur Egestas',   'detail_title' => 'Sed Ac Risus Volutpat Nulla',        'detail_body' => 'Mauris interdum, neque nec cursus ornare, massa odio blandit massa.',        'price' => '$ 320' ],
            [ 'label' => 'Quisque Consequat',   'detail_title' => 'Fusce Sagittis Mauris Eget Lorem',   'detail_body' => 'Donec non commodo neque, nec vulputate orci. Sed id lectus id nibh.',        'price' => '$ 540' ],
            [ 'label' => 'Cras Convallis',      'detail_title' => 'Nam Vitae Diam Eget Augue Fringilla','detail_body' => 'Ut aliquam leo a purus luctus, nec commodo urna faucibus aliquam.',         'price' => '$ 210' ],
            [ 'label' => 'Vestibulum faucibus', 'detail_title' => 'Proin Tempor Justo Eu Nisi',         'detail_body' => 'In non ipsum et enim eleifend scelerisque vitae vel lacus.',                'price' => '$ 380' ],
            [ 'label' => 'Ut Sed Eros',         'detail_title' => 'Aliquam Erat Volutpat Nunc',         'detail_body' => 'Praesent euismod, mauris sed condimentum ornare, est justo venenatis dui.',   'price' => '$ 290' ],
            [ 'label' => 'Vestibulum faucibus', 'detail_title' => 'Curabitur Nec Ligula Ut Tellus',     'detail_body' => 'Nunc volutpat, dolor nec sagittis cursus, felis nibh viverra mauris.',       'price' => '$ 415' ],
        ];
        foreach ( $rows as $ci => $r ) :
        ?>
        <div class="my-4 gsap-reveal">
            <div class="d-flex justify-content-between align-items-center mb-2 explore-meta">
                <span class="fw-bold" style="color:var(--color-dark)"><i class="bi <?php echo esc_attr( $r['icon'] ); ?> me-2"></i><?php echo esc_html( $r['title'] ); ?></span>
                <a href="#" class="fw-semibold text-decoration-none explore-seeall">SEE ALL <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
            <div class="explore-row" data-explore-row>
                <?php foreach ( $pills as $pi => $p ) :
                    $is_open = ( $pi === $ci );
                ?>
                    <div class="pill-card pill-card--<?php echo ( $pi % 7 ) + 1; ?> <?php echo $is_open ? 'is-open' : ''; ?>"
                         data-pill tabindex="0" role="button"
                         aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
                        <span class="pill-label"><?php echo esc_html( $p['label'] ); ?></span>
                        <div class="pill-detail">
                            <img src="<?php echo esc_url( $img . '/quiz-italy.png' ); ?>" alt="">
                            <div class="pill-detail__body">
                                <h6 class="fw-bold mb-1"><?php echo esc_html( $p['detail_title'] ); ?></h6>
                                <p class="small mb-2"><?php echo esc_html( $p['detail_body'] ); ?></p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fw-bold"><?php echo esc_html( $p['price'] ); ?></span>
                                    <a href="#" class="btn btn-outline-primary btn-sm px-3">EXPLORE</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ TESTIMONIALS ============ -->
<section class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5 gsap-reveal-left">
                <span class="eyebrow">TESTIMONIAL</span>
                <h2 class="display-5 fw-bold mt-3">What They Say?</h2>
                <p class="fs-5">TOTC has got more than 100k positive ratings from our users around the world.</p>
                <p>Some of the students and teachers were greatly helped by the Skilline.</p>
                <p>Are you too? Please give your assessment.</p>
                <a href="#" class="btn btn-outline-primary px-4 mt-2 testimonial-cta">
                    Write your assessment <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="col-lg-7 gsap-reveal-right">
                <div class="testimonial-img">
                    <div class="frame">
                        <img src="<?php echo esc_url( $img . '/testimonial.png' ); ?>" alt="Gloria Rose">
                    </div>
                    <div class="quote-card">
                        <p class="mb-3" style="color:var(--color-ink)">
                            &ldquo;Thank you so much for your help. It&rsquo;s exactly what I&rsquo;ve been looking for.
                            You won&rsquo;t regret it. It really saves me time and effort. TOTC is exactly what our business has been lacking.&rdquo;
                        </p>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <div class="fw-bold" style="color:var(--color-dark)">Gloria Rose</div>
                            </div>
                            <div class="text-end">
                                <div class="stars small">
                                    <?php for ( $i = 0; $i < 5; $i++ ) echo '<i class="bi bi-star-fill"></i>'; ?>
                                </div>
                                <div class="small" style="color:var(--color-muted)">12 reviews at Yelp</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ LATEST NEWS ============ -->
<section class="section pt-5">
    <div class="container">
        <div class="mb-5 gsap-reveal text-center" style="max-width:820px;margin-inline:auto;">
            <h2 class="display-6 fw-bold">Latest News and Resources</h2>
            <p>See the developments that have occurred to TOTC in the world.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-6 gsap-reveal">
                <article class="news-featured">
                    <img src="<?php echo esc_url( $img . '/tools-hero.png' ); ?>" alt="">
                    <span class="tag-news">NEWS</span>
                    <div class="bottom">
                        <h4 class="fw-bold">Class adds $30 million to its balance sheet for a Zoom-friendly edtech solution</h4>
                        <p class="small mb-0">Class, launched less than a year ago by Blackboard co-founder Michael Chasen, integrates exclusively with Zoom...</p>
                    </div>
                </article>
            </div>

            <div class="col-lg-6">
                <?php
                $news = [
                    [ 'tag' => 'PRESS RELEASE', 'cls' => 'press', 'title' => 'Class Technologies Inc. Closes $30 Million Series A Financing to Meet High Demand',
                      'body' => 'Class Technologies Inc., the company that created Class,...', 'img' => 'avatar-3.png' ],
                    [ 'tag' => 'NEWS', 'cls' => 'news', 'title' => 'Zoom\'s earliest investors are betting millions on a better Zoom for schools',
                      'body' => 'Zoom was never created to be a consumer product. Nonetheless, the...', 'img' => 'avatar-4.png' ],
                    [ 'tag' => 'NEWS', 'cls' => 'news', 'title' => 'Former Blackboard CEO Raises $16M to Bring LMS Features to Zoom Classrooms',
                      'body' => 'This year, investors have reaped big financial returns from betting on Zoom...', 'img' => 'avatar-1.png' ],
                ];
                foreach ( $news as $n ) : ?>
                    <article class="news-item gsap-reveal">
                        <div class="thumb">
                            <img src="<?php echo esc_url( $img . '/' . $n['img'] ); ?>" alt="">
                            <span class="tag <?php echo esc_attr( $n['cls'] ); ?>"><?php echo esc_html( $n['tag'] ); ?></span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold" style="font-size:1.05rem;"><?php echo esc_html( $n['title'] ); ?></h5>
                            <p class="small mb-0"><?php echo esc_html( $n['body'] ); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer();
