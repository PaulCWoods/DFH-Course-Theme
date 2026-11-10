<?php
/**
 * My addresses, each in a library Card with its edit link
 *
 * Overrides woocommerce/templates/myaccount/my-address.php. Keep the hooks
 * and filters in step with WooCommerce's template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined('ABSPATH') || exit;

$customer_id = get_current_user_id();
$address_titles = array('billing' => __('Billing address', 'woocommerce'));
if (!wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
    $address_titles['shipping'] = __('Shipping address', 'woocommerce');
}
$get_addresses = apply_filters('woocommerce_my_account_get_addresses', $address_titles, $customer_id);
?>
<p><?php echo apply_filters('woocommerce_my_account_my_address_description', esc_html__('The following addresses will be used on the checkout page by default.', 'woocommerce')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

<ul class="grid grid--2-col grid--xs prose-end" role="list">
    <?php foreach ($get_addresses as $name => $address_title):
        $address = wc_get_account_formatted_address($name); ?>
        <li class="card">
            <header class="card__header">
                <h2 class="card__title heading"><?php echo esc_html($address_title); ?></h2>
            </header>
            <div class="card__body">
                <address class="text">
                    <?php
                    echo $address ? wp_kses_post($address) : esc_html__('You have not set up this type of address yet.', 'woocommerce');
                    /**
                     * Used to output content after core address fields.
                     *
                     * @param string $name Address type.
                     * @since 8.7.0
                     */
                    do_action('woocommerce_my_account_after_my_address', $name);
                    ?>
                </address>
            </div>
            <footer class="card__footer">
                <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address', $name)); ?>" class="button">
                    <?php printf($address ? esc_html__('Edit %s', 'woocommerce') : esc_html__('Add %s', 'woocommerce'), esc_html(strtolower($address_title))); ?>
                </a>
            </footer>
        </li>
    <?php endforeach; ?>
</ul>
