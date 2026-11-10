<?php
/**
 * Cart totals: a Card with the sums in a Table and the way on to checkout
 *
 * Overrides woocommerce/templates/cart/cart-totals.php. WooCommerce's script
 * replaces div.cart_totals whole; keep the class and the hooks in step with
 * its template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.3.6
 */

defined('ABSPATH') || exit;

?>
<div class="cart_totals card <?php echo (WC()->customer->has_calculated_shipping()) ? 'calculated_shipping' : ''; ?>">

    <?php do_action('woocommerce_before_cart_totals'); ?>

    <header class="card__header"><h2 class="card__title heading"><?php esc_html_e('Cart totals', 'woocommerce'); ?></h2></header>

    <div class="card__body">
        <table class="table table--plain shop_table">
            <tr>
                <th scope="row"><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
                <td class="table__cell--numeric"><?php wc_cart_totals_subtotal_html(); ?></td>
            </tr>

            <?php foreach (WC()->cart->get_coupons() as $code => $coupon): ?>
                <tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                    <th scope="row"><?php wc_cart_totals_coupon_label($coupon); ?></th>
                    <td class="table__cell--numeric"><?php wc_cart_totals_coupon_html($coupon); ?></td>
                </tr>
            <?php endforeach; ?>

            <?php foreach (WC()->cart->get_fees() as $fee): ?>
                <tr class="fee">
                    <th scope="row"><?php echo esc_html($fee->name); ?></th>
                    <td class="table__cell--numeric"><?php wc_cart_totals_fee_html($fee); ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()): ?>
                <?php if ('itemized' === get_option('woocommerce_tax_total_display')): ?>
                    <?php foreach (WC()->cart->get_tax_totals() as $code => $tax): // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
                        <tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                            <th scope="row"><?php echo esc_html($tax->label); ?></th>
                            <td class="table__cell--numeric"><?php echo wp_kses_post($tax->formatted_amount); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <th scope="row"><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
                        <td class="table__cell--numeric"><?php wc_cart_totals_taxes_total_html(); ?></td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>

            <?php do_action('woocommerce_cart_totals_before_order_total'); ?>

            <tr>
                <th scope="row"><?php esc_html_e('Total', 'woocommerce'); ?></th>
                <td class="table__cell--numeric"><?php wc_cart_totals_order_total_html(); ?></td>
            </tr>

            <?php do_action('woocommerce_cart_totals_after_order_total'); ?>
        </table>
    </div>

    <footer class="card__footer">
        <div class="button-group">
            <?php do_action('woocommerce_proceed_to_checkout'); ?>
        </div>
    </footer>

    <?php do_action('woocommerce_after_cart_totals'); ?>

</div>
