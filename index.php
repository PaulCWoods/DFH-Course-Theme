<?php
/**
 * The main template fallback file for dfh-course-theme.
 */

get_header();
?>

<main class="site-main">
    <div class="container">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();
                the_content();
            endwhile;
        endif;
        ?>
    </div>
</main>

<?php
get_footer();