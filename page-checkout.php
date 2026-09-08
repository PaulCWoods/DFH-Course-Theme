<?php
/**
 * Template Name: Checkout Page
 * 
 */
get_header();
get_template_part('content', 'account-header');
?>

<main class="site-main shop-main" id="main">
    <!-- Use a wider container or full layout for checkout/cart, prose for normal pages -->
    <article class="article container shop">
        
        <header class="article-header">
            <h1 class="title"><? class_exists('WooCommerce') && is_cart() ? 'Shopping Cart' : the_title(); ?></h1>
        </header>

        <div class="article-content">
            <?php
            while ( have_posts() ) :
                the_post();
                the_content();
            endwhile;
            ?>
        </div>

    </article>
</main>

<?php
get_footer();