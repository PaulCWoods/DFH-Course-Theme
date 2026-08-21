<?php
/**
 * Template Name: Single Lesson
 * Template Post Type: lesson
 * 
 * The template for displaying all single lesson posts.
 */

get_header();
?>
<main class="home site-main">
    <article class="prose container">
        <header class="article-header">
            <h1 class="display-title">Design for Humans: Courses</h1>
        </header>
        <?php the_content(); ?>
    </article>


    <!-- Hero Section -->
    <section class="home-hero">
        <div class="container">
                <?php
                // Determine the target URL for the hero button: prefer the user's active course when available.
                $hero_target = home_url('/course/');
                if (is_user_logged_in() && function_exists('dfh_get_student_current_lesson')) {
                    $active_lesson = dfh_get_student_current_lesson();
                    $current_user = wp_get_current_user();
                    $user_name = '';
                    if ($current_user && $current_user->ID) {
                        $user_name = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
                    }
                    if (is_int($active_lesson) && $active_lesson > 0 && function_exists('dfh_get_courses_for_lesson')) {
                        $courses = dfh_get_courses_for_lesson($active_lesson);
                        if (!empty($courses)) {
                            $hero_target = get_permalink((int) $courses[0]);
                        }
                    }
                }

                if (is_user_logged_in()): ?>
                <p class="home-hero__logged-in title">Welcome back, <?php echo esc_html($user_name); ?></p>
                <div class="home-hero__access">
                    <a href="<?php echo esc_url($hero_target); ?>" class="button strong">
                        Resume your course
                        <svg class="icon dir" width="32" height="32" aria-hidden="true">
                            <use href="#ArrowRight" />
                        </svg>
                    </a>
                    <a class="button" href="<?php echo esc_url(wp_logout_url(home_url())); ?>">Log Out</a>
                </div>
                <?php else: ?>

                <div class="home-hero__access">
                    <a href="<?php echo esc_url(home_url('/register/')); ?>" class="button strong">Get Started</a>
                    <a href="<?php echo esc_url(home_url('/login/')); ?>" class="button">Log In</a>
                </div>
                <?php endif; ?>
        </div>
    </section>

    <!-- Courses Grid Section -->
    <section class="home-section">
        <div class="container">
            <h2 class="title">Available courses</h2>

            <?php
            $courses_query = new WP_Query(array(
                'post_type' => 'course',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'menu_order title',
                'order' => 'ASC',
            ));

            if ($courses_query->have_posts()):
                ?>
                    <ul class="course-card-list index-card-list">
                        <?php while ($courses_query->have_posts()):
                            $courses_query->the_post(); ?>
                                <li class="index-card-list__item">
                                    <div class="index-card course-card">
                                        <?php if (has_post_thumbnail()): ?>
                                                <div class="index-card__thumb">
                                                    <a href="<?php the_permalink(); ?>">
                                                        <?php the_post_thumbnail('medium_large'); ?>
                                                    </a>
                                                </div>
                                        <?php endif; ?>
                                        <div class="index-card__content">
                                            <a class="index-card__link link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            <?php the_excerpt(); ?>
                                            <?php if (function_exists('dfh_user_has_course_access') && dfh_user_has_course_access()): ?>
                                                    <span class="course-card__badge enrolled badge">Enroled</span>
                                            <?php endif; ?>
                                    
                                        </div>
                                    </div>
                                </li>
                        <?php endwhile;
                        wp_reset_postdata(); ?>
                    </ul>
            <?php else: ?>
                    <p>No courses are currently published. Check back soon!</p>
            <?php endif; ?>

        </div>
    </section>
</main>
<?php
get_footer();