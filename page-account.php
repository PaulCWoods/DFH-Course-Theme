<?php
/**
 * Template Name: My Account Page
 * 
 */
get_header();
get_template_part('content', 'account-header');
?>

<main class="site-main shop-main" id="main">
    <!-- Use a wider container or full layout for checkout/cart, prose for normal pages -->
    <div class="article container my-account">
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