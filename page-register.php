<?php
/**
 * Template Name: Student Register
 *
 * Creates the account, logs the student in and sends them on (to the page
 * that asked, or home, which welcomes them). A failed attempt shows the
 * form again with a summary of the problem, focused on load.
 */

$redirect_to = isset($_REQUEST['redirect_to']) ? esc_url_raw(wp_unslash($_REQUEST['redirect_to'])) : home_url();
$registration_error = '';

if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['dfh_register_nonce'])) {
    if (wp_verify_nonce($_POST['dfh_register_nonce'], 'dfh_register_action')) {
        $username = sanitize_user($_POST['user_login']);
        $email = sanitize_email($_POST['user_email']);
        $password = $_POST['user_pass'];
        $display_name = isset($_POST['dfh_student_name']) ? sanitize_text_field($_POST['dfh_student_name']) : '';

        if (empty($username) || empty($email) || empty($password)) {
            $registration_error = 'Enter a username, an email address and a password.';
        } elseif (username_exists($username)) {
            $registration_error = 'That username is taken. Try another.';
        } elseif (email_exists($email)) {
            $registration_error = 'There is already an account with that email address. Log in instead.';
        } else {
            $user_id = wp_create_user($username, $password, $email);

            if (is_wp_error($user_id)) {
                $registration_error = $user_id->get_error_message();
            } else {
                if (!empty($display_name)) {
                    update_user_meta($user_id, 'dfh_student_name', $display_name);
                }
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                wp_safe_redirect(add_query_arg('registered', '1', $redirect_to));
                exit;
            }
        }
    } else {
        $registration_error = 'The form expired. Try again.';
    }
}

$login_url = isset($_REQUEST['redirect_to']) ? add_query_arg('redirect_to', $redirect_to, home_url('/login/')) : home_url('/login/');
$posted = function ($key) {
    return isset($_POST[$key]) ? esc_attr(wp_unslash($_POST[$key])) : '';
};

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
                <header class="card__header"><h1 class="card__title title">Create an account</h1></header>
                <div class="card__body">
                    <form name="registerform" id="registerform" class="form" method="post">
                        <?php if ($registration_error): ?>
                            <div id="register-error" class="banner banner--danger" tabindex="-1" aria-labelledby="register-error-title">
                                <span class="icon-box" aria-hidden="true"><svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#Warning" /></svg></span>
                                <div class="banner__body">
                                    <h2 id="register-error-title" class="text font-bold">Your account wasn't created</h2>
                                    <p class="text"><?php echo esc_html($registration_error); ?></p>
                                </div>
                            </div>
                            <script>
                                document.title = 'Error: ' + document.title;
                                document.getElementById('register-error').focus();
                            </script>
                        <?php endif; ?>
                        <?php wp_nonce_field('dfh_register_action', 'dfh_register_nonce'); ?>
                        <?php if (isset($_REQUEST['redirect_to'])): ?>
                            <input type="hidden" name="redirect_to" value="<?php echo esc_url($redirect_to); ?>" />
                        <?php endif; ?>
                        <div class="field">
                            <label for="dfh_student_name" class="field__label label">Your name (optional)</label>
                            <input type="text" name="dfh_student_name" id="dfh_student_name" class="input" autocomplete="name" value="<?php echo $posted('dfh_student_name'); ?>" />
                        </div>
                        <div class="field">
                            <label for="user_login" class="field__label label">Username</label>
                            <input type="text" name="user_login" id="user_login" class="input" autocomplete="username" autocapitalize="off" value="<?php echo $posted('user_login'); ?>" required />
                        </div>
                        <div class="field">
                            <label for="user_email" class="field__label label">Email address</label>
                            <input type="email" name="user_email" id="user_email" class="input" autocomplete="email" value="<?php echo $posted('user_email'); ?>" required />
                        </div>
                        <div class="field">
                            <label for="user_pass" class="field__label label">Password</label>
                            <input type="password" name="user_pass" id="user_pass" class="input" autocomplete="new-password" required />
                        </div>
                        <?php do_action('register_form'); ?>
                        <div class="form__actions button-group">
                            <button type="submit" name="wp-submit" id="wp-submit" class="button button--strong button--accent">Create account</button>
                        </div>
                    </form>
                </div>
                <footer class="card__footer">
                    <p class="small-text">Already have an account? <a class="link font-bold" href="<?php echo esc_url($login_url); ?>">Log in</a></p>
                </footer>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php get_footer(); ?>
