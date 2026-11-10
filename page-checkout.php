<?php
/**
 * Template Name: Checkout Page
 *
 * The cart and checkout (their pages hold WooCommerce's classic shortcodes;
 * the overrides in woocommerce/cart and woocommerce/checkout lay them out),
 * the order received page after checkout, and the shop (page-shop.php).
 */
get_header();
get_template_part('content', 'course-header');
?>
<main class="main" id="main">
    <div class="wrapper stack py-cushion-lg">
        <h1 class="title"><?php the_title(); ?></h1>
        <?php
        while (have_posts()):
            the_post();
            the_content();
        endwhile;
        ?>
    </div>
</main>
<?php
get_footer();
