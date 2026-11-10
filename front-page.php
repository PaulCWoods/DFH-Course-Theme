<?php
/**
 * The home page: the brand title and the page's content, a welcome with
 * what to do next, the courses and what students say.
 */

$user_id = get_current_user_id();

// The course to carry on with, once a student has finished a lesson
$resume_url = '';
if ($user_id && dfh_get_completed_lessons($user_id)) {
    $active_lesson = dfh_get_student_current_lesson();
    if (is_int($active_lesson) && $active_lesson > 0) {
        $lesson_courses = dfh_get_courses_for_lesson($active_lesson);
        if ($lesson_courses) {
            $resume_url = get_permalink((int) $lesson_courses[0]);
        }
    }
}

$user_name = '';
if ($user_id) {
    $current_user = wp_get_current_user();
    $user_name = $current_user->display_name ?: $current_user->user_login;
}
$just_registered = isset($_GET['registered']) && '1' === sanitize_text_field(wp_unslash($_GET['registered']));

$courses_query = new WP_Query(array(
    'post_type' => 'course',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'menu_order title',
    'order' => 'ASC',
    'no_found_rows' => true,
    'update_post_term_meta_cache' => false,
));
$completed_courses = $user_id ? get_user_meta($user_id, 'dfh_course_completed_at', true) : array();
if (!is_array($completed_courses)) {
    $completed_courses = array();
}

$testimonials = get_posts(array(
    'post_type' => 'testimonial',
    'posts_per_page' => 3,
    'orderby' => 'date',
    'order' => 'DESC',
));

get_header();
?>
<main class="main" id="main">
    <article class="prose font-sizing-fluid wrapper pb-cushion">
        <header class="article__header">
            <h1 class="display-title brand-frame">Design for Humans: <span class="text-accent">Courses</span></h1>
        </header>
        <?php the_content(); ?>
    </article>

    <section class="section" aria-labelledby="home-welcome-heading">
        <div class="wrapper stack">
            <?php if ($user_id): ?>
                <h2 id="home-welcome-heading" class="title"><?php echo $just_registered ? 'Welcome' : 'Welcome back'; ?>, <a
                        class="link"
                        href="<?php echo esc_url(home_url('/my-account/')); ?>"><?php echo esc_html($user_name); ?></a></h2>
                <div class="button-group">
                    <?php if ($resume_url): ?>
                        <a href="<?php echo esc_url($resume_url); ?>" class="button button--strong button--accent">
                            <span>Carry on with your course</span>
                            <svg class="icon icon--dir" width="1em" height="1em" aria-hidden="true" focusable="false">
                                <use href="#ArrowRight" />
                            </svg>
                        </a>
                    <?php endif; ?>
                    <a class="button" href="<?php echo esc_url(wp_logout_url(home_url())); ?>">Log out</a>
                </div>
            <?php else: ?>
                <h2 id="home-welcome-heading" class="title">Ready to learn?</h2>
                <div class="button-group">
                    <a href="<?php echo esc_url(home_url('/register/')); ?>"
                        class="button button--strong button--accent">Get started</a>
                    <a href="<?php echo esc_url(home_url('/login/')); ?>" class="button">Log in</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="section section--strong-1st" aria-labelledby="home-courses-heading">
        <div class="wrapper">
            <h2 id="home-courses-heading" class="section__title heading">Available courses</h2>
            <?php if ($courses_query->have_posts()): ?>
                <ul class="card-list grid grid--cushioned" role="list">
                    <?php while ($courses_query->have_posts()):
                        $courses_query->the_post();
                        $course_id = get_the_ID(); ?>
                        <li>
                            <article class="card index-card">
                                <?php if (has_post_thumbnail()): ?>
                                    <div class="card__thumb"><?php the_post_thumbnail('medium_large', array('alt' => '')); ?></div>
                                <?php endif; ?>
                                <div class="card__content">
                                    <header class="card__header">
                                        <h3 class="card__title heading"><a class="link link--plain link--stretched"
                                                href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                        <?php if (!empty($completed_courses[$course_id])): ?>
                                            <span class="status status--plain status--success"><span
                                                    class="status__text">Complete</span></span>
                                        <?php elseif ($user_id && dfh_user_has_course_access($user_id, $course_id)): ?>
                                            <span class="status status--plain status--accent"><span
                                                    class="status__text">Enrolled</span></span>
                                        <?php endif; ?>
                                    </header>
                                    <div class="card__body"><?php the_excerpt(); ?></div>
                                </div>
                            </article>
                        </li>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </ul>
            <?php else: ?>
                <p class="text">No courses are open yet. Check back soon.</p>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($testimonials): ?>
        <section class="section" aria-labelledby="home-testimonials-heading">
            <div class="wrapper container-inline">
                <h2 id="home-testimonials-heading" class="section__title heading">What students say</h2>
                <ul class="grid grid--3-col grid--xs grid--cushioned @max-3xl:grid--carousel" role="list">
                    <?php foreach ($testimonials as $testimonial):
                        $author = get_post_meta($testimonial->ID, '_dfh_testimonial_author_title', true);
                        $author_url = get_post_meta($testimonial->ID, '_dfh_testimonial_author_url', true); ?>
                        <li class="card card--strong">
                            <figure class="card__body stack">
                                <svg class="icon icon--lg icon--accent" width="1em" height="1em" aria-hidden="true"
                                    focusable="false">
                                    <use href="#Quote" />
                                </svg>
                                <blockquote class="large-text">
                                    <?php echo apply_filters('the_content', $testimonial->post_content); ?>
                                </blockquote>
                                <?php if ($author): ?>
                                    <figcaption class="small-text">
                                        <?php if ($author_url): ?>
                                            <a class="link link--plain"
                                                href="<?php echo esc_url($author_url); ?>"><?php echo esc_html($author); ?></a>
                                        <?php else: ?>
                                            <?php echo esc_html($author); ?>
                                        <?php endif; ?>
                                    </figcaption>
                                <?php endif; ?>
                            </figure>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php
get_footer();
