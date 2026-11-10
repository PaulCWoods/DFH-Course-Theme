<?php
/**
 * Cart: the courses in a Table, a coupon Field, and the totals beside
 *
 * Overrides woocommerce/templates/cart/cart.php. A course sells one at a
 * time (dfh_sold_individually), so there's no quantity or Update cart.
 * WooCommerce's script reads .woocommerce-cart-form, .product-remove > a,
 * #coupon_code and [name="apply_coupon"]: keep them, and the hooks, in step
 * with its template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 11.0.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>

<div class="grid grid--2-col grid--sm grid--cushioned">
    <form class="woocommerce-cart-form stack" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <?php do_action('woocommerce_before_cart_table'); ?>

        <div class="table-container" tabindex="0" role="region" aria-label="<?php esc_attr_e('Cart', 'woocommerce'); ?>">
            <table class="table shop_table cart woocommerce-cart-form__contents">
                <thead>
                    <tr>
                        <th scope="col" class="product-name"><?php esc_html_e('Course', 'dfh'); ?></th>
                        <th scope="col" class="product-subtotal table__cell--numeric"><?php esc_html_e('Price', 'woocommerce'); ?></th>
                        <th scope="col" class="product-remove table__cell--fit"><span class="sr-only"><?php esc_html_e('Remove item', 'woocommerce'); ?></span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php do_action('woocommerce_before_cart_contents'); ?>

                    <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item):
                        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                        $visible = apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key);

                        if (!($_product instanceof WC_Product) || !$_product->exists() || $cart_item['quantity'] <= 0 || !$visible) {
                            continue;
                        }
                        $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                        $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('thumbnail', array('class' => 'thumbnail', 'alt' => '')), $cart_item, $cart_item_key);
                        ?>
                        <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                            <th scope="row" class="product-name">
                                <div class="inline">
                                    <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    <div class="lockup">
                                        <?php if ($product_permalink): ?>
                                            <a class="link text font-bold" href="<?php echo esc_url($product_permalink); ?>"><?php echo wp_kses_post($product_name); ?></a>
                                        <?php else: ?>
                                            <span class="text font-bold"><?php echo wp_kses_post($product_name); ?></span>
                                        <?php endif; ?>
                                        <?php do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key); ?>
                                        <?php echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </div>
                                </div>
                            </th>
                            <td class="product-subtotal table__cell--numeric">
                                <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </td>
                            <td class="product-remove table__cell--fit">
                                <?php echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    'woocommerce_cart_item_remove_link',
                                    sprintf(
                                        '<a role="button" href="%s" class="remove button button--plain button--icon" data-product_id="%s" data-product_sku="%s"><svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#Delete" /></svg><span class="sr-only">%s</span></a>',
                                        esc_url(wc_get_cart_remove_url($cart_item_key)),
                                        esc_attr($product_id),
                                        esc_attr($_product->get_sku()),
                                        /* translators: %s is the product name */
                                        esc_html(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name)))
                                    ),
                                    $cart_item_key
                                ); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php do_action('woocommerce_cart_contents'); ?>
                    <?php do_action('woocommerce_after_cart_contents'); ?>
                </tbody>
            </table>
        </div>

        <?php if (wc_coupons_enabled()): ?>
            <div class="coupon form">
                <div class="field">
                    <label for="coupon_code" class="field__label label"><?php esc_html_e('Coupon code', 'woocommerce'); ?></label>
                    <input type="text" name="coupon_code" class="input" id="coupon_code" value="" autocomplete="off" />
                </div>
                <div class="form__actions button-group">
                    <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"><?php esc_html_e('Apply coupon', 'woocommerce'); ?></button>
                    <?php do_action('woocommerce_cart_coupon'); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php do_action('woocommerce_cart_actions'); ?>
        <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>

        <?php do_action('woocommerce_after_cart_table'); ?>
    </form>

    <?php do_action('woocommerce_before_cart_collaterals'); ?>

    <div class="cart-collaterals stack self-start">
        <?php do_action('woocommerce_cart_collaterals'); ?>
    </div>
</div>

<?php do_action('woocommerce_after_cart'); ?>
