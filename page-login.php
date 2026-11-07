<?php
/**
 * Template Name: Student Login
 */
get_header();
get_template_part('content', 'course-header');
?>
<main class="main login-page" id="main">
    <div class="wrapper">
        <h1 class="title">Log in to your account</h1>

        <?php
        if (is_user_logged_in()) {
            echo '<p class="login-info">You are already logged in!</p>';
            echo '<p><a href="' . esc_url(home_url()) . '" class="button button--strong button--accent"><span>Go to Your Course &rarr;</span></a></p>';
        } else {
            // Use `redirect_to` query parameter if provided, otherwise default to course archive
            $redirect_to = isset($_REQUEST['redirect_to']) ? esc_url_raw(wp_unslash($_REQUEST['redirect_to'])) : home_url();

            // WordPress's login form (wp_login_form()) in the library's Field markup,
            // posting the same fields and keeping its filters for plugins
            $login_args = array('redirect' => $redirect_to, 'form_id' => 'loginform');
            ?>
            <form name="loginform" id="loginform" class="form" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
                <?php echo apply_filters('login_form_top', '', $login_args); ?>
                <div class="field">
                    <label for="user_login" class="field__label label"><?php esc_html_e('Email or username', 'dfh'); ?></label>
                    <input type="text" name="log" id="user_login" class="input" autocomplete="username" autocapitalize="off" required />
                </div>
                <div class="field">
                    <label for="user_pass" class="field__label label"><?php esc_html_e('Password', 'dfh'); ?></label>
                    <input type="password" name="pwd" id="user_pass" class="input" autocomplete="current-password" required />
                </div>
                <?php echo apply_filters('login_form_middle', '', $login_args); ?>
                <label class="choice">
                    <input type="checkbox" name="rememberme" id="rememberme" class="checkbox-input" value="forever" />
                    <span><?php esc_html_e('Remember me', 'dfh'); ?></span>
                </label>
                <input type="hidden" name="redirect_to" value="<?php echo esc_url($redirect_to); ?>" />
                <div class="form__actions button-group">
                    <button type="submit" name="wp-submit" id="wp-submit" class="button button--strong button--accent"><span><?php esc_html_e('Log in', 'dfh'); ?></span></button>
                </div>
                <?php echo apply_filters('login_form_bottom', '', $login_args); ?>
            </form>
            <?php
            echo '<p class="login-extras" class="login-forgotpassword"><a class="link" href="' . esc_url(wp_lostpassword_url()) . '">Forgot your password?</a></p>';
            $register_url = isset($_REQUEST['redirect_to']) ? add_query_arg('redirect_to', esc_url_raw(wp_unslash($_REQUEST['redirect_to'])), home_url('/register/')) : home_url('/register/');
            echo '<p class="login-extras">Don\'t have an account? <a class="link" href="' . esc_url($register_url) . '">Register here.</a></p>';
        }
        ?>
    </div>
</main>

<?php get_footer(); ?>