<?php
/**
 * Template Name: Checkout Page
 * 
 */
get_header();
get_template_part('content', 'account-header');
?>

<main class="main shop-main" id="main">
    <!-- Use a wider container or full layout for checkout/cart, prose for normal pages -->
    <article class="article wrapper shop">

        <header class="article__header">
            <h1 class="title"><?php if (class_exists('WooCommerce') && is_cart()) : ?>Shopping cart<?php else : the_title(); endif; ?></h1>
        </header>

        <div class="article__content">
            <?php
            while (have_posts()):
                the_post();
                the_content();
            endwhile;
            ?>
        </div>

    </article>
</main>

<?php
get_footer();