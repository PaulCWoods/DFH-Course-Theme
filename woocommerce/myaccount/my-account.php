<?php
/**
 * My Account page, with its content in the library's Prose
 *
 * Overrides woocommerce/templates/myaccount/my-account.php. The forms in
 * woocommerce/myaccount/ end the Prose with .prose-end. Keep the hooks in
 * step with WooCommerce's template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_account_navigation'); ?>

<div class="woocommerce-MyAccount-content prose">
    <?php do_action('woocommerce_account_content'); ?>
</div>
