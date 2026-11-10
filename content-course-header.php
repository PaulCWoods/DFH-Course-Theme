<?php
/**
 * The site header: the brand, then the visitor's links (their account, their
 * cart when it has something in it, log out; or log in).
 */
$cart_count = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
$current = function ($is_current) {
    return $is_current ? ' aria-current="page"' : '';
};
?>
<a href="#main" class="skip-link">Skip to main content</a>
<header class="header header--sticky header--translucent max-md:header--compact">
    <div class="header__bar">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="brand">
            <svg class="brand__mark icon" width="1em" height="1em" aria-hidden="true" focusable="false">
                <use href="#BrandDesignForHumans"></use>
            </svg>
            <span class="brand__name">Courses</span>
        </a>
        <nav class="header__nav tab-list tab-list--plain tab-list--scrollable" aria-label="Account">
            <ul>
                <?php if (is_user_logged_in()): ?>
                    <li><a class="tab" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"<?php echo $current(is_account_page()); ?>><span class="tab__text">Account</span></a></li>
                <?php endif; ?>
                <?php if ($cart_count): ?>
                    <li><a class="tab" href="<?php echo esc_url(wc_get_cart_url()); ?>"<?php echo $current(is_cart() || is_checkout()); ?>><span class="tab__text">Cart</span> <span class="badge badge--sm" aria-hidden="true"><span class="badge__text"><?php echo esc_html($cart_count); ?></span></span><span class="sr-only">, <?php echo esc_html(sprintf(_n("%d item", "%d items", $cart_count), $cart_count)); ?></span></a></li>
                <?php endif; ?>
                <?php if (is_user_logged_in()): ?>
                    <li><a class="tab" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>"><span class="tab__text">Log out</span></a></li>
                <?php elseif (!is_page('login')): ?>
                    <li><a class="tab" href="<?php echo esc_url(home_url('/login/')); ?>"><span class="tab__text">Log in</span></a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
