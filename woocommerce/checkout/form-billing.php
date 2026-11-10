<?php
/**
 * Checkout's billing details, under a heading at the page's second level
 *
 * Overrides woocommerce/templates/checkout/form-billing.php. The fields take
 * the library's Field classes from dfh_woocommerce_field_classes(). Keep the
 * hooks in step with WooCommerce's template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined('ABSPATH') || exit;
?>
<div class="woocommerce-billing-fields stack">
    <h2 class="heading">
        <?php wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ? esc_html_e('Billing &amp; Shipping', 'woocommerce') : esc_html_e('Billing details', 'woocommerce'); ?>
    </h2>

    <?php do_action('woocommerce_before_checkout_billing_form', $checkout); ?>

    <div class="woocommerce-billing-fields__field-wrapper form">
        <?php foreach ($checkout->get_checkout_fields('billing') as $key => $field) {
            woocommerce_form_field($key, $field, $checkout->get_value($key));
        } ?>
    </div>

    <?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>
</div>

<?php if (!is_user_logged_in() && $checkout->is_registration_enabled()): ?>
    <div class="woocommerce-account-fields form">
        <?php if (!$checkout->is_registration_required()): ?>
            <label class="choice create-account">
                <input class="checkbox-input" id="createaccount" <?php checked((true === $checkout->get_value('createaccount') || (true === apply_filters('woocommerce_create_account_default_checked', false))), true); ?> type="checkbox" name="createaccount" value="1" />
                <span><?php esc_html_e('Create an account?', 'woocommerce'); ?></span>
            </label>
        <?php endif; ?>

        <?php do_action('woocommerce_before_checkout_registration_form', $checkout); ?>

        <?php if ($checkout->get_checkout_fields('account')): ?>
            <div class="create-account form">
                <?php foreach ($checkout->get_checkout_fields('account') as $key => $field): ?>
                    <?php woocommerce_form_field($key, $field, $checkout->get_value($key)); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php do_action('woocommerce_after_checkout_registration_form', $checkout); ?>
    </div>
<?php endif; ?>
