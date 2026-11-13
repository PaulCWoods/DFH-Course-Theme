<?php
/**
 * The cart's way on to checkout, as the page's main action
 *
 * Overrides woocommerce/templates/cart/proceed-to-checkout-button.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="button button--strong button--accent">
    <span><?php esc_html_e('Go to checkout', 'dfh'); ?></span>
    <svg class="icon icon--dir" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#arrow-right" /></svg>
</a>
