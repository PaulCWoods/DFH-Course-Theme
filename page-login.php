<?php
/**
 * Template Name: Student Login
 */
get_header();
get_template_part('content', 'course-header');
?>
<main class="site-main login-page" id="main">
    <div class="container">
    <h1 class="heading">Log in to your account</h1>
    
    <?php
    if ( is_user_logged_in() ) {
        echo '<p class="login-info">You are already logged in!</p>';
        echo '<p><a href="' . esc_url( home_url() ) . '" class="button">Go to Your Course &rarr;</a></p>';
    } else {
        // Use `redirect_to` query parameter if provided, otherwise default to course archive
        $redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : home_url();

        // Render WordPress login form with redirect back to the desired URL
        wp_login_form( array(
            'redirect'       => $redirect_to,
            'remember'       => true,
            'label_username' => __( 'Email or Username', 'dfh' ),
            'label_log'      => __( 'Log In', 'dfh' ),
        ));

        echo '<p class="login-extras" class="login-forgotpassword"><a class="link" href="' . esc_url( wp_lostpassword_url() ) . '">Forgot your password?</a></p>';
        $register_url = isset($_REQUEST['redirect_to']) ? add_query_arg('redirect_to', esc_url_raw(wp_unslash($_REQUEST['redirect_to'])), home_url('/register/')) : home_url('/register/');
        echo '<p class="login-extras">Don\'t have an account? <a class="link" href="' . esc_url( $register_url ) . '">Register here.</a></p>';
    }
    ?>
    </div>
</main>

<?php get_footer(); ?>