<?php
/**
 * A payment method: the library's Choice, then its fields. The radio sits
 * beside its label, not in it: in the label, the gateways' scripts lose its
 * checked state
 *
 * Overrides woocommerce/templates/checkout/payment-method.php. The radio
 * keeps input-radio, which WooCommerce's script reads (it hides the radio
 * when there's only one method). Keep in step with its template when it
 * changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr($gateway->id); ?> stack">
    <div class="choice">
        <input id="payment_method_<?php echo esc_attr($gateway->id); ?>" type="radio" class="radio-input input-radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php checked($gateway->chosen, true); ?> data-order_button_text="<?php echo esc_attr($gateway->order_button_text); ?>" />
        <label class="font-bold" for="payment_method_<?php echo esc_attr($gateway->id); ?>"><?php echo $gateway->get_title(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?> <?php echo $gateway->get_icon(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></label>
    </div>
    <?php if ($gateway->has_fields() || $gateway->get_description()): ?>
        <div class="payment_box payment_method_<?php echo esc_attr($gateway->id); ?>" <?php if (!$gateway->chosen): ?>style="display:none;"<?php endif; ?>>
            <?php // WooCommerce's script shows and hides the box itself, so the Stack is inside it ?>
            <div class="text stack"><?php $gateway->payment_fields(); ?></div>
        </div>
    <?php endif; ?>
</li>
