<?php
/**
 * Show error messages, as the library's Banner (dfh_notice_banner() in functions.php).
 * One Banner for all of them, read in order with the page.
 *
 * Overrides woocommerce/templates/notices/error.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $notices ) {
	return;
}

dfh_notice_banner($notices, 'danger', 'Warning', 'woocommerce-error');
