<?php
/**
 * Page not found: the cart and checkout's page frame, with a way back to
 * the courses.
 */

get_header();
get_template_part('content', 'course-header');
?>
<main class="main" id="main">
    <div class="wrapper stack py-cushion-lg">
        <h1 class="title">Page not found</h1>
        <p class="text">This page may have moved or been removed. Check the address, or go back to the courses.</p>
        <div class="button-group">
            <a href="<?php echo esc_url(home_url()); ?>" class="button button--strong button--accent">Go to the courses</a>
        </div>
    </div>
</main>
<?php
get_footer();
