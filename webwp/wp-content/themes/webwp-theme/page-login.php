<?php
get_header();
?>

<section class="auth-page">
    <div class="container">
        <div class="auth-shell">
            <div class="auth-visual auth-visual--login">
                <span class="page-kicker">Welcome Back</span>
                <h1 class="page-title">Teach, learn, and manage class in one place.</h1>
                <p class="page-lead">Sign in to access your classroom dashboard, recent meetings, assignments, and student conversations.</p>
                <div class="auth-metrics">
                    <div class="auth-metric">
                        <strong>120K+</strong>
                        <span>active learners</span>
                    </div>
                    <div class="auth-metric">
                        <strong>4.9/5</strong>
                        <span>community rating</span>
                    </div>
                </div>
                <div class="auth-figure">
                    <img src="<?php echo esc_url( webwp_img( 'hero-girl.png' ) ); ?>" alt="Student learning online">
                </div>
            </div>

            <div class="auth-panel">
                <div class="auth-card">
                    <span class="page-kicker">Login</span>
                    <h2>Sign in to your account</h2>
                    <p>Use your school email or continue with your community profile.</p>

                    <form class="auth-form" action="#" method="post">
                        <label>
                            <span>Email</span>
                            <input type="email" name="email" placeholder="name@email.com" required>
                        </label>
                        <label>
                            <span>Password</span>
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </label>
                        <div class="auth-row">
                            <label class="auth-check">
                                <input type="checkbox" name="remember" checked>
                                <span>Remember me</span>
                            </label>
                            <a href="#" class="auth-link">Forgot password?</a>
                        </div>
                        <button type="submit" class="btn btn-primary auth-submit">Login</button>
                    </form>

                    <div class="auth-divider"><span>or continue with</span></div>

                    <div class="auth-socials">
                        <a href="#" class="auth-social"><i class="bi bi-google"></i> Google</a>
                        <a href="#" class="auth-social"><i class="bi bi-apple"></i> Apple</a>
                    </div>

                    <p class="auth-switch">Don&rsquo;t have an account? <a href="<?php echo esc_url( webwp_page_url( 'register' ) ); ?>">Create one</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer();
