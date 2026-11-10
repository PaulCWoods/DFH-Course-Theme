<?php
/**
 * Reset password form, in the library's Field markup
 *
 * Overrides woocommerce/templates/myaccount/form-reset-password.php. Keep the
 * hooks, field names and nonce in step with WooCommerce's template when it
 * changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_reset_password_form');
?>

<form method="post" class="form lost_reset_password">

    <p class="text"><?php echo apply_filters('woocommerce_reset_password_message', esc_html__('Enter a new password below.', 'woocommerce')); ?></p><?php // @codingStandardsIgnoreLine ?>

    <div class="field">
        <label for="password_1" class="field__label label"><?php esc_html_e('New password', 'woocommerce'); ?></label>
        <input type="password" class="input woocommerce-Input woocommerce-Input--text" name="password_1" id="password_1" autocomplete="new-password" required />
    </div>
    <div class="field">
        <label for="password_2" class="field__label label"><?php esc_html_e('Re-enter new password', 'woocommerce'); ?></label>
        <input type="password" class="input woocommerce-Input woocommerce-Input--text" name="password_2" id="password_2" autocomplete="new-password" required />
    </div>

    <input type="hidden" name="reset_key" value="<?php echo esc_attr($args['key']); ?>" />
    <input type="hidden" name="reset_login" value="<?php echo esc_attr($args['login']); ?>" />

    <?php do_action('woocommerce_resetpassword_form'); ?>

    <input type="hidden" name="wc_reset_password" value="true" />
    <div class="form__actions button-group">
        <button type="submit" class="button button--strong button--accent" value="<?php esc_attr_e('Save', 'woocommerce'); ?>"><span><?php esc_html_e('Save', 'woocommerce'); ?></span></button>
    </div>

    <?php wp_nonce_field('reset_password', 'woocommerce-reset-password-nonce'); ?>

</form>
<?php
do_action('woocommerce_after_reset_password_form');
