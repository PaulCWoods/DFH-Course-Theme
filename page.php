<?php
/**
 * The template for displaying all single pages.
 */

get_header();
get_template_part('content', 'course-header');
?>

<main class="site-main" id="main">
    <article class="prose container blog article">
        <?php while (have_posts()): the_post(); ?>
            <header class="article-header">
                <h1><?php the_title(); ?></h1>
            </header>
            <?php the_content(); ?>
        <?php endwhile; ?>
    </article>
</main>

<?php
get_footer();


