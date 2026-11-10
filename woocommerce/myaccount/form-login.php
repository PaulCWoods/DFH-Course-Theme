<?php
/**
 * Login form, in the library's Field markup
 *
 * Overrides woocommerce/templates/myaccount/form-login.php. Registration in
 * My Account is off (students register at /register/), so only the login
 * form is here. Keep the hooks, field names and nonce in step with
 * WooCommerce's template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_customer_login_form'); ?>

<h2 class="heading"><?php esc_html_e('Log in', 'woocommerce'); ?></h2>

<form class="form" method="post" novalidate>

    <?php do_action('woocommerce_login_form_start'); ?>

    <div class="field">
        <label for="username" class="field__label label"><?php esc_html_e('Username or email address', 'woocommerce'); ?></label>
        <input type="text" class="input woocommerce-Input woocommerce-Input--text" name="username" id="username" autocomplete="username" value="<?php echo (!empty($_POST['username']) && is_string($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required /><?php // @codingStandardsIgnoreLine ?>
    </div>
    <div class="field">
        <label for="password" class="field__label label"><?php esc_html_e('Password', 'woocommerce'); ?></label>
        <input type="password" class="input woocommerce-Input woocommerce-Input--text" name="password" id="password" autocomplete="current-password" required />
    </div>

    <?php do_action('woocommerce_login_form'); ?>

    <label class="choice">
        <input type="checkbox" class="checkbox-input" name="rememberme" id="rememberme" value="forever" />
        <span><?php esc_html_e('Remember me', 'woocommerce'); ?></span>
    </label>
    <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
    <div class="form__actions button-group">
        <button type="submit" class="button button--strong button--accent" name="login" value="<?php esc_attr_e('Log in', 'woocommerce'); ?>"><span><?php esc_html_e('Log in', 'woocommerce'); ?></span></button>
    </div>
    <p class="text">
        <a class="link" href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Lost your password?', 'woocommerce'); ?></a>
    </p>

    <?php do_action('woocommerce_login_form_end'); ?>

</form>

<?php do_action('woocommerce_after_customer_login_form'); ?>
