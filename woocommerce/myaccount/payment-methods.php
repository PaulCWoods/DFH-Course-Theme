<?php
/**
 * Saved payment methods: a Table (dfh_woocommerce_tables wraps it), each
 * card's actions as small Buttons, and a Button to add one
 *
 * Overrides woocommerce/templates/myaccount/payment-methods.php. Keep the
 * hooks in step with WooCommerce's template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.9.0
 */

defined('ABSPATH') || exit;

$saved_methods = wc_get_customer_saved_methods_list(get_current_user_id());
$has_methods = (bool) $saved_methods;
$columns = wc_get_account_payment_methods_columns();

do_action('woocommerce_before_account_payment_methods', $has_methods); ?>

<div class="stack prose-end">
    <?php if ($has_methods): ?>
        <table class="woocommerce-MyAccount-paymentMethods shop_table account-payment-methods-table">
            <thead>
                <tr>
                    <?php foreach ($columns as $column_id => $column_name): ?>
                        <th scope="col"
                            class="woocommerce-PaymentMethod--<?php echo esc_attr($column_id); ?><?php echo 'actions' === $column_id ? ' table__cell--fit' : ''; ?>">
                            <?php if ('actions' === $column_id): ?>
                                <span class="sr-only"><?php echo esc_html($column_name); ?></span>
                            <?php else: ?>
                                <?php echo esc_html($column_name); ?>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($saved_methods as $type => $methods): // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
                    <?php foreach ($methods as $method):
                        $card = !empty($method['method']['last4'])
                            /* translators: 1: credit card type 2: last 4 digits */
                            ? sprintf(esc_html__('%1$s ending in %2$s', 'woocommerce'), esc_html(wc_get_credit_card_type_label($method['method']['brand'])), esc_html($method['method']['last4']))
                            : esc_html(wc_get_credit_card_type_label($method['method']['brand']));
                        ?>
                        <tr class="payment-method<?php echo !empty($method['is_default']) ? ' default-payment-method' : ''; ?>">
                            <?php foreach ($columns as $column_id => $column_name): ?>
                                <?php if ('method' === $column_id): ?>
                                    <th scope="row" class="woocommerce-PaymentMethod--method">
                                        <?php if (has_action('woocommerce_account_payment_methods_column_method')) {
                                            do_action('woocommerce_account_payment_methods_column_method', $method);
                                        } else {
                                            echo $card; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                        } ?>
                                        <?php if (!empty($method['is_default'])): ?>
                                            <span class="status status--subtle"><span
                                                    class="status__text"><?php esc_html_e('Default', 'woocommerce'); ?></span></span>
                                        <?php endif; ?>
                                    </th>
                                <?php else: ?>
                                    <td
                                        class="woocommerce-PaymentMethod--<?php echo esc_attr($column_id); ?><?php echo 'actions' === $column_id ? ' table__cell--fit' : ''; ?>">
                                        <?php if (has_action('woocommerce_account_payment_methods_column_' . $column_id)) {
                                            do_action('woocommerce_account_payment_methods_column_' . $column_id, $method);
                                        } elseif ('expires' === $column_id) {
                                            echo esc_html($method['expires']);
                                        } elseif ('actions' === $column_id) { ?>
                                            <div class="button-group buttons-sm">
                                                <?php foreach ($method['actions'] as $key => $action): // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
                                                    <a href="<?php echo esc_url($action['url']); ?>"
                                                        class="button<?php echo 'delete' === $key ? ' button--danger' : ''; ?> <?php echo sanitize_html_class($key); ?>">
                                                        <?php echo esc_html($action['name']); ?><span class="sr-only">
                                                            <?php echo $card; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php } ?>
                                    </td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <?php wc_print_notice(esc_html__('No saved methods found.', 'woocommerce'), 'notice'); ?>
    <?php endif; ?>

    <?php do_action('woocommerce_after_account_payment_methods', $has_methods); ?>

    <?php if (WC()->payment_gateways->get_available_payment_gateways()): ?>
        <div class="button-group">
            <a class="button"
                href="<?php echo esc_url(wc_get_endpoint_url('add-payment-method')); ?>"><?php esc_html_e('Add payment method', 'woocommerce'); ?></a>
        </div>
    <?php endif; ?>
</div>