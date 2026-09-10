<?php
/**
 * Template Name: 404 Not Found
 * 
 * The template for displaying 404 pages (not found).
 */

get_header();
get_template_part('content', 'course-header');
?>

<main class="site-main error-404-page" id="main">
    <article class="article prose container +1/2 +middle">
        <h1>Page not found</h1>
        <p>
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>
        <p>
            <a href="<?php echo esc_url(home_url()); ?>" class="button">Return to Home</a>
        </p>
    </article>
</main>

<?php
get_footer();