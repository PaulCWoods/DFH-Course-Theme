<?php
/**
 * Template Name: Student Register
 */

$registration_error = '';
$registration_success = false;

// Handle form submission securely
if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['dfh_register_nonce'])) {
    if (wp_verify_nonce($_POST['dfh_register_nonce'], 'dfh_register_action')) {
        $username = sanitize_user($_POST['user_login']);
        $email = sanitize_email($_POST['user_email']);
        $password = $_POST['user_pass'];
        $display_name = isset($_POST['dfh_student_name']) ? sanitize_text_field($_POST['dfh_student_name']) : '';
        $redirect_to = isset($_POST['redirect_to']) ? esc_url_raw(wp_unslash($_POST['redirect_to'])) : home_url();

        // Basic validation
        if (empty($username) || empty($email) || empty($password)) {
            $registration_error = 'Please fill out all required fields.';
        } elseif (username_exists($username)) {
            $registration_error = 'That username is already taken.';
        } elseif (email_exists($email)) {
            $registration_error = 'An account is already registered with that email address.';
        } else {
            // Create the new user
            $user_id = wp_create_user($username, $password, $email);

            if (is_wp_error($user_id)) {
                $registration_error = $user_id->get_error_message();
            } else {
                // Save the display name if provided
                if (!empty($display_name)) {
                    update_user_meta($user_id, 'dfh_student_name', $display_name);
                }

                // Success: Automatically log the user in and redirect
                $registration_success = true;
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                $redirect_to = add_query_arg('registered', '1', $redirect_to);
                // Redirect will happen after page output
            }
        }
    } else {
        $registration_error = 'Security check failed. Please try again.';
    }
}

get_header();
get_template_part('content', 'course-header');
?>
<main class="main login-page" id="main">
    <div class="wrapper">


        <?php if (is_user_logged_in() && !$registration_success): ?>
            <h1 class="title">You are already logged in!</h1>
            <div class="flex flex-direction-column gap-breathe">
                <p>
                    <a href="<?php echo esc_url(home_url()); ?>" class="button button--strong button--accent">
                        <span>Go to Your Course</span>
                        <svg class="icon icon--dir" width="32" height="32" aria-hidden="true">
                            <use href="#ArrowRight" />
                        </svg>
                    </a>
                </p>
            </div>

        <?php elseif ($registration_success): ?>
            <h1 class="title">Welcome aboard!</h1>
            <div class="flex flex-direction-column gap-breathe">
                <p>Your account has been successfully created and you are now logged in.</p>
                <p style="margin-top: 1.5rem;">
                    <a href="<?php echo esc_url($redirect_to); ?>" class="button button--strong button--accent">
                        <span>Start Learning</span>
                        <svg class="icon icon--dir" width="32" height="32" aria-hidden="true">
                            <use href="#ArrowRight" />
                        </svg>
                    </a>
                </p>
            </div>
            <script>
                // Redirect after brief delay to show success message
                setTimeout(function () {
                    window.location.href = '<?php echo esc_url($redirect_to); ?>';
                }, 2000);
            </script>

        <?php else: ?>
            <h1 class="title">Create an Account</h1>

            <?php if (!empty($registration_error)): ?>
                <div class="banner banner--danger login-message">
                    <span class="icon-box" aria-hidden="true"><svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#Warning" /></svg></span>
                    <div class="banner__body">
                        <p class="text"><?php echo esc_html($registration_error); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form name="registerform" id="registerform" class="form" method="post">
                <?php wp_nonce_field('dfh_register_action', 'dfh_register_nonce'); ?>

                <!-- Preserve redirect_to parameter from course page -->
                <?php if (isset($_GET['redirect_to'])): ?>
                    <input type="hidden" name="redirect_to"
                        value="<?php echo esc_attr(esc_url_raw(wp_unslash($_GET['redirect_to']))); ?>" />
                <?php endif; ?>

                <div class="field">
                    <label for="dfh_student_name" class="field__label label">Your name <span class="text-muted">(optional)</span></label>
                    <input type="text" name="dfh_student_name" id="dfh_student_name" class="input" autocomplete="name"
                        value="<?php echo isset($_POST['dfh_student_name']) ? esc_attr($_POST['dfh_student_name']) : ''; ?>" />
                </div>
                <div class="field">
                    <label for="user_login" class="field__label label">Username</label>
                    <input type="text" name="user_login" id="user_login" class="input" autocomplete="username"
                        value="<?php echo isset($_POST['user_login']) ? esc_attr($_POST['user_login']) : ''; ?>"
                        autocapitalize="off" required />
                </div>
                <div class="field">
                    <label for="user_email" class="field__label label">Email address</label>
                    <input type="email" name="user_email" id="user_email" class="input" autocomplete="email"
                        value="<?php echo isset($_POST['user_email']) ? esc_attr($_POST['user_email']) : ''; ?>"
                        required />
                </div>
                <div class="field">
                    <label for="user_pass" class="field__label label">Password</label>
                    <input type="password" name="user_pass" id="user_pass" class="input" autocomplete="new-password" required />
                </div>

                <?php do_action('register_form'); ?>

                <div class="form__actions button-group">
                    <button type="submit" name="wp-submit" id="wp-submit" class="button button--strong button--accent"><span>Register account</span></button>
                </div>
            </form>

            <p class="login-extras">Already have an account? <a class="link font-bold"
                    href="<?php echo isset($_GET['redirect_to']) ? esc_url(add_query_arg('redirect_to', esc_url_raw(wp_unslash($_GET['redirect_to'])), home_url('/login/'))) : esc_url(home_url('/login/')); ?>">Log
                    in</a></p>

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>