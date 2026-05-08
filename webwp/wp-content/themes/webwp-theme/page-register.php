<?php
get_header();
?>

<section class="auth-page">
    <div class="container">
        <div class="auth-shell">
            <div class="auth-visual auth-visual--register">
                <span class="page-kicker">Join TOTC</span>
                <h1 class="page-title">Build your learning community with a cleaner workflow.</h1>
                <p class="page-lead">Create your account to launch courses, manage meetings, track attendance, and publish learning resources.</p>
                <ul class="auth-benefits">
                    <li>Launch unlimited classroom spaces</li>
                    <li>Track assignments, attendance, and gradebook</li>
                    <li>Run private sessions and live course meetings</li>
                </ul>
                <div class="auth-figure">
                    <img src="<?php echo esc_url( webwp_img( 'teacher-lesson.png' ) ); ?>" alt="Teacher presenting in class">
                </div>
            </div>

            <div class="auth-panel">
                <div class="auth-card">
                    <span class="page-kicker">Register</span>
                    <h2>Create your account</h2>
                    <p>Start with your school, team, or personal learning workspace.</p>

                    <form class="auth-form" action="#" method="post">
                        <label>
                            <span>Full name</span>
                            <input type="text" name="name" placeholder="Your full name" required>
                        </label>
                        <label>
                            <span>Email</span>
                            <input type="email" name="email" placeholder="name@email.com" required>
                        </label>
                        <label>
                            <span>Password</span>
                            <input type="password" name="password" placeholder="Create a password" required>
                        </label>
                        <label>
                            <span>Role</span>
                            <select name="role">
                                <option>Instructor</option>
                                <option>Student</option>
                                <option>Team admin</option>
                            </select>
                        </label>
                        <label class="auth-check">
                            <input type="checkbox" name="terms" checked>
                            <span>I agree to the Terms &amp; Conditions and Privacy Policy</span>
                        </label>
                        <button type="submit" class="btn btn-primary auth-submit">Create account</button>
                    </form>

                    <p class="auth-switch">Already have an account? <a href="<?php echo esc_url( webwp_page_url( 'login' ) ); ?>">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer();
