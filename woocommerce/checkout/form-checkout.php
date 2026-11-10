<?php
/**
 * Checkout: the customer's details beside a Card with the order and payment
 *
 * Overrides woocommerce/templates/checkout/form-checkout.php. Keep the hooks,
 * ids and classes WooCommerce's script reads (#customer_details,
 * #order_review) in step with its template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo '<p class="text">' . esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce'))) . '</p>';
    return;
}
?>
<form name="checkout" method="post" class="checkout woocommerce-checkout grid grid--2-col grid--sm grid--cushioned" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">

    <div class="stack">
        <?php if ($checkout->get_checkout_fields()): ?>
            <?php do_action('woocommerce_checkout_before_customer_details'); ?>
            <div class="stack" id="customer_details">
                <?php do_action('woocommerce_checkout_billing'); ?>
                <?php do_action('woocommerce_checkout_shipping'); ?>
            </div>
            <?php do_action('woocommerce_checkout_after_customer_details'); ?>
        <?php endif; ?>
    </div>

    <section class="card self-start" aria-labelledby="order_review_heading">
        <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
        <header class="card__header"><h2 id="order_review_heading" class="card__title heading"><?php esc_html_e('Your order', 'woocommerce'); ?></h2></header>
        <?php do_action('woocommerce_checkout_before_order_review'); ?>
        <div id="order_review" class="card__body stack woocommerce-checkout-review-order">
            <?php
            // The review table takes the library's Table (dfh_review_order_table)
            ob_start();
            do_action('woocommerce_checkout_order_review');
            echo dfh_review_order_table(ob_get_clean()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        </div>
        <?php do_action('woocommerce_checkout_after_order_review'); ?>
    </section>

</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
