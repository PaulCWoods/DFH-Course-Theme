<?php
/**
 * My account's sections, as the library's Navigation Tree: beside the
 * content where the Split sits side by side (@3xl, the same container as
 * my-account.php's Split), and in a start-side drawer Dialog below that,
 * opened by a menu Button
 *
 * Overrides woocommerce/templates/myaccount/navigation.php. Keep the hooks
 * in step with WooCommerce's template when it changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_account_navigation');

// The same links in both places
$dfh_account_menu = function () {
    ?>
    <ul>
        <?php foreach (wc_get_account_menu_items() as $endpoint => $label): ?>
            <li>
                <a class="button" href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" <?php echo wc_is_current_account_menu_item($endpoint) ? 'aria-current="page"' : ''; ?>><span><?php echo esc_html($label); ?></span></a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
};
?>
<nav class="navigation-tree navigation-tree--ruled @max-3xl:hide" aria-label="<?php esc_attr_e('Account pages', 'dfh'); ?>">
    <?php $dfh_account_menu(); ?>
</nav>

<div class="button-group @3xl:hide">
    <button type="button" class="button" commandfor="account-nav" command="show-modal" aria-haspopup="dialog">
        <svg class="button__icon icon" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#Navigation" /></svg>
        <span><?php esc_html_e('Account pages', 'dfh'); ?></span>
    </button>
</div>

<dialog id="account-nav" class="dialog dialog--drawer dialog--start" aria-labelledby="account-nav-title">
    <article class="card">
        <header class="card__header">
            <h2 id="account-nav-title" class="card__title small-heading"><?php esc_html_e('Account pages', 'dfh'); ?></h2>
            <button type="button" class="button button--plain button--icon" commandfor="account-nav" command="close"><span class="sr-only"><?php esc_html_e('Close', 'dfh'); ?></span><svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#Close" /></svg></button>
        </header>
        <div class="card__body card__body--scrollable">
            <nav class="navigation-tree" aria-labelledby="account-nav-title">
                <?php $dfh_account_menu(); ?>
            </nav>
        </div>
    </article>
</dialog>
<?php do_action('woocommerce_after_account_navigation'); ?>
