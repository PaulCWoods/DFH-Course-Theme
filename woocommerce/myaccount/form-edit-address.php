<?php
/**
 * Edit address form, as a library Form
 *
 * Overrides woocommerce/templates/myaccount/form-edit-address.php. The fields
 * take the library's Field classes from dfh_woocommerce_field_classes() in
 * functions.php. Keep the hooks, field names and nonce in step with
 * WooCommerce's template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined('ABSPATH') || exit;

$page_title = ('billing' === $load_address) ? esc_html__('Billing address', 'woocommerce') : esc_html__('Shipping address', 'woocommerce');

do_action('woocommerce_before_edit_account_address_form'); ?>

<?php if (!$load_address): ?>
    <?php wc_get_template('myaccount/my-address.php'); ?>
<?php else: ?>

    <form method="post" class="form prose-end woocommerce-address-fields" novalidate>

        <h2 class="heading"><?php echo apply_filters('woocommerce_my_account_edit_address_title', $page_title, $load_address); ?></h2><?php // @codingStandardsIgnoreLine ?>

        <?php do_action("woocommerce_before_edit_address_form_{$load_address}"); ?>

        <div class="form woocommerce-address-fields__field-wrapper">
            <?php
            foreach ($address as $key => $field) {
                woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, $field['value']));
            }
            ?>
        </div>

        <?php do_action("woocommerce_after_edit_address_form_{$load_address}"); ?>

        <?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
        <input type="hidden" name="action" value="edit_address" />
        <div class="form__actions button-group">
            <button type="submit" class="button button--strong button--accent" name="save_address" value="<?php esc_attr_e('Save address', 'woocommerce'); ?>"><span><?php esc_html_e('Save address', 'woocommerce'); ?></span></button>
        </div>

    </form>

<?php endif; ?>

<?php do_action('woocommerce_after_edit_account_address_form'); ?>
