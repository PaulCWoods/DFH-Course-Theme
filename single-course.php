<?php
/**
 * A course: a Poster with the title, the summary and what the visitor can
 * do next (enrol, buy, start, resume, or the completion), then what the
 * course is about and its plan.
 */

$user_id = get_current_user_id();
$course_id = get_the_ID();
$root_lesson_ids = dfh_get_course_root_ids($course_id);

$course_closed = function_exists('get_field') ? get_field('course_closed', $course_id) : get_post_meta($course_id, 'course_closed', true);
$course_closed = in_array($course_closed, array(true, 1, '1', 'true'), true);

// The linked WooCommerce product, so a pending order can be found
$woo_product_id = get_post_meta($course_id, '_dfh_product_id', true);
$course_product = $woo_product_id ? wc_get_product($woo_product_id) : false;

$has_access = dfh_user_has_course_access($user_id, $course_id);

// A payment that is still clearing grants access, so nobody sees a buy
// Button for a course they've paid for
$has_pending_order = false;
if ($user_id && !empty($woo_product_id) && !$has_access) {
    $pending_orders = wc_get_orders(array(
        'customer_id' => $user_id,
        'status' => array('processing', 'on-hold'),
        'limit' => 1,
        'product_id' => $woo_product_id,
    ));
    if (!empty($pending_orders)) {
        $has_pending_order = true;
        update_user_meta($user_id, 'dfh_course_enrolled', '1');
        $has_access = true;
    }
}

// Progress through the course
$all_lessons = array_map('intval', dfh_get_course_lesson_ids($course_id));
$total_lessons = count($all_lessons);
$completed_lessons = array_map('intval', dfh_get_completed_lessons($user_id));
$completed_count = count(array_intersect($completed_lessons, $all_lessons));
$progress_percent = $total_lessons ? round($completed_count / $total_lessons * 100) : 0;

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

$current_user = wp_get_current_user();
$user_name = $current_user->ID ? ($current_user->display_name ?: $current_user->user_login) : '';
$arrow = '<svg class="icon icon--dir" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#ArrowRight" /></svg>';

get_header();
get_template_part('content', 'course-header');
?>

<main class="main main--flush" id="main">
    <article>
        <header class="poster poster--block-end color-palette-inverted" style="--poster-min-size: 50svh;">
            <?php if (has_post_thumbnail()) {
                the_post_thumbnail('full', array('class' => 'poster__media', 'alt' => '', 'sizes' => '100vw', 'fetchpriority' => 'high', 'loading' => false));
            } ?>
            <div class="poster__content bg-accent">
                <div class="stack">
                    <h1 class="title"><?php the_title(); ?></h1>
                    <div class="large-text font-bold text-pretty"><?php the_excerpt(); ?></div>
                </div>
            </div>
        </header>
        <section class="section<?php echo 'completed' === $active_lesson_status ? ' bg-accent' : ''; ?>"
            aria-labelledby="course-dynamic-info">
            <div class="wrapper stack">
                <?php if ($course_closed): ?>
                    <h2 id="course-dynamic-info" class="heading">Coming soon</h2>
                    <p class="text text-muted">This course isn't open yet. Check back soon.</p>

                <?php elseif ($has_pending_order && !$has_access): ?>
                    <p class="small-heading text-muted">Welcome,
                        <?php echo esc_html($user_name); ?>
                    </p>
                    <h2 id="course-dynamic-info" class="heading">Payment received</h2>
                    <p class="text text-muted">Your payment is going through. You'll have the course as soon as it
                        clears.</p>
                    <div class="button-group">
                        <a href="<?php echo esc_url(get_permalink()); ?>"
                            class="button button--strong button--accent">Refresh
                            the page</a>
                    </div>

                <?php elseif (!is_user_logged_in()): ?>
                    <h2 id="course-dynamic-info" class="heading">Ready to start learning?</h2>
                    <p class="text text-muted">
                        <?php if ($course_product): ?>
                            Create an account to enrol (
                            <?php echo wp_kses_post($course_product->get_price_html()); ?>).
                        <?php else: ?>
                            Create an account to see the whole course.
                        <?php endif; ?>
                    </p>
                    <div class="button-group">
                        <a href="<?php echo esc_url(add_query_arg('redirect_to', get_permalink(), home_url('/register/'))); ?>"
                            class="button button--strong button--accent">Register to enrol</a>
                    </div>
                    <p class="small-text text-muted">Already have an account? <a
                            href="<?php echo esc_url(add_query_arg('redirect_to', get_permalink(), home_url('/login/'))); ?>"
                            class="link font-bold">Log in</a> to enrol.</p>

                <?php elseif (!$has_access && $course_product): ?>
                    <p class="small-heading text-muted">Welcome,
                        <?php echo esc_html($user_name); ?>
                    </p>
                    <h2 id="course-dynamic-info" class="heading">Get the whole course</h2>
                    <p class="text text-muted">Buy the course to open every lesson and keep track of your progress.</p>
                    <form action="<?php echo esc_url(wc_get_checkout_url()); ?>" method="post" class="button-group">
                        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($woo_product_id); ?>" />
                        <button type="submit" class="button button--strong button--accent">Buy the course:
                            <?php echo wp_kses_post($course_product->get_price_html()); ?>
                        </button>
                    </form>

                <?php elseif ('completed' === $active_lesson_status): ?>
                    <p class="status status--plain status--success"><span class="status__text">Complete</span></p>
                    <h2 id="course-dynamic-info" class="heading">You've finished the course <span
                            aria-hidden="true">🎉</span></h2>
                    <p class="text">Congratulations: you've completed every lesson.</p>
                    <div class="button-group">
                        <a href="<?php echo esc_url(add_query_arg(array('action' => 'download_certificate', 'course_id' => $course_id, 'nonce' => wp_create_nonce('dfh_cert_' . $course_id)), home_url('/'))); ?>"
                            class="button button--strong button--accent">
                            <span>Download your certificate (PDF)</span>
                            <svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false">
                                <use href="#Download" />
                            </svg>
                        </a>
                        <?php if ($all_lessons): ?>
                            <a href="<?php echo esc_url(get_permalink($all_lessons[0])); ?>" class="button">Review from the
                                start</a>
                        <?php endif; ?>
                    </div>
                    <p class="small-text">What did you think of the course? <a class="link"
                            href="https://designforhumans.blog/contact?subject=Course%20feedback">Let me know</a>.</p>

                <?php elseif ($completed_count > 0): ?>
                    <p class="small-heading text-muted">Welcome back,
                        <?php echo esc_html($user_name); ?>
                    </p>
                    <h2 id="course-dynamic-info" class="heading" id="course-progress-heading">Your progress:
                        <?php echo esc_html($progress_percent); ?>% complete
                    </h2>
                    <progress class="progress" max="100" value="<?php echo esc_attr($progress_percent); ?>"
                        aria-labelledby="course-progress-heading">
                        <?php echo esc_html($progress_percent); ?>%
                    </progress>
                    <div class="button-group">
                        <a href="<?php echo esc_url(get_permalink($active_lesson_status)); ?>"
                            class="button button--strong button--accent">
                            <span>Carry on:
                                <?php echo esc_html(get_the_title($active_lesson_status)); ?>
                            </span>
                            <?php echo $arrow; ?>
                        </a>
                    </div>

                <?php else: ?>
                    <p class="small-heading text-muted">Welcome,
                        <?php echo esc_html($user_name); ?>
                    </p>
                    <h2 class="heading">Ready to begin?</h2>
                    <?php if ($all_lessons): ?>
                        <p class="text text-muted">Go straight to the first lesson.</p>
                        <div class="button-group">
                            <a href="<?php echo esc_url(get_permalink($all_lessons[0])); ?>"
                                class="button button--strong button--accent">
                                <span>Start the course</span>
                                <?php echo $arrow; ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <p class="text text-muted">The lessons are still being written. Check back soon.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" aria-labelledby="course-about-heading">
            <div class="wrapper prose font-sizing-fluid">
                <h2 id="course-about-heading" class="heading">About this course</h2>
                <?php the_content(); ?>
                <?php
                $sample_id = get_post_meta($course_id, '_dfh_linked_sample_id', true);
                if ($sample_id && !$has_access): ?>
                    <div class="button-group mt-cushion prose-end">
                        <a href="<?php echo esc_url(get_permalink($sample_id)); ?>" class="button">
                            <svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false">
                                <use href="#Search" />
                            </svg>
                            <span>Read a free sample</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" aria-labelledby="course-plan-heading">
            <div class="wrapper stack">
                <h2 id="course-plan-heading" class="heading">Course plan</h2>
                <p class="small-text text-muted">Each lesson opens as you finish the one before.</p>
                <?php if ($root_lesson_ids): ?>
                    <?php if ($has_access): ?>
                        <nav aria-labelledby="course-plan-heading">
                            <?php echo dfh_render_course_plan($root_lesson_ids, $active_lesson_status); ?>
                        </nav>
                    <?php else: ?>
                        <?php echo dfh_render_course_plan($root_lesson_ids, $active_lesson_status); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </article>
</main>

<?php
get_footer();
