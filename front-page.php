<?php


get_header();
?>
<main class="home site-main" id="main">
    <article class="prose container">
        <header class="article-header">
            <h1 class="display-title">Design for Humans: <span class="home__title-highlight">Courses</span></h1>
        </header>
        <?php the_content(); ?>
    </article>


    <!-- Hero Section -->
    <section class="home__hero">
        <div class="container">
            <?php
            // Determine the target URL for the hero button: prefer the user's active course when available.
            $hero_target = home_url('/course/');
            $has_resume_course = false;
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
                        $has_resume_course = !empty($hero_target);
                    }
                }
            }

            if (is_user_logged_in()): ?>
                <p class="home__logged-in title">Welcome back, <?php echo esc_html($user_name); ?></p>
                <div class="home__access">
                    <?php if ($has_resume_course): ?>
                        <a href="<?php echo esc_url($hero_target); ?>" class="button strong">
                            Resume Your Course
                            <svg class="icon dir" width="32" height="32" aria-hidden="true">
                                <use href="#ArrowRight" />
                            </svg>
                        </a>
                    <?php endif; ?>
                    <a class="button" href="<?php echo esc_url(wp_logout_url(home_url())); ?>">Log Out</a>
                </div>
            <?php else: ?>

                <div class="home__access">
                    <a href="<?php echo esc_url(home_url('/register/')); ?>" class="button strong">Get Started</a>
                    <a href="<?php echo esc_url(home_url('/login/')); ?>" class="button">Log In</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Courses Grid Section -->
    <section class="home__section">
        <div class="container">
            <h2 class="title">Available courses</h2>

            <?php
            // 1. Fetch all courses once with optimized WP_Query parameters
            $courses_query = new WP_Query(array(
                'post_type' => 'course',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'menu_order title',
                'order' => 'ASC',
                // Performance boosters: disable unneeded SQL calculations if pagination isn't used
                'no_found_rows' => true,
                'update_post_meta_cache' => true, // Keep true only if you need thumbnails
                'update_post_term_meta_cache' => false,
            ));

            // 2. Pre-fetch accessible course IDs ONCE for the logged-in user (avoids queries inside the loop)
            $user_accessible_courses = array();
            if (is_user_logged_in() && function_exists('dfh_get_user_accessible_courses')) {
                $user_accessible_courses = dfh_get_user_accessible_courses(get_current_user_id());
            }
            $user_completed_courses = is_user_logged_in()
                ? get_user_meta(get_current_user_id(), 'dfh_course_completed_at', true)
                : array();
            if (!is_array($user_completed_courses)) {
                $user_completed_courses = array();
            }

            if ($courses_query->have_posts()):
                ?>
                <ul class="course-card-list index-card-list">
                    <?php while ($courses_query->have_posts()):
                        $courses_query->the_post(); ?>
                        <li class="index-card-list__item">
                            <div class="index-card course-card">
                                <?php if (has_post_thumbnail()): ?>
                                    <div class="index-card__thumb">
                                        <?php the_post_thumbnail('medium_large'); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="index-card__content">
                                    <a class="index-card__link link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    <?php the_excerpt(); ?>
                                    <?php
                                    $course_id = get_the_ID();
                                    $is_course_completed = is_array($user_completed_courses) && !empty($user_completed_courses[$course_id]);
                                    $has_access = in_array($course_id, $user_accessible_courses, true);
                                    if ($is_course_completed): ?>
                                        <span class="course-card__badge completed badge +success">Completed</span>
                                    <?php elseif ($has_access): ?>
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
<script>
    document.addEventListener('click', function (e) {
        const card = e.target.closest('.index-card');
        if (card) {
            card.classList.add('is-loading');
            card.setAttribute('aria-busy', 'true');
        }
    });
</script>
<?php
get_footer();