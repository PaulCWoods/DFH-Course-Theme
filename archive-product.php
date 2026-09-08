<?php
get_header();
?>

<main class="site-main shop-archive" id="main">
    <article class="container">
        <header class="article-header">
            <h1 class="display-title"><?php woocommerce_page_title(); ?></h1>
        </header>

        <div class="article-content">
            <?php
            if ( have_posts() ) {
                // WooCommerce loop container wrapper
                woocommerce_product_loop_start();
                
                while ( have_posts() ) {
                    the_post();
                    wc_get_template_part( 'content', 'product' );
                }
                
                woocommerce_product_loop_end();
            } else {
                do_action( 'woocommerce_no_products_found' );
            }
            ?>
        </div>
    </article>
</main>

<?php
get_footer();