<?php
/**
 * Add a payment method: each gateway as a Choice (the radio beside its
 * label, not in it, where the gateways' scripts lose its checked state), its
 * fields under it, and the form's main action
 *
 * Overrides woocommerce/templates/myaccount/form-add-payment-method.php.
 * WooCommerce's script reads #add_payment_method, #payment, .payment_box,
 * input[name="payment_method"] and #place_order: keep them, and the hooks,
 * in step with its template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.9.0
 */

defined('ABSPATH') || exit;

$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

if ($available_gateways):
    current($available_gateways)->set_current();
    ?>
    <form id="add_payment_method" class="form prose-end" method="post">
        <div id="payment" class="stack">
            <ul role="list" class="woocommerce-PaymentMethods payment_methods methods stack" aria-label="<?php esc_attr_e('Payment methods', 'woocommerce'); ?>">
                <?php foreach ($available_gateways as $gateway): ?>
                    <li class="woocommerce-PaymentMethod woocommerce-PaymentMethod--<?php echo esc_attr($gateway->id); ?> payment_method_<?php echo esc_attr($gateway->id); ?> stack">
                        <div class="choice">
                            <input id="payment_method_<?php echo esc_attr($gateway->id); ?>" type="radio" class="radio-input input-radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php checked($gateway->chosen, true); ?> />
                            <label class="font-bold" for="payment_method_<?php echo esc_attr($gateway->id); ?>"><?php echo wp_kses_post($gateway->get_title()); ?> <?php echo wp_kses_post($gateway->get_icon()); ?></label>
                        </div>
                        <?php if ($gateway->has_fields() || $gateway->get_description()): ?>
                            <div class="woocommerce-PaymentBox woocommerce-PaymentBox--<?php echo esc_attr($gateway->id); ?> payment_box payment_method_<?php echo esc_attr($gateway->id); ?>" style="display: none;">
                                <div class="text stack"><?php $gateway->payment_fields(); ?></div>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php do_action('woocommerce_add_payment_method_form_bottom'); ?>

            <div class="form__actions button-group">
                <?php wp_nonce_field('woocommerce-add-payment-method', 'woocommerce-add-payment-method-nonce'); ?>
                <button type="submit" class="button button--strong button--accent alt" id="place_order" value="<?php esc_attr_e('Add payment method', 'woocommerce'); ?>"><?php esc_html_e('Add payment method', 'woocommerce'); ?></button>
                <input type="hidden" name="woocommerce_add_payment_method" id="woocommerce_add_payment_method" value="1" />
            </div>
        </div>
    </form>
<?php else: ?>
    <?php wc_print_notice(esc_html__('New payment methods can only be added during checkout. Please contact us if you require assistance.', 'woocommerce'), 'notice'); ?>
<?php endif; ?>
