<?php
/**
 * Template Name: Student Register
 */
get_header();
?>
<header class="site-header">
    <div class="container site-header-inner">
        <nav class="site-navigation">
            <ul class="site-breadcrumb container">
                <li>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="link site-title">
                        <svg class="icon" width="32" height="32" aria-hidden="true">
                            <use href="#Home" />
                        </svg>
                        Courses Home
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>
<main class="site-main login-page">
    <div class="container">

        <h1 class="heading">Create an Account</h1>

        <?php
        if (is_user_logged_in()) {
            echo '<div class="login-message"><p>You are already logged in!</p>
                <p><a href="' . esc_url(home_url()) . '" class="button strong">
                    Go to your course
                    <svg class="icon dir" width="32" height="32" aria-hidden="true"><use href="#ArrowRight" /></svg>
                </a>
                </p></div>';
        } else {
            // WordPress registration form handler check
            if (isset($_GET['registered']) && 'true' === $_GET['registered']) {
                echo '<div class="login-message">Registration successful! You can now log in.</div>';
            }

            // Output default registration form fields via a clean wrapper
            ?>
            <form name="registerform" id="registerform"
                action="<?php echo esc_url(site_url('wp-login.php?action=register', 'login_post')); ?>" method="post">
                <p>
                    <label for="user_login">Username</label>
                    <input type="text" name="user_login" id="user_login" value="" size="20" autocapitalization="off" required />
                </p>
                <p>
                    <label for="user_email">Email
                        Address</label>
                    <input type="email" name="user_email" id="user_email" value="" size="25" required />
                </p>
                <?php do_action('register_form'); ?>
                <p id="reg_passmail">Registration password will be emailed to you.</p>
                <p class="submit">
                    <input type="submit" name="wp-submit" id="wp-submit" class="button strong" value="Register" />
                </p>
            </form>

            <p class="login-extras">Already have an account? <a class="link" href="<?php echo esc_url(home_url('/login/')); ?>">Log
                    in</a></p>
            <?php
        }
        ?>
    </div>
</main>

<?php get_footer(); ?>