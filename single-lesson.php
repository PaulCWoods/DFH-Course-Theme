<?php
$lesson_id = get_the_ID();
$user_id = get_current_user_id();

// 1. Gate check: Verify course access first
if (!dfh_user_has_course_access()) {
    get_header();
    get_template_part('content', 'course-header');
    ?>
    <main class="main" id="main">
        <article class="article prose font-sizing-fluid wrapper">
            <h1>Student-only content</h1>
            <p>You need to be enrolled in the course to view this lesson.</p>
            <?php if (!is_user_logged_in()): ?>
                <p><a href="<?php echo esc_url(home_url('/login/')); ?>" class="button button--strong button--accent"><span>Log
                            in</span></a>
                </p>
            <?php endif; ?>
        </article>
    </main>
    <?php
    get_footer();
    exit;
}

// 2. Progression Gate Check: Prevent URL manipulation skipping ahead
// Administrators bypass this check so you can freely test or review any page.
if (!user_can($user_id, 'administrator') && !dfh_lessons_open()) {
    $is_completed = dfh_is_lesson_completed($lesson_id, $user_id);
    $current_allowed_lesson = dfh_get_student_current_lesson($user_id);

    // If the lesson is NOT completed AND is not their current active lesson, block access
    // (If all lessons are complete, $current_allowed_lesson returns 'completed', meaning future icon--direct URLs are locked)
    if (!$is_completed && (int) $current_allowed_lesson !== (int) $lesson_id) {
        // Find where they are supposed to be (fallback to course archive or active lesson)
        $redirect_url = home_url();
        if (is_numeric($current_allowed_lesson)) {
            $redirect_url = get_permalink($current_allowed_lesson);
        } else {
            // If course is fully completed, send them back to the first course page or home
            $courses = dfh_get_courses_for_lesson($lesson_id);
            if (!empty($courses)) {
                $redirect_url = get_permalink($courses[0]);
            }
        }

        wp_redirect($redirect_url);
        exit;
    }
}

// The lesson, its course and where it sits in it
$adjacent = dfh_get_adjacent_lesson($lesson_id);
$is_completed = dfh_is_lesson_completed($lesson_id);
$is_bookmarked = dfh_is_lesson_bookmarked($lesson_id);
$playback_id = get_post_meta($lesson_id, 'mux_playback_id', true);
$lesson_code = dfh_get_lesson_hierarchy_number();
$courses = dfh_get_courses_for_lesson($lesson_id);
$course_id = $courses ? (int) $courses[0] : null;
$course_lessons = $course_id ? dfh_get_course_lesson_ids($course_id) : dfh_get_ordered_lesson_tree(0);

// The breadcrumb goes up a level: the parent lesson, else the course
$parent_id = wp_get_post_parent_id($lesson_id);
if ($parent_id) {
    $up_url = get_permalink($parent_id);
    $up_title = get_the_title($parent_id);
} elseif ($course_id) {
    $up_url = get_permalink($course_id);
    $up_title = get_the_title($course_id);
} else {
    $up_url = home_url();
    $up_title = 'Courses home';
}

// The main action: complete this lesson and go on, or finish the course
$is_last_lesson = $course_lessons && (int) end($course_lessons) === (int) $lesson_id;
if ($is_completed && $is_last_lesson) {
    $progress_label = 'Course home';
    $progress_icon = '#Home';
} elseif ($is_last_lesson) {
    $progress_label = 'Complete course';
    $progress_icon = '#Check';
} else {
    $progress_label = 'Next lesson';
    $progress_icon = '#ArrowRight';
}

// Progress through the course, for the panel
$completed_lessons = array_map('intval', dfh_get_completed_lessons());
$progress_total = count($course_lessons);
$progress_count = count(array_intersect($completed_lessons, $course_lessons));
$progress_percent = $progress_total ? round($progress_count / $progress_total * 100) : 0;

/**
 * Template Name: Single Lesson
 * Template Post Type: lesson
 *
 * The template for displaying all single lesson posts.
 */

get_header();
?>

<?php get_template_part('content', 'lesson-head', array(
    'code' => $lesson_code,
    'up_url' => $up_url,
    'up_title' => $up_title,
    'playback_id' => $playback_id,
    'progress' => true,
)); ?>
<main class="main main--flush" id="main">
    <article id="post-<?php the_ID(); ?>" <?php post_class('stack gap-0 min-h-full'); ?>>
        <header class="lesson-header prose font-sizing-fluid bg-darker color-scheme-dark">
            <div class="wrapper">
                <h1 class="lesson-header__title"><?php the_title(); ?></h1>
                <?php
                $lesson_subtitle = function_exists('get_field') ? get_field('subtitle') : get_post_meta($lesson_id, 'subtitle', true);
                if ($lesson_subtitle): ?>
                    <p class="small-heading"><?php echo esc_html($lesson_subtitle); ?></p>
                <?php endif; ?>
                <?php if ($is_completed): ?>
                    <p class="status status--plain status--success"><svg class="icon" width="1em" height="1em"
                            aria-hidden="true" focusable="false">
                            <use href="#Check" />
                        </svg><span class="status__text">Complete</span></p>
                <?php endif; ?>
            </div>
        </header>
        <?php get_template_part('content', 'lesson-video', array('playback_id' => $playback_id)); ?>
        <div class="lesson-content">
            <div class="wrapper article__main">
                <div class="lesson-body article__body prose font-sizing-fluid"><?php the_content(); ?></div>
                <aside class="lesson-aside article__aside stack gap-cushion-lg rule" aria-label="Lesson resources">
                    <div class="lesson-bookmark print:hide">
                        <?php // The label says the state ("Bookmarked"), so this isn't a pressed toggle ?>
                        <button type="button" id="dfh-bookmark-btn"
                            class="button<?php echo $is_bookmarked ? ' button--strong' : ''; ?>"
                            data-lesson-id="<?php echo esc_attr($lesson_id); ?>"
                            data-nonce="<?php echo esc_attr(wp_create_nonce('dfh_bookmark_nonce')); ?>">
                            <span
                                class="dfh-bookmark-label"><?php echo $is_bookmarked ? 'Bookmarked' : 'Bookmark'; ?></span>
                            <svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false">
                                <use href="<?php echo $is_bookmarked ? '#BookmarkFilled' : '#Bookmark'; ?>" />
                            </svg>
                            <svg class="icon spinner" width="1em" height="1em" aria-hidden="true" focusable="false">
                                <use href="#LoaderArc" />
                            </svg>
                        </button>
                    </div>
                    <?php get_template_part('content', 'lesson-resources', array('lesson_id' => $lesson_id)); ?>
                </aside>
            </div>
        </div>
        <div class="lesson-explore stack print:hide">
            <?php
            $children_html = dfh_render_lesson_children($lesson_id);
            if ($children_html): ?>
                <section class="wrapper stack" aria-labelledby="lesson-children-heading">
                    <h2 class="heading" id="lesson-children-heading">Lessons in this section</h2>
                    <nav class="navigation-tree navigation-tree--ruled navigation-tree--compact max-w-md"
                        aria-labelledby="lesson-children-heading">
                        <?php echo $children_html; ?>
                    </nav>
                </section>
            <?php endif; ?>
            <div class="wrapper mt-auto">
                <div class="cluster cluster--end">
                    <?php if ($adjacent['previous']): ?>
                        <a href="<?php echo esc_url(get_permalink($adjacent['previous'])); ?>"
                            class="button button--subtle me-auto">
                            <svg class="icon icon--dir" width="1em" height="1em" aria-hidden="true" focusable="false">
                                <use href="#ArrowLeft" />
                            </svg>
                            <span>Previous lesson</span>
                        </a>
                    <?php endif; ?>
                    <p class="small-text" role="status" id="dfh-complete-status"></p>
                    <button type="button" id="dfh-complete-btn" class="button button--strong button--accent"
                        data-lesson-id="<?php echo esc_attr($lesson_id); ?>"
                        data-nonce="<?php echo esc_attr(wp_create_nonce('dfh_progress_nonce')); ?>">
                        <span><?php echo esc_html($progress_label); ?></span>
                        <svg class="icon icon--dir" width="1em" height="1em" aria-hidden="true" focusable="false">
                            <use href="<?php echo esc_attr($progress_icon); ?>" />
                        </svg>
                        <svg class="icon spinner" width="1em" height="1em" aria-hidden="true" focusable="false">
                            <use href="#LoaderArc" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </article>
    <dialog id="course-progress" class="dialog dialog--drawer" aria-labelledby="course-progress-title" closedby="any">
        <article class="card">
            <header class="card__header">
                <h2 class="card__title heading" id="course-progress-title">
                    <?php if ($course_id): ?>
                        <a class="link link--plain"
                            href="<?php echo esc_url(get_permalink($course_id)); ?>"><?php echo esc_html(get_the_title($course_id)); ?></a>
                    <?php else: ?>
                        Course
                    <?php endif; ?>
                </h2>
                <button type="button" class="button button--plain button--icon" commandfor="course-progress"
                    command="close">
                    <span class="sr-only">Close</span>
                    <svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false">
                        <use href="#Close" />
                    </svg>
                </button>
            </header>
            <div class="card__body card__body--scrollable stack" tabindex="0" aria-labelledby="course-progress-title">
                <div class="field">
                    <div class="field__header">
                        <label for="course-progress-bar" class="field__label label">Your progress</label>
                        <span class="small-text"><?php echo esc_html($progress_percent); ?>% complete</span>
                    </div>
                    <progress id="course-progress-bar" class="progress progress--sm" max="100"
                        value="<?php echo esc_attr($progress_percent); ?>"><?php echo esc_html($progress_percent); ?>%</progress>
                </div>
                <nav class="navigation-tree navigation-tree--compact" aria-label="Lessons">
                    <?php echo dfh_render_lesson_tree($course_id ? dfh_get_course_root_ids($course_id) : null); ?>
                </nav>
            </div>
            <footer class="card__footer">
                <a href="<?php echo esc_url(home_url()); ?>" class="brand">
                    <svg class="brand__mark icon" width="1em" height="1em" aria-hidden="true" focusable="false">
                        <use href="#BrandDesignForHumans"></use>
                    </svg>
                    <span class="brand__name">Courses</span>
                </a>
            </footer>
        </article>
    </dialog>
    <div class="toast-stack" popover="manual" role="status" aria-label="Notifications" id="dfh-toasts"></div>
</main>
<?php if ($playback_id): ?>
    <script src="https://cdn.jsdelivr.net/npm/@mux/mux-player"></script>
<?php endif; ?>
<script>
    (() => {
        const ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
        const toasts = document.getElementById('dfh-toasts');
        toasts.showPopover();

        // A Toast: success ones go after 6 seconds unless hovered or focused;
        // errors stay until dismissed (WCAG 2.2.1)
        const toast = (title, intent = 'success') => {
            const item = document.createElement('div');
            item.className = `banner banner--${intent} banner--round toast`;
            item.innerHTML = `<span class="icon-box" aria-hidden="true"><svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#${intent === 'danger' ? 'Error' : 'Check'}"/></svg></span>
                <div class="banner__body"><p class="text font-bold"></p></div>
                <button type="button" class="button button--icon banner__action"><svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#Close"/></svg><span class="sr-only">Dismiss</span></button>`;
            item.querySelector('p').textContent = title;
            item.querySelector('button').addEventListener('click', () => item.remove());
            toasts.append(item);
            if (intent === 'danger') return;
            const close = () => { if (!item.matches(':hover, :focus-within')) item.remove(); else setTimeout(close, 1000); };
            setTimeout(close, 6000);
        };

        const post = (data) => fetch(ajaxUrl, { method: 'POST', body: new URLSearchParams(data) }).then((r) => r.json());

        // Busy: keep the Button's focus and name, block a second press
        const busy = (button, on) => {
            button.classList.toggle('is-busy', on);
            on ? button.setAttribute('aria-disabled', 'true') : button.removeAttribute('aria-disabled');
        };

        const complete = document.getElementById('dfh-complete-btn');
        const completeStatus = document.getElementById('dfh-complete-status');
        complete?.addEventListener('click', () => {
            if (complete.classList.contains('is-busy')) return;
            busy(complete, true);
            completeStatus.textContent = 'Saving your progress…';
            post({ action: 'dfh_mark_complete', lesson_id: complete.dataset.lessonId, nonce: complete.dataset.nonce })
                .then((data) => {
                    if (!data.success || !data.data.next_url) throw new Error();
                    completeStatus.textContent = 'Saved. Opening the next page…';
                    window.location.href = data.data.next_url;
                })
                .catch(() => {
                    busy(complete, false);
                    completeStatus.textContent = 'Your progress wasn’t saved. Check your connection and try again.';
                });
        });

        const bookmark = document.getElementById('dfh-bookmark-btn');
        bookmark?.addEventListener('click', () => {
            if (bookmark.classList.contains('is-busy')) return;
            busy(bookmark, true);
            post({ action: 'dfh_toggle_bookmark', lesson_id: bookmark.dataset.lessonId, nonce: bookmark.dataset.nonce })
                .then((data) => {
                    if (!data.success) throw new Error();
                    const on = Boolean(data.data.is_bookmarked);
                    bookmark.classList.toggle('button--strong', on);
                    bookmark.querySelector('.dfh-bookmark-label').textContent = on ? 'Bookmarked' : 'Bookmark';
                    bookmark.querySelector('.icon:not(.spinner) use').setAttribute('href', on ? '#BookmarkFilled' : '#Bookmark');
                    toast(on ? 'Lesson bookmarked' : 'Bookmark removed');
                })
                .catch(() => toast('The bookmark wasn’t saved. Try again.', 'danger'))
                .finally(() => busy(bookmark, false));
        });
    })();
</script>
<?php
get_footer();
