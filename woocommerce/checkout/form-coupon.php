<?php
/**
 * Checkout's coupon form, in the library's Field markup, opened from the
 * notice above it
 *
 * Overrides woocommerce/templates/checkout/form-coupon.php. WooCommerce's
 * script shows the form (.showcoupon, #woocommerce-checkout-form-coupon).
 * Keep them in step with its template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

defined('ABSPATH') || exit;

if (!wc_coupons_enabled()) { // @codingStandardsIgnoreLine.
    return;
}
?>
<div class="woocommerce-form-coupon-toggle">
    <?php wc_print_notice(apply_filters('woocommerce_checkout_coupon_message', esc_html__('Have a coupon?', 'woocommerce') . ' <a href="#" role="button" aria-controls="woocommerce-checkout-form-coupon" aria-expanded="false" class="showcoupon link">' . esc_html__('Enter your code', 'dfh') . '</a>'), 'notice'); ?>
</div>

<form class="checkout_coupon woocommerce-form-coupon form max-w-md" method="post" style="display:none" id="woocommerce-checkout-form-coupon">
    <div class="field">
        <label for="coupon_code" class="field__label label"><?php esc_html_e('Coupon code', 'woocommerce'); ?></label>
        <input type="text" name="coupon_code" class="input" id="coupon_code" value="" autocomplete="off" />
    </div>
    <div class="form__actions button-group">
        <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"><?php esc_html_e('Apply coupon', 'woocommerce'); ?></button>
    </div>
</form>
