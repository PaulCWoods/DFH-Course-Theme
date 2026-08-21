<?php
get_header();

// Fetch the roots selected in ACF (safe when ACF is not active)
$root_lessons = function_exists('get_field') ? get_field('course_root_lessons') : get_post_meta(get_the_ID(), 'course_root_lessons', true);
if (!$root_lessons) {
    $root_lessons = array();
}

?>

<header class="site-header">
    <div class="container site-header-inner">
        <nav class="site-navigation">
            <ul class="site-breadcrumb container">
                <li>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="link site-title">
                        <svg class="icon dir" width="32" height="32" aria-hidden="true">
                            <use href="#Home" />
                        </svg>
                        Courses Home
                    </a>
                </li>
                <?php if (is_user_logged_in()): ?>
                    <li>
                        <a class="link" href="<?php echo esc_url(wp_logout_url(home_url())); ?>">Log Out</a>
                    </li>
                <?php else: ?>
                    <li>
                        <!-- Replace 'login' with the slug or URL of your new login page -->
                        <a class="link" href="<?php echo esc_url(home_url('/login/')); ?>" class="login-link">Log In</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main class="course-landing site-main">
    <article class="article course-about prose">
        <figure class="course-about-poster">
<?php if (has_post_thumbnail())
                the_post_thumbnail(); ?>
        </figure>
        <header class="article-header course-about-header">
            
            <div class="container">
                <h1><?php the_title(); ?></h1>
            </div>
        </header>
        <div class="course-about-main">
            <div class="container">
                <?php the_content(); ?>
            </div>
        </div>
    </article>

    <?php
    $user_id = get_current_user_id();
    $active_lesson_status = dfh_get_student_current_lesson($user_id);
    $all_lessons = dfh_get_ordered_lesson_tree(0);
    $total_lessons = count($all_lessons);
    $completed_lessons = dfh_get_completed_lessons($user_id);
    $completed_count = count(array_intersect($completed_lessons, $all_lessons));

    // Calculate progress percentage for a subtle progress bar
    $progress_percent = ($total_lessons > 0) ? round(($completed_count / $total_lessons) * 100) : 0;
    $current_user = wp_get_current_user();
    $user_name = '';
    if ($current_user && $current_user->ID) {
        $user_name = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
    }
    $welcome_msg = '<span id="course-access-welcome" class="small-heading tc-muted">Welcome, ' . esc_html($user_name) . '!</span>';
    $welcome_back_msg = '<span id="course-access-welcome" class="small-heading tc-muted">Welcome back, ' . esc_html($user_name) . '</span>';
    ?>

    <section class="course-access prose" aria-describedby="course-access-heading">
        <div class="container">
            <?php if (!is_user_logged_in()): ?>
                <!-- State 0: Guest Visitor -->
                <?php $welcome_back_msg; ?>
                <h2>Ready to start learning?</h2>
                <p class="small-text tc-muted">Log in or enroll to access the course syllabus and track your progress.</p>
                <a href="<?php echo esc_url(home_url('/login/')); ?>" class="button">Log in to access
                    course</a>

            <?php elseif ('completed' === $active_lesson_status): ?>
                <!-- State 3: Course Completed -->
                <div class="course-complete-badge">
                    <h2>🎉 Course completed!</h2>
                    <p>Congratulations! You have finished all lessons in this course.</p>
                    <a href="<?php echo esc_url(get_permalink($all_lessons[0])); ?>" class="button secondary-button">Review
                        from beginning</a>
                </div>

            <?php elseif ($completed_count > 0): ?>
                <!-- State 2: In-Progress (Resume) -->
                <?php echo $welcome_back_msg; ?>
                <h2>Your progress: <?php echo esc_html($progress_percent); ?>% Complete</h2>
                <div class="progress-bar-container course-access-progress">
                    <progress class="progress-bar" max="100"
                        value="<?php echo esc_html($progress_percent); ?>"><?php echo esc_html($progress_percent); ?>%</progress>
                </div>

                <?php
                $resume_title = get_the_title($active_lesson_status);
                $resume_url = get_permalink($active_lesson_status);
                ?>
                <p class="small-text tc-muted">Pick up where you left off:</p>
                <a href="<?php echo esc_url($resume_url); ?>" class="button resume-btn">
                    Resume: <?php echo esc_html($resume_title); ?>
                    <svg class="icon dir" width="32" height="32" aria-hidden="true">
                        <use href="#ArrowRight" />
                    </svg>
                </a>

            <?php else: ?>
                <!-- State 1: Brand New (Not Started) -->
                <?php echo $welcome_msg; ?>
                <h2>Ready to Begin?</h2>
                <p class="small-text tc-muted">Jump straight into the first lesson of the course.</p>
                <?php if (!empty($all_lessons)):
                    $first_lesson_url = get_permalink($all_lessons[0]);
                    ?>
                    <a href="<?php echo esc_url($first_lesson_url); ?>" class="button strong start-btn">
                        Start course
                        <svg class="icon dir" width="32" height="32" aria-hidden="true">
                            <use href="#ArrowRight" />
                        </svg>
                    </a>
                <?php else: ?>
                    <p>Course syllabus is currently being built. <em>Check back soon!</em></p>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </section>
    <section class="course-syllabus" aria-describedby="course-permissions-heading">
        <div class="container">
            <h2 id="course-permissions-heading" class="heading">Course Plan</h2>
            <?php if ($root_lessons): ?>
                <div class="lesson-list-container">
                    <?php echo dfh_render_lesson_tree($root_lessons, 1); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>