<?php

$lesson_id = get_the_ID();
$adjacent = dfh_get_adjacent_lesson($lesson_id);
$is_completed = dfh_is_lesson_completed($lesson_id);
$is_bookmarked = dfh_is_lesson_bookmarked($lesson_id);
$bookmark_text = $is_bookmarked ? 'Bookmarked' : 'Bookmark Lesson';
$bookmark_class = $is_bookmarked ? 'button bookmark-btn active strong' : 'button bookmark-btn';
$bookmark_icon = $is_bookmarked ? '#Bookmarked' : '#Bookmark';
// Gate check at the very top of single-lesson.php or single-course.php
if (!dfh_user_has_course_access()) {
    get_header();
    ?>
    <header class="site-header">
        <div class="container site-header-inner">
            <nav class="site-navigation">
                <ul class="site-breadcrumb container">
                    <li>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="link site-title">
                            <svg class="icon dir" width="32" height="32" aria-hidden="true"><use href="#Home" /></svg>
                            Courses Home
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="restricted-access site-main">
        <article class="article prose container">
            <h1>Student-only content</h1>
            <p>You need to be enrolled in the course to view this lesson.</p>
            <?php if (!is_user_logged_in()): ?>
                <p><a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="button">Log in</a></p>
            <?php endif; ?>
        </article>
    </main>
    <?php
    get_footer();
    exit; // Stop loading the rest of the page
}

/**
 * Template Name: Single Lesson
 * Template Post Type: lesson
 * 
 * The template for displaying all single lesson posts.
 */

get_header();
?>

<header class="lesson-head site-header" aria-label="Lesson navigation">
    <div class="lesson-head-inner container">
        <?php
        $lesson_code = dfh_get_lesson_hierarchy_number();
        if ($lesson_code):
            ?>
            <span class="lesson-code-badge" title="Lesson code"><span class="sr">Lesson
                    code:</span><?php echo esc_html($lesson_code); ?></span>
        <?php endif; ?>
        <nav class="lesson-nav">
            <?php
            $post_id = get_the_ID();
            $parent_id = wp_get_post_parent_id($post_id);

            // Breadcrumb: parent lesson when available; otherwise try to find parent Course
            if ($parent_id): ?>
                <a class="lesson-breadcrumb link" title="<?php echo esc_html(get_the_title($parent_id)); ?>"
                    href="<?php echo esc_url(get_permalink($parent_id)); ?>"><?php echo esc_html(get_the_title($parent_id)); ?></a>
            <?php else:
                // Attempt to find a Course that references this lesson as a root (ACF or postmeta)
                $course_id = null;
                $all_courses = get_posts(array('post_type' => 'course', 'posts_per_page' => -1, 'fields' => 'ids'));
                if (!empty($all_courses)) {
                    foreach ($all_courses as $c_id) {
                        $roots = function_exists('get_field') ? get_field('course_root_lessons', $c_id) : get_post_meta($c_id, 'course_root_lessons', true);
                        if (is_string($roots)) {
                            $maybe = @unserialize($roots);
                            if ($maybe !== false)
                                $roots = $maybe;
                        }
                        if (!empty($roots) && in_array($post_id, (array) $roots)) {
                            $course_id = $c_id;
                            break;
                        }
                    }
                }

                if ($course_id): ?>
                    <a class="lesson-breadcrumb link" title="<?php echo esc_html(get_the_title($course_id)); ?>"
                        href="<?php echo esc_url(get_permalink($course_id)); ?>"><?php echo esc_html(get_the_title($course_id)); ?></a>
                <?php else: ?>
                    <a class="lesson-breadcrumb link" href="<?php echo esc_url(home_url()); ?>">Home</a>
                <?php endif;
            endif; ?>

            <!-- Syllabus overlay toggle -->

        </nav>
        <button class="progress-toggle lesson-progress-toggle button subtle" command="toggle-popover"
            commandfor="lesson-progress">
            Progress
            <svg class="icon dir" width="32" height="32" aria-hidden="true"><use href="#Navigation" /></svg>
        </button>
    </div>

</header>
<main id="primary" class="site-main lesson-page">
    <!-- Syllabus overlay panel (hidden by default) -->
    <aside id="lesson-progress" class="lesson-progress progress-panel" popover>
        <?php
        // Determine a course to display in the panel: prefer any already-found $course_id,
        // otherwise check the current lesson and its ancestors for a course that lists them as roots.
        $panel_course_id = isset($course_id) && $course_id ? $course_id : null;
        if (empty($panel_course_id)) {
            $ancestors = get_post_ancestors(get_the_ID());
            array_unshift($ancestors, get_the_ID());

            $all_courses = get_posts(array('post_type' => 'course', 'posts_per_page' => -1, 'fields' => 'ids'));
            if (!empty($all_courses)) {
                foreach ($all_courses as $c_id) {
                    $roots = function_exists('get_field') ? get_field('course_root_lessons', $c_id) : get_post_meta($c_id, 'course_root_lessons', true);
                    if (is_string($roots)) {
                        $maybe = @unserialize($roots);
                        if ($maybe !== false)
                            $roots = $maybe;
                    }
                    if (!empty($roots)) {
                        foreach ($ancestors as $anc) {
                            if (in_array($anc, (array) $roots)) {
                                $panel_course_id = $c_id;
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        ?>
        <header class="progress-panel-header">
            <a class="link-button syllabus-course" href="<?php echo esc_url(home_url()); ?>" title="Home">
                <svg class="icon dir" width="32" height="32" aria-hidden="true"><use href="#Home" /></svg>
                Courses Home
            </a>

            <button class="syllabus-close button subtle" command="hide-popover" commandfor="lesson-progress" title="Close navigation">
                <svg class="icon dir" width="32" height="32" aria-hidden="true"><use href="#Close" /></svg>
                <span class="sr">Close navigation</span>
            </button>
        </header>
        <nav class="progress-panel-course" aria-label="Course syllabus">

            <?php
            // Show the current Course title and link when available
            $display_course_id = !empty($panel_course_id) ? $panel_course_id : (!empty($course_id) ? $course_id : null);
            if ($display_course_id): ?>
                <h2 class="heading progress-panel-course-name"><a class="link"
                        href="<?php echo esc_url(get_permalink($display_course_id)); ?>"><?php echo esc_html(get_the_title($display_course_id)); ?></a>
                </h2>
            <?php else: ?>
                <h2 class="heading progress-panel-course-name">Course</h2>
            <?php endif; ?>
            <div class="lesson-list-container">
                <?php
                // If we have a course, get its roots; otherwise leave null to render top-level lessons
                $roots = null;
                if (!empty($course_id)) {
                    $roots = function_exists('get_field') ? get_field('course_root_lessons', $course_id) : get_post_meta($course_id, 'course_root_lessons', true);
                }

                echo dfh_render_lesson_tree($roots, 1);
                ?>
            </div>
        </nav>
        <footer class="progress-panel-footer">
            <a href="https://designforhumans.blog" class="progress-panel-brand">
                Design for Humans
            </a>
        </footer>
    </aside>
    <article id="post-<?php the_ID(); ?>" <?php post_class('lesson-article'); ?>>

        <header class="lesson-header prose">
            <div class="container">
                <h1 class="lesson-title">
                    <?php the_title(); ?>
                </h1>
                <?php if ($is_completed): ?>
                    <span class="lesson-kicker small-heading">Complete</span>
                <?php endif; ?>
            </div>
        </header>

        <?php
        // Video playback: Mux only (legacy fallback removed)
        $playback_id = get_post_meta(get_the_ID(), 'mux_playback_id', true);
        if ($playback_id): ?>
            <div class="lesson-video">
                <div class="lesson-video-container container">
                    <mux-player playback-id="<?php echo esc_attr($playback_id); ?>" accent-color="#2eab93"
                        metadata-video-title="<?php echo esc_attr(get_the_title()); ?>" style="width:100%;height:auto;">
                    </mux-player>
                </div>
            </div>
        <?php endif; ?>
        <div class="lesson-content">

            <div class="container lesson-main">
                <div class="lesson-body prose">
                    <?php
                    the_content();
                    ?>
                </div>
                <aside class="lesson-aside">
                            <div class="lesson-bookmark">
                                <button id="dfh-bookmark-btn" data-lesson-id="<?php echo esc_attr($lesson_id); ?>"
                                    data-nonce="<?php echo esc_attr(wp_create_nonce('dfh_bookmark_nonce')); ?>"
                                    class="<?php echo esc_attr($bookmark_class); ?>">
                                    <svg class="icon" width="32" height="32" title="Bookmark" aria-hidden="true"><use href="<?php echo esc_attr($bookmark_icon); ?>" /></svg>
                                    <span class="bookmark-label"><?php echo esc_html($bookmark_text); ?></span>
                                </button>
                            </div>
                    <?php
                    // Gather stats and external links; render aside only if either exists
                    $stats_meta = get_post_meta(get_the_ID(), 'lesson_stats', true);
                    $external_links = get_post_meta(get_the_ID(), 'lesson_external_links', true);

                    if (!empty($stats_meta) || !empty($external_links)): ?>

                        <?php
                        // Stats
                        if (!empty($stats_meta)):
                            $slines = explode("\n", $stats_meta);
                            ?>
                            <section class="lesson-resources lesson-stats" aria-describedby="lesson-stats-heading">
                                <h2 class="subheading lesson-resources-heading" id="lesson-stats-heading">Key statistics</h2>
                                <dl class="lesson-stats-list">
                                    <?php foreach ($slines as $sline) {
                                        $sline = trim($sline);
                                        if (empty($sline))
                                            continue;
                                        $parts = explode('|', $sline);
                                        $label = trim($parts[0]);
                                        $value = isset($parts[1]) ? trim($parts[1]) : '';
                                        $pc = isset($parts[2]) ? trim($parts[2]) : '';
                                        if ($label === '' && $value === '')
                                            continue;
                                        ?>
                                        <div class="stat" <?php echo ($pc ? 'style="--stat-progress:' . esc_attr($pc) . ';" data-pc="' . esc_attr($pc) . '"' : ''); ?>>
                                            <dt class="stat-label"><?php echo esc_html($label); ?></dt>
                                            <dd class="stat-value"><?php echo esc_html($value); ?></dd>
                                        </div>
                                    <?php } ?>
                                </dl>
                            </section>
                            <?php
                        endif;

                        // External links
                        if (!empty($external_links)):
                            ?>
                            <section class="lesson-resources lesson-links" aria-describedby="lesson-links-heading">
                                <div class="container">
                                    <h2 class="subheading lesson-resources-heading" id="lesson-links-heading">Further reading &
                                        links</h2>
                                    <ul class="resource-list">
                                        <?php
                                        $lines = explode("\n", $external_links);
                                        foreach ($lines as $line) {
                                            $line = trim($line);
                                            if (empty($line))
                                                continue;
                                            $parts = explode('|', $line);
                                            $link_title = trim($parts[0]);
                                            $link_url = isset($parts[1]) ? trim($parts[1]) : '#';
                                            echo '<li><a href="' . esc_url($link_url) . '" target="_blank" class="link" rel="noopener noreferrer">' . esc_html($link_title) . '</a></li>';
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </section>
                            <?php
                        endif;
                        ?>
                    <?php endif; ?>
                </aside>
            </div>
        </div>


        <!-- Child lessons -->
        <div class="lesson-explore">
            <?php
            // Render child lessons for this lesson (if any).
            $parent_id = get_the_ID();
            $current_code = dfh_get_lesson_hierarchy_number($parent_id);
            $child_level = 1;
            if (!empty($current_code)) {
                $child_level = count(explode('.', $current_code)) + 1;
            }
            ?>
            <?php
            $children_html = dfh_render_lesson_children($parent_id, $child_level);
            if (!empty(trim($children_html))): ?>
                <section class="container lesson-explore-section lesson-children" aria-describedby="lesson-children-heading"
                    data-level="<?php echo esc_attr($child_level); ?>">
                    <h2 class="heading" id="lesson-children-heading">Lessons in this section</h2>
                    <div class="lesson-list-container">
                        <?php echo $children_html; ?>
                    </div>
                </section>
            <?php endif; ?>
            <?php
            // Check if this is the final lesson in the course.
            // Prefer a course-aware check (find the course(s) that include this lesson
            // and see whether this lesson is the last in that course's ordered list).
            $is_last_lesson = false;
            $courses_for_lesson = function_exists('dfh_get_courses_for_lesson') ? dfh_get_courses_for_lesson($lesson_id) : array();
            if (!empty($courses_for_lesson)) {
                foreach ($courses_for_lesson as $c_id) {
                    // Get root lessons for this course (support ACF and plain postmeta)
                    $roots = function_exists('get_field') ? get_field('course_root_lessons', $c_id) : get_post_meta($c_id, 'course_root_lessons', true);
                    if (is_string($roots)) {
                        $maybe = @unserialize($roots);
                        if ($maybe !== false) {
                            $roots = $maybe;
                        }
                    }
                    if (empty($roots)) {
                        $roots = array();
                    }
                    if (!is_array($roots)) {
                        if (is_numeric($roots)) {
                            $roots = array((int) $roots);
                        } elseif (is_string($roots)) {
                            $parts = preg_split('/\s*,\s*|\s+/u', trim($roots));
                            $roots = array_map('intval', $parts);
                        } else {
                            $roots = array();
                        }
                    }

                    $course_lessons = array();
                    foreach ($roots as $r) {
                        $course_lessons = array_merge($course_lessons, dfh_get_ordered_lesson_tree((int) $r));
                    }
                    $course_lessons = array_values(array_unique($course_lessons));

                    if (!empty($course_lessons) && (int) end($course_lessons) === (int) $lesson_id) {
                        $is_last_lesson = true;
                        break;
                    }
                }
            } else {
                // Fall back to global adjacent calculation
                $is_last_lesson = empty($adjacent['next']);
            }

            $button_label = $is_last_lesson ? 'Complete course' : 'Next lesson';
            $button_class = $is_last_lesson ? 'button course-complete' : 'button next-lesson';

            // Compute the label shown to the user when the lesson is already completed.
            if ($is_completed) {
                if ($is_last_lesson) {
                    $display_button_label = 'Course home';
                    $button_class = 'button course-home';
                } else {
                    $display_button_label = 'Next lesson';
                }
            } else {
                $display_button_label = $button_label;
            }
            ?>

            <div class="container lesson-explore-section lesson-progression"
                aria-describedby="lesson-progression-heading">
                <h2 class="sr" id="progression-progress-heading">Proceed to the next step</h2>
                <div class="lesson-progression-buttons">
                    <?php if ($adjacent['previous']): ?>
                        <a href="<?php echo esc_url(get_permalink($adjacent['previous'])); ?>"
                            class="link-button prev-lesson">
                            <svg class="icon dir" width="32" height="32" aria-hidden="true"><use href="#ArrowLeft" /></svg>
                            Previous lesson
                        </a>
                    <?php endif; ?>

                            <?php
                            // Choose an icon id for the progress button
                            $complete_icon = $is_last_lesson ? '#Home' : '#ArrowRight';
                            ?>
                            <button id="dfh-complete-btn" data-lesson-id="<?php echo esc_attr($lesson_id); ?>"
                                data-nonce="<?php echo esc_attr(wp_create_nonce('dfh_progress_nonce')); ?>"
                                class="<?php echo esc_attr($button_class); ?>"
                                style="padding: 10px 20px; font-weight: 600; cursor: pointer;">
                                <span class="progress-label"><?php echo esc_html($display_button_label); ?></span>
                                <svg class="icon dir" width="32" height="32" aria-hidden="true"><use href="<?php echo esc_attr($complete_icon); ?>" /></svg>
                            </button>
                </div>
            </div>
        </div>


    </article><!-- #post-<?php the_ID(); ?> -->
</main><!-- #primary -->

<!-- Tiny Inline JS for AJAX Progression -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('dfh-complete-btn');
        if (!btn) return;

        btn.addEventListener('click', function () {
            const lessonId = this.getAttribute('data-lesson-id');
            const nonce = this.getAttribute('data-nonce');

            btn.disabled = true;
            const progressLabel = btn.querySelector('.progress-label');
            if (progressLabel) progressLabel.textContent = 'Saving...';

            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: new URLSearchParams({
                    action: 'dfh_mark_complete',
                    lesson_id: lessonId,
                    nonce: nonce
                })
            })
                .then(response => response.json())
                .then(data => {
                        if (data.success && data.data.next_url) {
                            window.location.href = data.data.next_url;
                        } else {
                            alert('Error updating progress. Please try again.');
                            btn.disabled = false;
                            if (progressLabel) progressLabel.textContent = '<?php echo esc_js( $display_button_label ); ?>';
                        }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.disabled = false;
                    btn.textContent = 'Next lesson';
                });
        });

        const bookmarkBtn = document.getElementById('dfh-bookmark-btn');
        if (!bookmarkBtn) return;

        bookmarkBtn.addEventListener('click', function () {
            const lessonId = this.getAttribute('data-lesson-id');
            const nonce = this.getAttribute('data-nonce');

            // Optimistic UI toggle effect
            const isCurrentlyBookmarked = this.classList.contains('active');

            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: new URLSearchParams({
                    action: 'dfh_toggle_bookmark',
                    lesson_id: lessonId,
                    nonce: nonce
                })
            })
                .then(response => response.json())
                .then(data => {
                        if (data.success) {
                            const label = bookmarkBtn.querySelector('.bookmark-label');
                            const useEl = bookmarkBtn.querySelector('use');
                            if (data.data.is_bookmarked) {
                                if (label) label.textContent = 'Bookmarked';
                                bookmarkBtn.classList.add('active');
                                bookmarkBtn.classList.add('strong');
                                if (useEl) useEl.setAttribute('href', '#Bookmarked');
                            } else {
                                if (label) label.textContent = 'Bookmark Lesson';
                                bookmarkBtn.classList.remove('active');
                                bookmarkBtn.classList.remove('strong');
                                if (useEl) useEl.setAttribute('href', '#Bookmark');
                            }
                        } else {
                            alert('Could not update bookmark.');
                        }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    });
</script>

<?php
get_footer();