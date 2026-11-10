<?php
/**
 * A product in the shop, as the home page's course Card: courses are sold
 * from their own page, so the title links to the course the product sells,
 * and the footer has the course page's buy action (or Enrolled).
 *
 * Overrides woocommerce/templates/content-product.php. It leaves out the
 * loop item hooks: nothing on the site adds to them.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

global $product;

if (empty($product) || !$product->is_visible()) {
    return;
}

$course = dfh_course_for_product($product->get_id());
$user_id = get_current_user_id();
$has_access = $user_id && dfh_user_has_course_access($user_id);
$image_id = $course && has_post_thumbnail($course) ? get_post_thumbnail_id($course) : $product->get_image_id();
?>
<li>
    <article class="card index-card">
        <?php if ($image_id): ?>
            <div class="card__thumb"><?php echo wp_get_attachment_image($image_id, 'medium_large', false, array('alt' => '')); ?></div>
        <?php endif; ?>
        <div class="card__content">
            <header class="card__header">
                <h2 class="card__title heading"><a class="link link--plain link--stretched" href="<?php echo esc_url($course ? get_permalink($course) : $product->get_permalink()); ?>"><?php echo esc_html($course ? get_the_title($course) : $product->get_name()); ?></a></h2>
                <?php if ($has_access): ?>
                    <span class="status status--plain status--accent"><span class="status__text">Enrolled</span></span>
                <?php endif; ?>
            </header>
            <div class="card__body stack">
                <?php if ($course && has_excerpt($course)): ?>
                    <p class="text"><?php echo esc_html(get_the_excerpt($course)); ?></p>
                <?php endif; ?>
                <p class="large-text"><?php echo wp_kses_post($product->get_price_html()); ?></p>
            </div>
            <?php if ($user_id && !$has_access && $product->is_purchasable()): ?>
                <footer class="card__footer">
                    <form action="<?php echo esc_url(wc_get_checkout_url()); ?>" method="post" class="button-group">
                        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" />
                        <button type="submit" class="button button--strong button--accent">Buy the course</button>
                    </form>
                </footer>
            <?php endif; ?>
        </div>
    </article>
</li>
