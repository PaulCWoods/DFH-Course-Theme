<?php
get_header();

// Fetch the roots selected in ACF (safe when ACF is not active)
$root_lessons = function_exists('get_field') ? get_field('course_root_lessons') : get_post_meta(get_the_ID(), 'course_root_lessons', true);
if (!is_array($root_lessons)) {
    $root_lessons = empty($root_lessons) ? array() : array($root_lessons);
}
$root_lesson_ids = array();
foreach ($root_lessons as $root_lesson) {
    $root_lesson_id = is_object($root_lesson) ? (int) $root_lesson->ID : (int) $root_lesson;
    if ($root_lesson_id) {
        $root_lesson_ids[] = $root_lesson_id;
    }
}
$root_lesson_ids = array_values(array_unique($root_lesson_ids));

get_template_part('content', 'course-header');
?>

<main class="course-landing site-main" id="main">
    <article class="article course-landing__article">
        <figure class="course-landing__poster">
            <?php if (has_post_thumbnail())
                the_post_thumbnail(); ?>
        </figure>
        <header class="article-header course-landing__header prose">

            <div class="container +2/3 +start">
                <h1><?php the_title(); ?></h1>
            </div>
        </header>
        <div class="course-landing__intro prose">
            <div class="container +2/3 +start">
                <?php the_excerpt(); ?>
            </div>
        </div>

        <?php
        $user_id = get_current_user_id();
        $course_id = get_the_ID();
        $course_closed = function_exists('get_field')
            ? get_field('course_closed', $course_id)
            : get_post_meta($course_id, 'course_closed', true);
        $course_closed = in_array($course_closed, array(true, 1, '1', 'true'), true);

        // Get linked WooCommerce product first so we can check pending orders
        $woo_product_id = get_post_meta($course_id, '_dfh_product_id', true);
        $product = $woo_product_id ? wc_get_product($woo_product_id) : false;

        // Check if user has course access (requires access function and product linkage)
        $has_access = false; 
        if (function_exists('dfh_user_has_course_access')) {
            $has_access = dfh_user_has_course_access($user_id, $course_id);
        }

        // Check for pending/processing orders so users don't see a buy button while payment clears
        $has_pending_order = false;
        if ($user_id && !empty($woo_product_id) && !$has_access) {
            $pending_orders = wc_get_orders(array(
                'customer_id' => $user_id,
                'status'      => array('processing', 'on-hold'),
                'limit'       => 1,
                'product_id'  => $woo_product_id,
            ));
            if (!empty($pending_orders)) {
                $has_pending_order = true;
                // Self-heal: auto-grant enrollment meta so they gain access seamlessly
                update_user_meta($user_id, 'dfh_course_enrolled', '1');
                $has_access = true;
            }
        }

        // Gather lesson tree data...
        $all_lessons = array();
        foreach ($root_lessons as $root_lesson) {
            $current_root_id = is_object($root_lesson) ? (int) $root_lesson->ID : (int) $root_lesson;
            if ($current_root_id) {
                $all_lessons[] = $current_root_id;
                $all_lessons = array_merge($all_lessons, dfh_get_ordered_lesson_tree($current_root_id));
            }
        }
        $all_lessons = array_values(array_unique(array_map('intval', $all_lessons)));
        $total_lessons = count($all_lessons);
        $completed_lessons = dfh_get_completed_lessons($user_id);
        $completed_lessons = array_map('intval', $completed_lessons);
        $completed_count = count(array_intersect($completed_lessons, $all_lessons));

        $active_lesson_status = false;
        if (is_user_logged_in() && $has_access) {
            foreach ($all_lessons as $lesson_id) {
                if (!in_array($lesson_id, $completed_lessons, true)) {
                    $active_lesson_status = $lesson_id;
                    break;
                }
            }
            if (!$active_lesson_status && $total_lessons > 0) {
                $active_lesson_status = 'completed';
            }
        }

        $progress_percent = ($total_lessons > 0) ? round(($completed_count / $total_lessons) * 100) : 0;
        $current_user = wp_get_current_user();
        $user_name = '';
        if ($current_user && $current_user->ID) {
            $user_name = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
        }
        $welcome_msg = '<span id="course-landing__access-welcome" class="small-heading tc-muted">Welcome, ' . esc_html($user_name) . '!</span>';
        $welcome_back_msg = '<span id="course-landing__access-welcome" class="small-heading tc-muted">Welcome back, ' . esc_html($user_name) . '</span>';
        ?>

        <section class="course-landing__access prose" aria-describedby="course-landing__access-heading">
            <div class="container +2/3 +start">
                <?php if ($course_closed): ?>
                    <h2>Coming soon</h2>
                    <p class="small-text tc-muted">This course is not available yet. Check back soon.</p>

                <?php elseif ($has_pending_order && !$has_access): ?>
                    <!-- State 0D: Order Processing / Payment Clearing -->
                    <?php echo $welcome_msg; ?>
                    <h2>Payment received!</h2>
                    <p class="small-text tc-muted">Your payment is processing. Your course access will unlock automatically in a moment.</p>
                    <p style="margin-top: 1.5rem;">
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="button +strong">Refresh Page</a>
                    </p>

                <?php elseif (!is_user_logged_in()): ?>
                    <!-- State 0: Logged-out Visitor -->
                    <h2>Ready to start learning?</h2>
                    <p class="small-text tc-muted">
                        <?php if ($product): ?>
                            Log in or create an account to enroll (<?php echo $product->get_price_html(); ?>).
                        <?php else: ?>
                            Log in or register to access the course syllabus.
                        <?php endif; ?>
                    </p>
                    <div class="course-purchase-actions" style="margin-top: 1.5rem;">
                        <a href="<?php echo esc_url(add_query_arg('redirect_to', get_permalink(), home_url('/login/'))); ?>" class="button +strong">
                            Log In to Enroll
                        </a>
                    </div>

                <?php elseif (!$has_access && $product): ?>
                    <!-- State 0C: Logged-in User without purchase -->
                    <?php echo $welcome_msg; ?>
                    <h2>Unlock Full Course Access</h2>
                    <p class="small-text tc-muted">Purchase the course to unlock all lessons and track your progress.</p>
                    <div class="course-purchase-actions" style="margin-top: 1.5rem;">
                        <form action="<?php echo esc_url(wc_get_checkout_url()); ?>" method="post" class="cart">
                            <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($woo_product_id); ?>" />
                            <button type="submit" class="button +strong buy-button">
                                <span>Buy Course — <?php echo $product->get_price_html(); ?></span>
                            </button>
                        </form>
                    </div>

                <?php elseif ('completed' === $active_lesson_status): ?>
                    <!-- State 3: Course Completed -->
                    <div class="course-landing__completion">
                        <h2>Course completed!</h2>
                        <p>Congratulations! You have finished all lessons in this course.</p>
                        <div class="course-landing__completion-buttons">
                            <a href="<?php echo esc_url(get_permalink($all_lessons[0])); ?>"
                                class="button secondary-button">Review from Beginning</a>
                            <!-- Certificate Download Link -->
                            <a href="<?php echo esc_url(add_query_arg(array('action' => 'download_certificate', 'course_id' => $course_id, 'nonce' => wp_create_nonce('dfh_cert_' . $course_id)), home_url('/'))); ?>"
                                class="button +strong cert-btn" target="_blank">
                                Download Certificate (PDF)
                                <svg class="icon dir" width="32" height="32" aria-hidden="true">
                                    <use href="#Download" />
                                </svg>
                            </a>
                        </div>
                    </div>

                <?php elseif ($completed_count > 0): ?>
                    <!-- State 2: In-Progress (Resume) -->
                    <?php echo $welcome_back_msg; ?>
                    <h2>Your progress: <?php echo esc_html($progress_percent); ?>% Complete</h2>
                    <div class="progress-bar-container course-landing__progress">
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
                    <!-- State 1: Brand New (Not Started / Has Access) -->
                    <?php echo $welcome_msg; ?>
                    <h2>Ready to Begin?</h2>
                    <p class="small-text tc-muted">Jump straight into the first lesson of the course.</p>
                    <?php if (!empty($all_lessons)):
                        $first_lesson_url = get_permalink($all_lessons[0]);
                        ?>
                        <a href="<?php echo esc_url($first_lesson_url); ?>" class="button +strong start-btn">
                            Start Course
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
    </article>
    <section class="course-landing__section course-landing__about" aria-describedby="course-about-heading">
        <article class="container">
            <h2 id="course-about-heading" class="heading">About this course</h2>
            <div class="prose">
                <?php the_content(); ?>
            </div>
            <?php
            $sample_id = get_post_meta($course_id, '_dfh_linked_sample_id', true);

            if ($sample_id && !$has_access):
                $sample_url = get_permalink($sample_id);
                $sample_title = get_the_title($sample_id);
                ?>
                <div class="course-sample-preview" style="margin-top: 1.5rem;">
                    <a href="<?php echo esc_url($sample_url); ?>" class="button secondary-button">
                        <svg class="icon dir" width="32" height="32" aria-hidden="true">
                            <use href="#Search" />
                        </svg>
                        Read a Free Sample
                    </a>
                </div>
            <?php endif; ?>
        </article>
    </section>
    <section class="course-landing__section course-landing__syllabus" aria-describedby="course-plan-heading">
        <div class="container">
            <h2 id="course-plan-heading" class="heading">Course plan</h2>
            <p class="small-text course-landing__note">Note: Access to lessons will be granted as you progress through
                the course.</p>
            <?php if ($root_lessons): ?>
                <div class="lesson-list__container">
                    <?php echo dfh_render_lesson_tree($root_lesson_ids, 1, $active_lesson_status); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>