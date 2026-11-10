<?php
/**
 * My account: the navigation beside the open section, which takes the
 * library's Prose. Narrower than the sidebar layout, the navigation comes
 * first and the section under it.
 *
 * Overrides woocommerce/templates/myaccount/my-account.php. Keep the hooks
 * in step with WooCommerce's template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined('ABSPATH') || exit;
?>
<div class="split @3xl:split--sidebar">
    <?php do_action('woocommerce_account_navigation'); ?>
    <div class="woocommerce-MyAccount-content prose">
        <?php do_action('woocommerce_account_content'); ?>
    </div>
</div>
