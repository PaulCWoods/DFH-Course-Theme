<?php
/**
 * Template Name: Student Login
 *
 * WordPress's login form (wp_login_form()) in the library's Field markup,
 * posting the same fields and keeping its filters for plugins.
 */

$redirect_to = isset($_REQUEST['redirect_to']) ? esc_url_raw(wp_unslash($_REQUEST['redirect_to'])) : home_url();
$register_url = isset($_REQUEST['redirect_to']) ? add_query_arg('redirect_to', $redirect_to, home_url('/register/')) : home_url('/register/');
$login_args = array('redirect' => $redirect_to, 'form_id' => 'loginform');

get_header();
get_template_part('content', 'course-header');
?>
<main class="main" id="main">
    <div class="wrapper py-cushion-lg">
        <div class="card max-w-md mx-auto">
            <?php if (is_user_logged_in()): ?>
                <header class="card__header"><h1 class="card__title title">You're logged in</h1></header>
                <div class="card__body">
                    <div class="button-group">
                        <a href="<?php echo esc_url(home_url()); ?>" class="button button--strong button--accent">
                            <span>Go to your courses</span>
                            <svg class="icon icon--dir" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#ArrowRight" /></svg>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <header class="card__header"><h1 class="card__title title">Log in</h1></header>
                <div class="card__body stack">
                    <form name="loginform" id="loginform" class="form" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
                        <?php echo apply_filters('login_form_top', '', $login_args); ?>
                        <div class="field">
                            <label for="user_login" class="field__label label">Email or username</label>
                            <input type="text" name="log" id="user_login" class="input" autocomplete="username" autocapitalize="off" required />
                        </div>
                        <div class="field">
                            <label for="user_pass" class="field__label label">Password</label>
                            <input type="password" name="pwd" id="user_pass" class="input" autocomplete="current-password" required />
                        </div>
                        <?php echo apply_filters('login_form_middle', '', $login_args); ?>
                        <label class="choice">
                            <input type="checkbox" name="rememberme" id="rememberme" class="checkbox-input" value="forever" />
                            <span>Remember me</span>
                        </label>
                        <input type="hidden" name="redirect_to" value="<?php echo esc_url($redirect_to); ?>" />
                        <div class="form__actions button-group">
                            <button type="submit" name="wp-submit" id="wp-submit" class="button button--strong button--accent">Log in</button>
                        </div>
                        <?php echo apply_filters('login_form_bottom', '', $login_args); ?>
                    </form>
                    <p class="small-text"><a class="link" href="<?php echo esc_url(wp_lostpassword_url()); ?>">Forgotten your password?</a></p>
                </div>
                <footer class="card__footer">
                    <p class="small-text">No account yet? <a class="link font-bold" href="<?php echo esc_url($register_url); ?>">Register</a></p>
                </footer>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php get_footer(); ?>
