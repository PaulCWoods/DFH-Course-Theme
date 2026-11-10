<?php
/**
 * Lost password form, in the library's Field markup
 *
 * Overrides woocommerce/templates/myaccount/form-lost-password.php. Keep the
 * hooks, field names and nonce in step with WooCommerce's template when it
 * changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_lost_password_form');
?>

<form method="post" class="form lost_reset_password">

    <p class="text" id="user_login_hint"><?php echo apply_filters('woocommerce_lost_password_message', esc_html__('Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce')); ?></p><?php // @codingStandardsIgnoreLine ?>

    <div class="field">
        <label for="user_login" class="field__label label"><?php esc_html_e('Username or email', 'woocommerce'); ?></label>
        <input type="text" class="input woocommerce-Input woocommerce-Input--text" name="user_login" id="user_login" autocomplete="username" aria-describedby="user_login_hint" required />
    </div>

    <?php do_action('woocommerce_lostpassword_form'); ?>

    <input type="hidden" name="wc_reset_password" value="true" />
    <div class="form__actions button-group">
        <button type="submit" class="button button--strong button--accent" value="<?php esc_attr_e('Reset password', 'woocommerce'); ?>"><span><?php esc_html_e('Reset password', 'woocommerce'); ?></span></button>
    </div>

    <?php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); ?>

</form>
<?php
do_action('woocommerce_after_lost_password_form');
