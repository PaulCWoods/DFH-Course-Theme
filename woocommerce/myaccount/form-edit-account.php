<?php
/**
 * Edit account form, in the library's Field markup
 *
 * Overrides woocommerce/templates/myaccount/form-edit-account.php. Keep the
 * hooks, field names and nonce in step with WooCommerce's template when it
 * changes version.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 11.0.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_edit_account_form');
?>

<form class="form prose-end" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>

    <?php do_action('woocommerce_edit_account_form_start'); ?>

    <div class="field">
        <label for="account_first_name" class="field__label label"><?php esc_html_e('First name', 'woocommerce'); ?></label>
        <input type="text" class="input woocommerce-Input woocommerce-Input--text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr($user->first_name); ?>" aria-required="true" />
    </div>
    <div class="field">
        <label for="account_last_name" class="field__label label"><?php esc_html_e('Last name', 'woocommerce'); ?></label>
        <input type="text" class="input woocommerce-Input woocommerce-Input--text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr($user->last_name); ?>" aria-required="true" />
    </div>
    <div class="field">
        <label for="account_display_name" class="field__label label"><?php esc_html_e('Display name', 'woocommerce'); ?></label>
        <p id="account_display_name_description" class="field__hint small-text"><?php echo wc_reviews_enabled() ? esc_html__('This will be how your name will be displayed in the account section and in reviews', 'woocommerce') : esc_html__('This will be how your name will be displayed in the account section', 'woocommerce'); ?></p>
        <input type="text" class="input woocommerce-Input woocommerce-Input--text" name="account_display_name" id="account_display_name" aria-describedby="account_display_name_description" value="<?php echo esc_attr($user->display_name); ?>" aria-required="true" />
    </div>
    <div class="field">
        <label for="account_email" class="field__label label"><?php esc_html_e('Email address', 'woocommerce'); ?></label>
        <input type="email" class="input woocommerce-Input woocommerce-Input--email" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr($user->user_email); ?>" aria-required="true" />
    </div>

    <?php do_action('woocommerce_edit_account_form_fields'); ?>

    <fieldset class="fieldset" aria-describedby="password_change_hint">
        <legend class="fieldset__legend label"><?php esc_html_e('Password change', 'woocommerce'); ?></legend>
        <p id="password_change_hint" class="fieldset__hint small-text"><?php esc_html_e('Leave these blank to keep your current password.', 'dfh'); ?></p>

        <div class="field">
            <label for="password_current" class="field__label label"><?php esc_html_e('Current password', 'woocommerce'); ?></label>
            <input type="password" class="input woocommerce-Input woocommerce-Input--password" name="password_current" id="password_current" autocomplete="current-password" />
        </div>
        <div class="field">
            <label for="password_1" class="field__label label"><?php esc_html_e('New password', 'woocommerce'); ?></label>
            <input type="password" class="input woocommerce-Input woocommerce-Input--password" name="password_1" id="password_1" autocomplete="new-password" />
        </div>
        <div class="field">
            <label for="password_2" class="field__label label"><?php esc_html_e('Confirm new password', 'woocommerce'); ?></label>
            <input type="password" class="input woocommerce-Input woocommerce-Input--password" name="password_2" id="password_2" autocomplete="new-password" />
        </div>
    </fieldset>

    <?php do_action('woocommerce_edit_account_form'); ?>

    <?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
    <input type="hidden" name="action" value="save_account_details" />
    <div class="form__actions button-group">
        <button type="submit" class="button button--strong button--accent" name="save_account_details" value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>"><span><?php esc_html_e('Save changes', 'woocommerce'); ?></span></button>
    </div>

    <?php do_action('woocommerce_edit_account_form_end'); ?>
</form>

<?php do_action('woocommerce_after_edit_account_form'); ?>
