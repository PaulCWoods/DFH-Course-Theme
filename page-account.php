<?php
/**
 * Template Name: My Account Page
 *
 * WooCommerce's My account: its title (the page's, or the open section's,
 * which WooCommerce filters in) and the [woocommerce_my_account] content,
 * laid out by woocommerce/myaccount/my-account.php. Logged out, the only
 * sections are the lost and reset password forms (the account's front
 * page sends visitors to /login/), so they sit in a narrow Card.
 */
get_header();
get_template_part('content', 'course-header');
?>
<main class="main" id="main">
    <div class="wrapper py-cushion-lg">
        <?php while (have_posts()):
            the_post(); ?>
            <?php if (is_user_logged_in()): ?>
                <div class="stack">
                    <h1 class="title"><?php the_title(); ?></h1>
                    <?php the_content(); ?>
                </div>
            <?php else: ?>
                <div class="card max-w-md mx-auto">
                    <header class="card__header"><h1 class="card__title title"><?php the_title(); ?></h1></header>
                    <div class="card__body stack"><?php the_content(); ?></div>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
</main>
<?php
get_footer();
