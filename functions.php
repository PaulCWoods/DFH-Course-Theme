<?php
/**
 * DFH Course Theme Functions
 *
 * This file contains theme-specific functionality for the DFH Course Theme.
 * It is organized into logical sections:
 *  - Post Types: register CPTs used by the theme.
 *  - META Boxes: admin meta boxes (Mux, Links, Stats, Downloads).
 *  - Helpers: Tree rendering, hierarchy numbering, and traversal.
 *  - Student Management & Progress: Roles, Access, and AJAX tracking.
 */

/**
 * -------------------------------------------------------------------------
 * Post Types
 * -------------------------------------------------------------------------
 */

/**
 * Register the hierarchical `lesson` custom post type.
 * Supports parent/child nesting for module/chapter/page structure.
 *
 * @return void
 */
function dfh_register_lesson_cpt()
{
    $labels = array(
        'name' => 'Lessons',
        'singular_name' => 'Lesson',
        'menu_name' => 'Course Lessons',
        'add_new' => 'Add New Lesson',
        'add_new_item' => 'Add New Lesson',
        'edit_item' => 'Edit Lesson',
        'new_item' => 'New Lesson',
        'view_item' => 'View Lesson',
        'search_items' => 'Search Lessons',
        'not_found' => 'No lessons found',
        'not_found_in_trash' => 'No lessons found in trash'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true, // Enables Gutenberg block editor support
        'hierarchical' => true, // Enables parent/child lesson nesting
        'menu_icon' => 'dashicons-welcome-learn-more',
        'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'page-attributes', 'custom-fields'), // page-attributes adds Parent Lesson dropdown; custom-fields enables the Footnotes block.
        'rewrite' => array('slug' => 'lesson', 'with_front' => false),
    );

    register_post_type('lesson', $args);
}
add_action('init', 'dfh_register_lesson_cpt', 0);

/**
 * Register the `course` custom post type.
 * Used for course landing pages and syllabi that reference Lessons.
 *
 * @return void
 */
function dfh_register_course_cpt()
{
    $labels = array(
        'name' => 'Courses',
        'singular_name' => 'Course',
        'menu_name' => 'Courses',
        'add_new' => 'Add New Course',
        'add_new_item' => 'Add New Course',
        'edit_item' => 'Edit Course',
        'new_item' => 'New Course',
        'view_item' => 'View Course',
        'search_items' => 'Search Courses',
        'not_found' => 'No courses found',
        'not_found_in_trash' => 'No courses found in trash'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => 'course',
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true, // Enables Gutenberg block editor
        'menu_icon' => 'dashicons-welcome-add-page',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes'),
        'rewrite' => array('slug' => 'course', 'with_front' => false),
    );

    register_post_type('course', $args);
}
add_action('init', 'dfh_register_course_cpt', 0);

/**
 * -------------------------------------------------------------------------
 * META Boxes
 * -------------------------------------------------------------------------
 */

// 1. Mux Meta Box Registration (for Lessons & Sample Lessons)
function dfh_add_lesson_mux_meta_box()
{
    foreach (array('lesson', 'sample-lesson') as $screen) {
        add_meta_box(
            'dfh_mux_playback_id_box',
            'Mux Video Settings',
            'dfh_render_mux_meta_box_html',
            $screen,
            'normal',
            'high'
        );
    }
}
add_action('add_meta_boxes', 'dfh_add_lesson_mux_meta_box');

function dfh_render_mux_meta_box_html($post)
{
    wp_nonce_field('dfh_save_mux_meta_box', 'dfh_mux_nonce');
    $playback_id = get_post_meta($post->ID, 'mux_playback_id', true);
    ?>
    <div style="margin-bottom: 15px;">
        <label for="mux_playback_id" style="display: block; font-weight: 600; margin-bottom: 5px;">Mux Playback ID:</label>
        <input type="text" id="mux_playback_id" name="mux_playback_id" value="<?php echo esc_attr($playback_id); ?>"
            style="width: 100%; padding: 8px;" placeholder="e.g. C500v0293J6j02K029F301t02K...">
        <p style="font-size: 12px; color: #666; margin-top: 5px;">Paste the playback ID generated from your Mux dashboard
            for this lesson.</p>
    </div>
    <?php
}

// 2. External Links Meta Box Registration (for Lessons & Sample Lessons)
function dfh_add_lesson_links_meta_box()
{
    foreach (array('lesson', 'sample-lesson') as $screen) {
        add_meta_box(
            'dfh_lesson_links_box',
            'Lesson External Links',
            'dfh_render_links_meta_box_html',
            $screen,
            'normal',
            'high'
        );
    }
}
add_action('add_meta_boxes', 'dfh_add_lesson_links_meta_box');

function dfh_render_links_meta_box_html($post)
{
    wp_nonce_field('dfh_save_links_meta', 'dfh_links_nonce');
    $external_links = get_post_meta($post->ID, 'lesson_external_links', true);
    ?>
    <div style="margin-bottom: 10px;">
        <label style="display: block; font-weight: 600; margin-bottom: 5px;">External Links (One per line: Title |
            URL):</label>
        <textarea name="lesson_external_links" rows="4" style="width: 100%; padding: 8px;"
            placeholder="W3C Accessibility Guidelines | https://www.w3.org/WAI/standards-guidelines/&#10;A11y Project Checklist | https://www.a11yproject.com/checklist/"><?php echo esc_textarea($external_links); ?></textarea>
        <p style="font-size: 12px; color: #666; margin-top: 5px;">Format each link as `Link Title | https://url` on a new
            line.</p>
    </div>
    <?php
}

// 3. Lesson Stats Meta Box Registration (for Lessons & Sample Lessons)
function dfh_add_lesson_stats_meta_box()
{
    foreach (array('lesson', 'sample-lesson') as $screen) {
        add_meta_box(
            'dfh_lesson_stats_box',
            'Lesson Stats',
            'dfh_render_stats_meta_box_html',
            $screen,
            'normal',
            'default'
        );
    }
}
add_action('add_meta_boxes', 'dfh_add_lesson_stats_meta_box');

function dfh_render_stats_meta_box_html($post)
{
    wp_nonce_field('dfh_save_stats_meta', 'dfh_stats_nonce');
    $stats = get_post_meta($post->ID, 'lesson_stats', true);
    ?>
    <div style="margin-bottom: 10px;">
        <label style="display: block; font-weight: 600; margin-bottom: 5px;">Lesson Stats (one per line: Label |
            Value):</label>
        <textarea name="lesson_stats" rows="4" style="width: 100%; padding: 8px;"
            placeholder="Students | 120"><?php echo esc_textarea($stats); ?></textarea>
        <p style="font-size: 12px; color: #666; margin-top: 5px;">Each line should contain a label, a pipe, then the
            number/value.</p>
    </div>
    <?php
}

// 4. Associated Course Meta Box Registration (Sample Lessons Only)
function dfh_add_sample_lesson_course_meta_box()
{
    add_meta_box(
        'dfh_sample_lesson_course_box',
        'Associated Course',
        'dfh_render_sample_lesson_course_meta_box',
        'sample-lesson',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'dfh_add_sample_lesson_course_meta_box');

function dfh_render_sample_lesson_course_meta_box($post)
{
    wp_nonce_field('dfh_save_sample_lesson_course', 'dfh_sample_lesson_course_nonce');
    $associated_course_id = get_post_meta($post->ID, 'sample_lesson_course', true);
    $courses = get_posts(array(
        'post_type' => 'course',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));
    ?>
    <p>
        <label for="sample_lesson_course">Link this sample lesson to a course:</label>
    </p>
    <p>
        <select name="sample_lesson_course" id="sample_lesson_course" style="width: 100%;">
            <option value="">— Select Course —</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo esc_attr($course->ID); ?>" <?php selected($associated_course_id, $course->ID); ?>>
                    <?php echo esc_html($course->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p class="description">The course page will be displayed as the back link.</p>
    <?php
}

/**
 * Unified Save Routine for Lesson & Sample Lesson Meta Boxes
 */
function dfh_save_lesson_meta_boxes($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $post_type = get_post_type($post_id);
    if (!in_array($post_type, array('lesson', 'sample-lesson'), true)) {
        return;
    }

    // Save Mux ID
    if (isset($_POST['dfh_mux_nonce']) && wp_verify_nonce($_POST['dfh_mux_nonce'], 'dfh_save_mux_meta_box')) {
        if (isset($_POST['mux_playback_id'])) {
            update_post_meta($post_id, 'mux_playback_id', sanitize_text_field($_POST['mux_playback_id']));
        }
    }

    // Save External Links
    if (isset($_POST['dfh_links_nonce']) && wp_verify_nonce($_POST['dfh_links_nonce'], 'dfh_save_links_meta')) {
        if (isset($_POST['lesson_external_links'])) {
            update_post_meta($post_id, 'lesson_external_links', sanitize_textarea_field($_POST['lesson_external_links']));
        }
    }

    // Save Stats
    if (isset($_POST['dfh_stats_nonce']) && wp_verify_nonce($_POST['dfh_stats_nonce'], 'dfh_save_stats_meta')) {
        if (isset($_POST['lesson_stats'])) {
            update_post_meta($post_id, 'lesson_stats', sanitize_textarea_field($_POST['lesson_stats']));
        }
    }

    // Save Associated Course (Sample Lesson)
    if (isset($_POST['dfh_sample_lesson_course_nonce']) && wp_verify_nonce($_POST['dfh_sample_lesson_course_nonce'], 'dfh_save_sample_lesson_course')) {
        if (isset($_POST['sample_lesson_course'])) {
            $course_id = absint($_POST['sample_lesson_course']);
            if ($course_id) {
                update_post_meta($post_id, 'sample_lesson_course', $course_id);
            } else {
                delete_post_meta($post_id, 'sample_lesson_course');
            }
        }
    }
}
add_action('save_post_lesson', 'dfh_save_lesson_meta_boxes');
add_action('save_post_sample-lesson', 'dfh_save_lesson_meta_boxes');

/**
 * -------------------------------------------------------------------------
 * ACF Field Groups (registered only when ACF is available)
 * -------------------------------------------------------------------------
 */
if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
        'key' => 'group_course_syllabus',
        'title' => 'Course Syllabus',
        'fields' => array(
            array(
                'key' => 'field_course_lessons',
                'label' => 'Select Root Lessons',
                'name' => 'course_root_lessons',
                'type' => 'relationship',
                'instructions' => 'Select the top-level Lessons (Modules) for this course.',
                'post_type' => array('lesson'),
                'return_format' => 'id',
            ),
            array(
                'key' => 'field_course_closed',
                'label' => 'Course closed',
                'name' => 'course_closed',
                'type' => 'true_false',
                'message' => 'Show this course as coming soon and hide access buttons.',
                'default_value' => 0,
                'ui' => 1,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'course',
                ),
            ),
        ),
    ));

    acf_add_local_field_group(array(
        'key' => 'group_lesson_header',
        'title' => 'Lesson Header',
        'fields' => array(
            array(
                'key' => 'field_lesson_subtitle',
                'label' => 'Subtitle',
                'name' => 'subtitle',
                'type' => 'text',
                'instructions' => 'Optional subtitle displayed beneath the lesson title.',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'lesson',
                ),
            ),
        ),
    ));
}

/**
 * -------------------------------------------------------------------------
 * Helpers & Tree Traversal
 * -------------------------------------------------------------------------
 */

/**
 * The library's published CSS, pinned to a version. Define DFH_COMPONENTS_CSS
 * in wp-config.php to load another build (a local one, or the next version).
 */
function dfh_library_css($file)
{
    $base = defined('DFH_COMPONENTS_CSS') ? DFH_COMPONENTS_CSS : 'https://components.designforhumans.blog/css/0.4.0';
    return rtrim($base, '/') . '/' . $file;
}

/**
 * DFH Shared Styles (what the course, the blog and the components docs share),
 * pinned to a tag. Define DFH_SHARED_CSS in wp-config.php to load another
 * build (a local one, or the next version).
 */
function dfh_shared_css()
{
    return defined('DFH_SHARED_CSS') ? DFH_SHARED_CSS : 'https://cdn.jsdelivr.net/gh/PaulCWoods/dfh-shared-styles@0.3.0/dist/dfh-site.css';
}

/**
 * The sub-brand class for a top-level lesson number, set beside its
 * data-lesson or data-syllabus. Lesson 1 wears the course itself; 2 to 4
 * their own colours (tokens/themes/course-*.json in DFH-Components).
 */
function dfh_lesson_theme_class($number)
{
    $themes = array('1' => 'theme-course', '2' => 'theme-course-aqua', '3' => 'theme-course-purple', '4' => 'theme-course-olive');
    return $themes[(string) $number] ?? '';
}

function dfh_get_lesson_hierarchy_number($post_id = null)
{
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'lesson') {
        return '';
    }

    $ancestors = get_post_ancestors($post->ID);
    $ancestors = array_reverse($ancestors);
    $ancestors[] = $post->ID;

    $numbers = array();

    foreach ($ancestors as $ancestor_id) {
        $current_post = get_post($ancestor_id);
        if (!$current_post) {
            continue;
        }

        $siblings = get_posts(array(
            'post_type' => 'lesson',
            'posts_per_page' => -1,
            'post_parent' => $current_post->post_parent,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
            'fields' => 'ids',
        ));

        $position = array_search($current_post->ID, $siblings);
        $numbers[] = (false !== $position) ? ($position + 1) : 1;
    }

    return !empty($numbers) ? implode('.', $numbers) : '';
}

function dfh_get_lesson_code($post_id = null)
{
    if (!$post_id) {
        $post = get_post();
        $post_id = $post ? $post->ID : null;
    }

    if (!$post_id) {
        return '';
    }

    if (function_exists('get_field')) {
        $m = get_field('lesson_module', $post_id);
        $c = get_field('lesson_chapter', $post_id);
        $p = get_field('lesson_page', $post_id);
    } else {
        $m = get_post_meta($post_id, 'lesson_module', true);
        $c = get_post_meta($post_id, 'lesson_chapter', true);
        $p = get_post_meta($post_id, 'lesson_page', true);
    }

    if ($m && $c && $p) {
        return sprintf('%d.%d.%d', (int) $m, (int) $c, (int) $p);
    }

    return dfh_get_lesson_hierarchy_number($post_id);
}

/**
 * Recursively flattens the hierarchical lesson tree into an ordered array of IDs.
 */
function dfh_get_ordered_lesson_tree($parent_id = 0, &$flattened = array())
{
    $args = array(
        'post_type' => 'lesson',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => array('menu_order' => 'ASC', 'date' => 'ASC'),
        'post_parent' => $parent_id,
    );

    $children = get_posts($args);

    foreach ($children as $child) {
        $flattened[] = $child->ID;
        dfh_get_ordered_lesson_tree($child->ID, $flattened);
    }

    return $flattened;
}

/**
 * Find the Next and Previous lesson relative to a current lesson ID in the tree
 */
function dfh_get_adjacent_lesson($current_lesson_id)
{
    $all_lessons = dfh_get_ordered_lesson_tree(0);
    $currentIndex = array_search((int) $current_lesson_id, array_map('intval', $all_lessons), true);

    $result = array(
        'next' => null,
        'previous' => null,
    );

    if (false !== $currentIndex) {
        if (isset($all_lessons[$currentIndex + 1])) {
            $result['next'] = $all_lessons[$currentIndex + 1];
        }
        if (isset($all_lessons[$currentIndex - 1])) {
            $result['previous'] = $all_lessons[$currentIndex - 1];
        }
    }

    return $result;
}

/**
 * The lesson tree, as the library's Navigation Tree: nested lists of
 * Buttons. A lesson the student can open is a link (aria-current on the
 * page being read); one they haven't reached is an unavailable link,
 * dimmed and locked; for someone without the course, every lesson is
 * plain text, so the plan reads as a list rather than a wall of locks.
 * The caller wraps the lists: a <nav class="navigation-tree"> where the
 * lessons can be opened, a <div class="navigation-tree"> otherwise.
 */
function dfh_lesson_tree_context($active_lesson = null)
{
    if (null === $active_lesson) {
        $active_lesson = dfh_get_student_current_lesson();
    }
    return array(
        'active' => $active_lesson,
        'access' => dfh_user_has_course_access(),
        'open' => dfh_lessons_open() || current_user_can('administrator'),
    );
}

/* What a student can do with a lesson: read it, not yet, or only see it */
function dfh_lesson_state($lesson_id, $context)
{
    if (!$context['access']) {
        return 'listed';
    }
    $is_active = $context['active'] && (int) $context['active'] === (int) $lesson_id;
    if (dfh_is_lesson_completed($lesson_id)) {
        return 'complete';
    }
    if ($is_active) {
        return 'in-progress';
    }
    return $context['open'] ? 'open' : 'locked';
}

/* Started: the lesson or any lesson under it is complete or in progress */
function dfh_lesson_started($lesson_id, $context)
{
    if (in_array(dfh_lesson_state($lesson_id, $context), array('complete', 'in-progress'), true)) {
        return true;
    }
    foreach (dfh_get_lesson_children($lesson_id) as $child_id) {
        if (dfh_lesson_started($child_id, $context)) {
            return true;
        }
    }
    return false;
}

function dfh_lesson_status_html($state)
{
    $statuses = array(
        'complete' => '<span class="status status--plain status--success"><span class="status__text">Complete</span></span>',
        'in-progress' => '<span class="status status--plain status--accent"><span class="status__text">In progress</span></span>',
    );
    return $statuses[$state] ?? '';
}

function dfh_lesson_label_html($lesson_id)
{
    $code = dfh_get_lesson_hierarchy_number($lesson_id);
    return ($code ? esc_html($code) . ' ' : '') . esc_html(get_the_title($lesson_id));
}

function dfh_render_lesson_item($lesson_id, $context)
{
    $label = '<span>' . dfh_lesson_label_html($lesson_id) . '</span>';
    $state = dfh_lesson_state($lesson_id, $context);

    if ('listed' === $state) {
        return '<span class="button">' . $label . '</span>';
    }
    if ('locked' === $state) {
        return '<a role="link" class="button" aria-disabled="true">' . $label
            . '<svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#Lock" /></svg><span class="sr-only">, locked</span></a>';
    }

    $bookmark = dfh_is_lesson_bookmarked($lesson_id)
        ? '<svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#BookmarkFilled" /></svg><span class="sr-only">, bookmarked</span>'
        : '';
    $current = (int) $lesson_id === (int) get_the_ID() ? ' aria-current="page"' : '';

    return '<a class="button" href="' . esc_url(get_permalink($lesson_id)) . '"' . $current . '>' . $label . $bookmark . dfh_lesson_status_html($state) . '</a>';
}

function dfh_get_lesson_children($parent_id)
{
    return get_posts(array(
        'post_type' => 'lesson',
        'post_parent' => $parent_id,
        'posts_per_page' => -1,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
        'fields' => 'ids',
    ));
}

/* An <ol> of lessons, each with its own children nested inside it */
function dfh_render_lesson_list($lesson_ids, $context)
{
    if (empty($lesson_ids)) {
        return '';
    }
    $output = '<ol>';
    foreach ($lesson_ids as $lesson_id) {
        $output .= '<li>' . dfh_render_lesson_item($lesson_id, $context)
            . dfh_render_lesson_list(dfh_get_lesson_children($lesson_id), $context) . '</li>';
    }
    return $output . '</ol>';
}

function dfh_render_lesson_children($parent_id, $active_lesson = null)
{
    return dfh_render_lesson_list(dfh_get_lesson_children($parent_id), dfh_lesson_tree_context($active_lesson));
}

/* A course's root lessons, without any that sit inside another root */
function dfh_get_root_lesson_ids($roots)
{
    if (empty($roots)) {
        return dfh_get_lesson_children(0);
    }
    $root_ids = array();
    foreach ((array) $roots as $r) {
        $id = is_object($r) ? (int) $r->ID : (int) $r;
        if ($id) {
            $root_ids[] = $id;
        }
    }
    $root_ids = array_values(array_unique($root_ids));
    return array_values(array_filter($root_ids, function ($id) use ($root_ids) {
        return !array_intersect(get_post_ancestors($id), $root_ids);
    }));
}

function dfh_render_lesson_tree($roots = null, $active_lesson = null)
{
    return dfh_render_lesson_list(dfh_get_root_lesson_ids($roots), dfh_lesson_tree_context($active_lesson));
}

/**
 * The course plan: a Card for each root lesson, in its sub-brand colour,
 * with the root as its title and its lessons as a Navigation Tree. The
 * caller wraps it in a <nav> when the lessons can be opened.
 */
function dfh_render_course_plan($roots = null, $active_lesson = null)
{
    $context = dfh_lesson_tree_context($active_lesson);
    $output = '<ol class="grid grid--2-col grid--sm" role="list">';
    foreach (dfh_get_root_lesson_ids($roots) as $root_id) {
        $state = dfh_lesson_state($root_id, $context);
        $title = dfh_lesson_label_html($root_id);
        if (in_array($state, array('complete', 'in-progress', 'open'), true)) {
            $title = '<a class="link link--plain" href="' . esc_url(get_permalink($root_id)) . '">' . $title . '</a>';
        } elseif ('locked' === $state) {
            $title .= '<span class="sr-only">, locked</span>';
        }
        $frame = dfh_lesson_started($root_id, $context) ? ' border bg-accent border-accent' : ' border border-muted';
        $output .= '<li class="card card--ruled ' . esc_attr(dfh_lesson_theme_class(dfh_get_lesson_hierarchy_number($root_id)) . $frame) . '">'
            . '<header class="card__header"><h3 class="card__title heading">' . $title . '</h3>' . dfh_lesson_status_html($state) . '</header>'
            . '<div class="card__body"><div class="navigation-tree navigation-tree--compact">'
            . dfh_render_lesson_list(dfh_get_lesson_children($root_id), $context)
            . '</div></div></li>';
    }
    return $output . '</ol>';
}

/**
 * -------------------------------------------------------------------------
 * Student Management & Progress Tracking
 * -------------------------------------------------------------------------
 */

/**
 * 1. Register Custom Student Role
 */
function dfh_add_student_role()
{
    add_role(
        'course_student',
        __('Course Student', 'dfh'),
        array(
            'read' => true,
            'edit_posts' => false,
            'upload_files' => false,
        )
    );
}
add_action('init', 'dfh_add_student_role');

/**
 * 2. Add User Profile Fields to Admin Screen
 */
function dfh_show_extra_user_profile_fields($user)
{
    if (!current_user_can('administrator')) {
        return;
    }

    $is_enrolled = get_user_meta($user->ID, 'dfh_course_enrolled', true);
    $student_name = get_user_meta($user->ID, 'dfh_student_name', true);
    ?>
    <h3>Course Access Control</h3>
    <table class="form-table">
        <tr>
            <th><label for="dfh_student_name">Student Display Name</label></th>
            <td>
                <input type="text" name="dfh_student_name" id="dfh_student_name"
                    value="<?php echo esc_attr($student_name); ?>" class="regular-text" />
                <p class="description">Custom name to display on certificates. If empty, will use Display Name or username.
                </p>
            </td>
        </tr>
        <tr>
            <th><label for="dfh_course_enrolled">Course Enrollment</label></th>
            <td>
                <label>
                    <input type="checkbox" name="dfh_course_enrolled" id="dfh_course_enrolled" value="1" <?php checked($is_enrolled, '1'); ?>>
                    Grant access to Design for Humans course and lessons
                </label>
                <p class="description">Check this box to grant manual student access.</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'dfh_show_extra_user_profile_fields');
add_action('edit_user_profile', 'dfh_show_extra_user_profile_fields');

/**
 * 3. Save User Profile Fields Data
 */
function dfh_save_extra_user_profile_fields($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return;
    }

    // Save student name
    if (isset($_POST['dfh_student_name'])) {
        $student_name = sanitize_text_field($_POST['dfh_student_name']);
        if (!empty($student_name)) {
            update_user_meta($user_id, 'dfh_student_name', $student_name);
        } else {
            delete_user_meta($user_id, 'dfh_student_name');
        }
    }

    // Save enrollment status
    if (isset($_POST['dfh_course_enrolled']) && '1' === $_POST['dfh_course_enrolled']) {
        update_user_meta($user_id, 'dfh_course_enrolled', '1');
    } else {
        delete_user_meta($user_id, 'dfh_course_enrolled');
    }
}
add_action('personal_options_update', 'dfh_save_extra_user_profile_fields');
add_action('edit_user_profile_update', 'dfh_save_extra_user_profile_fields');

/**
 * Every lesson open to anyone, for working on the templates: only on a local
 * install, and only when its wp-config.php defines DFH_OPEN_LESSONS as true.
 */
function dfh_lessons_open()
{
    return 'local' === wp_get_environment_type() && defined('DFH_OPEN_LESSONS') && DFH_OPEN_LESSONS;
}

/**
 * 4. Helper Function: Check if User Has Access (With Admin Safety Override)
 */
function dfh_user_has_course_access($user_id = 0)
{
    if (dfh_lessons_open()) {
        return true;
    }

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return false;
    }

    if (user_can($user_id, 'administrator')) {
        return true;
    }

    $is_enrolled = get_user_meta($user_id, 'dfh_course_enrolled', true);
    return ('1' === $is_enrolled);
}

/**
 * 5. Get Completed Lessons Array
 */
function dfh_get_completed_lessons($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return array();
    }

    $completed = get_user_meta($user_id, 'dfh_completed_lessons', true);
    return is_array($completed) ? $completed : array();
}

/**
 * 6. Check if Specific Lesson is Completed
 */
function dfh_is_lesson_completed($lesson_id, $user_id = 0)
{
    $completed = dfh_get_completed_lessons($user_id);
    return in_array((int) $lesson_id, array_map('intval', $completed), true);
}

/**
 * 7. AJAX Handler to Mark Lesson Complete & Return Next URL
 */
function dfh_ajax_mark_lesson_complete()
{
    check_ajax_referer('dfh_progress_nonce', 'nonce');

    $user_id = get_current_user_id();
    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;

    if (!$user_id || !$lesson_id) {
        wp_send_json_error(array('message' => 'Invalid request.'));
    }
    // Record completion and timestamps using helper (handles per-lesson and per-course timestamps)
    dfh_mark_lesson_completed($lesson_id, $user_id);

    // Determine next lesson in tree sequence
    $adjacent = dfh_get_adjacent_lesson($lesson_id);
    $next_url = '';

    if (!empty($adjacent['next'])) {
        $next_url = get_permalink($adjacent['next']);
    } else {
        // If no next lesson, try to return to the Course page that contains this lesson
        $courses = dfh_get_courses_for_lesson($lesson_id);
        if (!empty($courses)) {
            $next_url = get_permalink((int) $courses[0]);
        } else {
            // Fallback to course archive
            $next_url = get_post_type_archive_link('course');
        }
    }

    wp_send_json_success(array(
        'message' => 'Lesson marked as complete.',
        'next_url' => $next_url,
    ));
}
add_action('wp_ajax_dfh_mark_complete', 'dfh_ajax_mark_lesson_complete');


/**
 * Find the student's current active lesson (the first uncompleted lesson in sequence).
 * Returns the lesson Post ID, or the first lesson if none started, or false if all complete.
 */
function dfh_get_student_current_lesson($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    // Get all published lessons in hierarchical order
    $all_lessons = dfh_get_ordered_lesson_tree(0);
    if (empty($all_lessons)) {
        return false;
    }

    $completed = dfh_get_completed_lessons($user_id);

    // Find the first lesson in the tree that is NOT in the completed array
    foreach ($all_lessons as $lesson_id) {
        if (!in_array((int) $lesson_id, array_map('intval', $completed), true)) {
            return $lesson_id; // This is their active "in-progress" lesson
        }
    }

    // If all lessons are completed, return 'completed'
    return 'completed';
}

/**
 * Record that a user has started a lesson (timestamped).
 * Stores an associative array in user meta `dfh_started_lessons` => [ lesson_id => timestamp ]
 */
function dfh_mark_lesson_started($lesson_id, $user_id = 0)
{
    if (!$lesson_id) {
        return false;
    }
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    $timestamps = get_user_meta($user_id, 'dfh_started_lessons', true);
    if (!is_array($timestamps)) {
        $timestamps = array();
    }

    if (empty($timestamps[$lesson_id])) {
        $timestamps[$lesson_id] = (int) current_time('timestamp');
        update_user_meta($user_id, 'dfh_started_lessons', $timestamps);
    }

    return true;
}

/**
 * Internal: mark lesson completed for user and record a completion timestamp.
 * Returns true if the lesson was newly marked as complete, false if it already existed.
 */
function dfh_mark_lesson_completed($lesson_id, $user_id = 0)
{
    if (!$lesson_id) {
        return false;
    }
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    $completed = dfh_get_completed_lessons($user_id);
    $was_new = false;
    if (!in_array((int) $lesson_id, array_map('intval', $completed), true)) {
        $completed[] = (int) $lesson_id;
        update_user_meta($user_id, 'dfh_completed_lessons', $completed);
        $was_new = true;
    }

    // Record per-lesson completion timestamp
    $timestamps = get_user_meta($user_id, 'dfh_completed_lessons_timestamps', true);
    if (!is_array($timestamps)) {
        $timestamps = array();
    }
    $timestamps[$lesson_id] = (int) current_time('timestamp');
    update_user_meta($user_id, 'dfh_completed_lessons_timestamps', $timestamps);

    // After marking lesson complete, check and timestamp any course completions
    dfh_mark_course_completed_if_needed($lesson_id, $user_id);

    return $was_new;
}

/**
 * Get the started timestamp (integer) for a lesson for a user, or null.
 */
function dfh_get_lesson_started_timestamp($lesson_id, $user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    $timestamps = get_user_meta($user_id, 'dfh_started_lessons', true);
    if (is_array($timestamps) && isset($timestamps[$lesson_id])) {
        return (int) $timestamps[$lesson_id];
    }
    return null;
}

/**
 * Get the completed timestamp (integer) for a lesson for a user, or null.
 */
function dfh_get_lesson_completed_timestamp($lesson_id, $user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    $timestamps = get_user_meta($user_id, 'dfh_completed_lessons_timestamps', true);
    if (is_array($timestamps) && isset($timestamps[$lesson_id])) {
        return (int) $timestamps[$lesson_id];
    }
    return null;
}

/**
 * Find courses that include the given lesson (by checking each course's root lessons).
 * Returns an array of course post IDs.
 */
function dfh_get_courses_for_lesson($lesson_id)
{
    $courses = get_posts(array(
        'post_type' => 'course',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
    ));

    $matched = array();
    foreach ($courses as $course_id) {
        // Support both ACF stored arrays and plain postmeta
        $roots = array();
        if (function_exists('get_field')) {
            $roots = get_field('course_root_lessons', $course_id);
        }
        if (empty($roots)) {
            $roots = get_post_meta($course_id, 'course_root_lessons', true);
        }
        if (!is_array($roots)) {
            // Could be single ID or CSV; normalize
            if (empty($roots)) {
                $roots = array();
            } elseif (is_numeric($roots)) {
                $roots = array((int) $roots);
            } elseif (is_string($roots)) {
                $parts = preg_split('/\s*,\s*|\s+/u', trim($roots));
                $roots = array_map('intval', $parts);
            } else {
                $roots = array();
            }
        }

        if (empty($roots)) {
            continue;
        }

        $ancestors = get_post_ancestors($lesson_id);
        foreach ($roots as $root_id) {
            $root_id = (int) $root_id;
            if ($root_id === (int) $lesson_id || in_array($root_id, $ancestors, true)) {
                $matched[] = $course_id;
                break;
            }
        }
    }

    return array_values(array_unique($matched));
}

/**
 * A course's root lesson IDs, however they were stored (an ACF array, an
 * ID, a list in a string or a serialised array).
 */
function dfh_get_course_root_ids($course_id)
{
    $roots = function_exists('get_field') ? get_field('course_root_lessons', $course_id) : null;
    if (empty($roots)) {
        $roots = get_post_meta($course_id, 'course_root_lessons', true);
    }
    if (is_string($roots)) {
        $maybe = @unserialize($roots);
        $roots = false !== $maybe ? $maybe : preg_split('/\s*,\s*|\s+/u', trim($roots));
    }
    $ids = array();
    foreach ((array) $roots as $root) {
        $id = is_object($root) ? (int) $root->ID : (int) $root;
        if ($id) {
            $ids[] = $id;
        }
    }
    return array_values(array_unique($ids));
}

/* Every lesson in a course, in reading order */
function dfh_get_course_lesson_ids($course_id)
{
    $lessons = array();
    foreach (dfh_get_root_lesson_ids(dfh_get_course_root_ids($course_id)) as $root_id) {
        $lessons[] = $root_id;
        $lessons = array_merge($lessons, dfh_get_ordered_lesson_tree($root_id));
    }
    return array_values(array_unique($lessons));
}

/**
 * For each course that the lesson belongs to, if all its lessons are completed by the user,
 * set/update a per-user course completion timestamp stored in `dfh_course_completed_at` (assoc array course_id => timestamp).
 */
function dfh_mark_course_completed_if_needed($lesson_id, $user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }

    $courses = dfh_get_courses_for_lesson($lesson_id);
    if (empty($courses)) {
        return;
    }

    $completed = dfh_get_completed_lessons($user_id);

    foreach ($courses as $course_id) {
        // Gather all lessons for this course from its root lessons
        $roots = get_field('course_root_lessons', $course_id);
        if (empty($roots)) {
            $roots = get_post_meta($course_id, 'course_root_lessons', true);
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

        if (empty($course_lessons)) {
            continue;
        }

        // If every lesson in course_lessons is in $completed, mark course completed
        $all_done = true;
        $completed_int = array_map('intval', $completed);
        foreach ($course_lessons as $lid) {
            if (!in_array((int) $lid, $completed_int, true)) {
                $all_done = false;
                break;
            }
        }

        if ($all_done) {
            $course_timestamps = get_user_meta($user_id, 'dfh_course_completed_at', true);
            if (!is_array($course_timestamps)) {
                $course_timestamps = array();
            }
            if (empty($course_timestamps[$course_id])) {
                $course_timestamps[$course_id] = (int) current_time('timestamp');
                update_user_meta($user_id, 'dfh_course_completed_at', $course_timestamps);
            }
        }
    }
}


/**
 * -------------------------------------------------------------------------
 * Bookmarking
 * -------------------------------------------------------------------------
 */

/**
 * Get an array of bookmarked lesson IDs for a user
 */
function dfh_get_bookmarked_lessons($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return array();
    }

    $bookmarks = get_user_meta($user_id, 'dfh_bookmarked_lessons', true);
    return is_array($bookmarks) ? $bookmarks : array();
}

/**
 * Check if a specific lesson is bookmarked
 */
function dfh_is_lesson_bookmarked($lesson_id, $user_id = 0)
{
    $bookmarks = dfh_get_bookmarked_lessons($user_id);
    return in_array((int) $lesson_id, array_map('intval', $bookmarks), true);
}

/**
 * AJAX Handler to Toggle Bookmark State
 */
function dfh_ajax_toggle_bookmark()
{
    check_ajax_referer('dfh_bookmark_nonce', 'nonce');

    $user_id = get_current_user_id();
    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;

    if (!$user_id || !$lesson_id) {
        wp_send_json_error(array('message' => 'Invalid request.'));
    }

    $bookmarks = dfh_get_bookmarked_lessons($user_id);
    $is_bookmarked = false;

    // Toggle logic: remove if exists, add if missing
    $key = array_search($lesson_id, array_map('intval', $bookmarks), true);
    if (false !== $key) {
        unset($bookmarks[$key]);
        $bookmarks = array_values($bookmarks); // Reindex array
        $is_bookmarked = false;
    } else {
        $bookmarks[] = $lesson_id;
        $is_bookmarked = true;
    }

    update_user_meta($user_id, 'dfh_bookmarked_lessons', $bookmarks);

    wp_send_json_success(array(
        'is_bookmarked' => $is_bookmarked,
        'message' => $is_bookmarked ? 'Lesson bookmarked.' : 'Bookmark removed.'
    ));
}
add_action('wp_ajax_dfh_toggle_bookmark', 'dfh_ajax_toggle_bookmark');


/**
 * Automatically assign the 'course_student' role to newly registered users.
 *
 * @param int $user_id ID of the newly registered user.
 */
function dfh_set_default_user_role($user_id)
{
    $user = new WP_User($user_id);

    // Ensure we don't accidentally override administrator registrations
    if (in_array('administrator', $user->roles, true)) {
        return;
    }

    // Set role to course_student
    $user->set_role('course_student');
}
add_action('user_register', 'dfh_set_default_user_role');


/**
 * Style the native WordPress login/lost password screen to match theme branding.
 */
function dfh_custom_login_styles()
{
    ?>
    <style type="text/css">
        .wp-login-logo {
            display: none;
        }

        body.login {
            background-color: #f9f9f9;
            /* Match your background token */
        }

        body.login div#login h1 a {
            background-image: url('<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/logo.svg'); ?>');
            background-size: contain;
            width: 200px;
            height: 60px;
        }

        /* Style form buttons to match your theme buttons */
        body.login .button.button-primary {
            background: var(--color-primary, #0073aa);
            border-color: var(--color-primary, #0073aa);
            box-shadow: none;
        }
    </style>
    <?php
}
add_action('login_enqueue_scripts', 'dfh_custom_login_styles');

/**
 * Redirect course students to the course page upon login instead of the WP Dashboard.
 * Administrators are still allowed into the dashboard.
 *
 * @param string  $redirect_to           The default redirect destination.
 * @param string  $requested_redirect_to The requested redirect destination.
 * @param WP_User $user                  The logged-in user object.
 * @return string                        The modified redirect URL.
 */
function dfh_redirect_student_after_login($redirect_to, $requested_redirect_to, $user)
{
    // Make sure $user is a valid WP_User object
    if (isset($user->roles) && is_array($user->roles)) {
        // If they are a course student (and NOT an administrator), redirect to the course
        if (in_array('course_student', $user->roles, true) && !in_array('administrator', $user->roles, true)) {
            return home_url(); // Adjust if your course archive/slug differs
        }
    }

    return $redirect_to;
}
add_filter('login_redirect', 'dfh_redirect_student_after_login', 10, 3);

/**
 * Hide the WordPress admin bar for course students.
 * Administrators will still see it.
 */
function dfh_hide_admin_bar_for_students($show)
{
    if (current_user_can('course_student') && !current_user_can('administrator')) {
        return false;
    }
    return $show;
}
add_filter('show_admin_bar', 'dfh_hide_admin_bar_for_students');

/**
 * Add a meta description using the current post excerpt when available.
 */
function dfh_add_meta_description()
{
    $description = '';

    if (is_singular()) {
        $description = get_post_field('post_excerpt', get_queried_object_id());
        $description = preg_replace('/\s+/', ' ', wp_strip_all_tags($description));
        $description = trim($description);
    }

    if (empty($description)) {
        $description = 'Learn to design and build digital products that work better for the people who use them.';
    }

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
}
add_action('wp_head', 'dfh_add_meta_description', 1);

/**
 * Register a meta box for linking a WooCommerce Product ID to a Course.
 */
function dfh_register_course_product_meta_box()
{
    add_meta_box(
        'dfh_course_product_id_box',
        __('WooCommerce Product ID', 'dfh'),
        'dfh_render_course_product_meta_box',
        'course',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'dfh_register_course_product_meta_box');

/**
 * Render the meta box input field.
 */
function dfh_render_course_product_meta_box($post)
{
    // Add a nonce field for security
    wp_nonce_field('dfh_save_course_product_meta', 'dfh_course_product_nonce');

    // Retrieve current value if it exists
    $product_id = get_post_meta($post->ID, '_dfh_product_id', true);
    ?>
    <p>
        <label for="dfh_product_id_field">Enter the WooCommerce Product ID for this course:</label>
    </p>
    <p>
        <input type="number" id="dfh_product_id_field" name="dfh_product_id" value="<?php echo esc_attr($product_id); ?>"
            style="width: 100%;" />
    </p>
    <?php
}

/**
 * Save the meta box value when the course is saved.
 */
function dfh_save_course_product_meta($post_id)
{
    // Check nonce
    if (!isset($_POST['dfh_course_product_nonce']) || !wp_verify_nonce($_POST['dfh_course_product_nonce'], 'dfh_save_course_product_meta')) {
        return;
    }

    // Check user permissions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save or delete the product ID meta
    if (isset($_POST['dfh_product_id'])) {
        $product_id = sanitize_text_field($_POST['dfh_product_id']);
        if (!empty($product_id)) {
            update_post_meta($post_id, '_dfh_product_id', $product_id);
        } else {
            delete_post_meta($post_id, '_dfh_product_id');
        }
    }
}
add_action('save_post_course', 'dfh_save_course_product_meta');

/**
 * Automatically grant course access when a WooCommerce order is processing or completed.
 */
function dfh_grant_course_access_on_purchase($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    $user_id = $order->get_user_id();
    if (!$user_id) {
        return;
    }

    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();

        if (dfh_course_for_product($product_id)) {
            update_user_meta($user_id, 'dfh_course_enrolled', '1');
        }
    }
}
// Listen to both processing and completed states so the user gets instant access
add_action('woocommerce_order_status_processing', 'dfh_grant_course_access_on_purchase');
add_action('woocommerce_order_status_completed', 'dfh_grant_course_access_on_purchase');

/**
 * The course a WooCommerce product sells (the course's WooCommerce Product ID),
 * or null.
 */
function dfh_course_for_product($product_id)
{
    $courses = get_posts(array(
        'post_type' => 'course',
        'meta_key' => '_dfh_product_id',
        'meta_value' => $product_id,
        'numberposts' => 1,
    ));
    return $courses ? $courses[0] : null;
}

/**
 * Courses are sold from their own page, so a product's page (which the cart
 * links to) goes to the course it sells, or the courses home.
 */
function dfh_redirect_product_to_course()
{
    if (!function_exists('is_product') || !is_product()) {
        return;
    }
    $course = dfh_course_for_product(get_queried_object_id());
    wp_safe_redirect($course ? get_permalink($course) : home_url('/'), 301);
    exit;
}
add_action('template_redirect', 'dfh_redirect_product_to_course');

/**
 * Completely disable WooCommerce default stylesheets.
 */
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

/**
 * WooCommerce's generated fields (addresses) take the library's Field classes.
 * Its account forms are overridden in woocommerce/myaccount/.
 */
function dfh_woocommerce_field_classes($args)
{
    // A state is a text input where the country has no list (the UK)
    $controls = array('select' => 'select', 'country' => 'select', 'textarea' => 'textarea', 'checkbox' => 'checkbox-input', 'radio' => 'radio-input');
    $args['class'][] = 'field';
    $args['label_class'][] = 'field__label';
    $args['label_class'][] = 'label';
    $args['input_class'][] = $controls[$args['type']] ?? 'input';
    return $args;
}
add_filter('woocommerce_form_field_args', 'dfh_woocommerce_field_classes');

// Without selectWoo, WooCommerce keeps the native country and state selects
function dfh_remove_select_woo()
{
    wp_dequeue_script('selectWoo');
    wp_dequeue_style('select2');
}
add_action('wp_enqueue_scripts', 'dfh_remove_select_woo', 100);

/**
 * WordPress's own front-end styles. It prints them outside any cascade layer,
 * so they beat every rule in the library's layers whatever its specificity.
 * The course styles its content with the library instead; css/src/wordpress.css
 * covers the WordPress classes that still need styling. The editor keeps them.
 */
function dfh_remove_wordpress_styles()
{
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('classic-theme-styles');
    wp_dequeue_style('global-styles');
    wp_dequeue_style('woocommerce-inline');
}
add_action('wp_enqueue_scripts', 'dfh_remove_wordpress_styles', 100);

remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
remove_action('wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles');
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_enqueue_scripts', 'wp_enqueue_emoji_styles');

// Each core block's own stylesheet, which loads when the block is on the page
function dfh_remove_core_block_styles($args, $name)
{
    if (!is_admin() && str_starts_with($name, 'core/')) {
        $args['style_handles'] = array();
    }
    return $args;
}
add_filter('register_block_type_args', 'dfh_remove_core_block_styles', 10, 2);

/**
 * A WooCommerce notice as the library's Banner: the intent's icon, the message
 * in a text atom, and any links WooCommerce styles as buttons ("View cart",
 * "Confirm email address") moved under it in a cluster. Used by the theme's
 * woocommerce/notices templates.
 */
function dfh_notice_banner($messages, $intent, $icon, $extra_class = '')
{
    $actions = array();
    $items = array();
    foreach ($messages as $notice) {
        $html = wc_kses_notice($notice['notice']);
        $html = preg_replace_callback('#<a\s[^>]*class="[^"]*\bbutton\b[^"]*"[^>]*>.*?</a>#s', function ($match) use (&$actions) {
            $actions[] = $match[0];
            return '';
        }, $html);
        $items[] = array('html' => trim($html), 'attr' => wc_get_notice_data_attr($notice));
    }
    ?>
    <div class="banner banner--<?php echo esc_attr($intent); ?> prose-end <?php echo esc_attr($extra_class); ?>">
        <span class="icon-box" aria-hidden="true"><svg class="icon" width="1em" height="1em" aria-hidden="true"
                focusable="false">
                <use href="#<?php echo esc_attr($icon); ?>" />
            </svg></span>
        <div class="banner__body">
            <?php if (count($items) === 1): ?>
                <p class="text" <?php echo $items[0]['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo $items[0]['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
            <?php else: ?>
                <ul class="list">
                    <?php foreach ($items as $item): ?>
                        <li class="text" <?php echo $item['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo $item['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if ($actions): ?>
                <div class="cluster mt-sm">
                    <?php echo implode('', $actions); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// WooCommerce filters the whole notice output, Banner and all: let the
// Banner's sprite icon through
function dfh_notice_allowed_icon($tags)
{
    $tags['svg'] = array('class' => true, 'width' => true, 'height' => true, 'aria-hidden' => true, 'focusable' => true);
    $tags['use'] = array('href' => true);
    return $tags;
}
add_filter('woocommerce_kses_notice_allowed_tags', 'dfh_notice_allowed_icon');

/**
 * WooCommerce's account tables (orders, downloads, payment methods, an
 * order's details, there and on the order received page) as the library's Table, each in a labelled container
 * that scrolls on a narrow screen. After the shortcodes run (priority 11).
 */
function dfh_woocommerce_tables($content)
{
    if (!function_exists('is_account_page') || !(is_account_page() || is_order_received_page())) {
        return $content;
    }
    $labels = array(
        'woocommerce-orders-table' => __('Orders', 'woocommerce'),
        'woocommerce-MyAccount-paymentMethods' => __('Payment methods', 'woocommerce'),
        'woocommerce-table--order-downloads' => __('Downloads', 'woocommerce'),
        'woocommerce-table--order-details' => __('Order details', 'woocommerce'),
    );
    return preg_replace_callback('#<table class="([^"]*\bshop_table\b[^"]*)"(.*?)</table>#s', function ($match) use ($labels) {
        $label = __('Table', 'dfh');
        foreach ($labels as $class => $text) {
            if (str_contains($match[1], $class)) {
                $label = $text;
            }
        }
        return '<div class="table-container" tabindex="0" role="region" aria-label="' . esc_attr($label) . '">'
            . '<table class="table ' . $match[1] . '"' . $match[2] . '</table></div>';
    }, $content);
}
add_filter('the_content', 'dfh_woocommerce_tables', 20);

/**
 * The checkout's order review as the library's Table. It sits in the order
 * Card, which is narrow enough without a scrolling container, and
 * WooCommerce's script replaces it whole as a fragment.
 */
function dfh_review_order_table($html)
{
    return str_replace('<table class="shop_table ', '<table class="table table--plain shop_table ', $html);
}

function dfh_review_order_fragment($fragments)
{
    if (isset($fragments['.woocommerce-checkout-review-order-table'])) {
        $fragments['.woocommerce-checkout-review-order-table'] = dfh_review_order_table($fragments['.woocommerce-checkout-review-order-table']);
    }
    return $fragments;
}
add_filter('woocommerce_update_order_review_fragments', 'dfh_review_order_fragment');

/**
 * WooCommerce's saved cards ("Visa ending in 4242", "Use a new payment
 * method") and its "Save to account" checkbox as the library's Choice, the
 * list without bullets. Each input stays beside its label (see
 * woocommerce/checkout/payment-method.php). Its script finds them by
 * their classes, which stay.
 */
function dfh_payment_choices($html)
{
    $html = preg_replace_callback('#<input([^>]*)/>\s*<label for="([^"]+)"[^>]*>(.*?)</label>#s', function ($match) {
        $input = str_replace(' style="width:auto;"', '', $match[1]);
        $control = str_contains($input, 'type="checkbox"') ? 'checkbox-input' : 'radio-input';
        $input = str_contains($input, 'class="')
            ? str_replace('class="', 'class="' . $control . ' ', $input)
            : $input . ' class="' . $control . '"';
        return '<div class="choice"><input' . $input . ' /><label for="' . $match[2] . '">' . $match[3] . '</label></div>';
    }, $html);
    return str_replace('<ul class="woocommerce-SavedPaymentMethods ', '<ul role="list" class="stack woocommerce-SavedPaymentMethods ', $html);
}
add_filter('wc_payment_gateway_form_saved_payment_methods_html', 'dfh_payment_choices');
add_filter('woocommerce_payment_gateway_save_new_payment_method_option_html', 'dfh_payment_choices');

// A course sells one place at a time, so the cart has no quantities
add_filter('woocommerce_is_sold_individually', '__return_true');

// One place per course, so the order review leaves out the "× 1"
add_filter('woocommerce_checkout_cart_item_quantity', '__return_empty_string');

// Nothing about a course needs a note with the order
add_filter('woocommerce_enable_order_notes_field', '__return_false');

// The shop lists a handful of courses: no count or sort menu above them
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

// The footnotes list takes the library's small text
function dfh_footnotes_small_text($content)
{
    $tags = new WP_HTML_Tag_Processor($content);
    if ($tags->next_tag('ol')) {
        $tags->add_class('small-text');
    }
    return $tags->get_updated_html();
}
add_filter('render_block_core/footnotes', 'dfh_footnotes_small_text');

/**
 * Automatically complete orders for virtual products.
 */
function dfh_auto_complete_virtual_orders($order_id)
{
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    // Only affect orders that are currently processing
    if ('processing' !== $order->get_status()) {
        return;
    }

    $has_downloadable_or_virtual = false;

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if ($product && ($product->is_virtual() || $product->is_downloadable())) {
            $has_downloadable_or_virtual = true;
            break;
        }
    }

    if ($has_downloadable_or_virtual) {
        $order->update_status('completed');
    }
}
add_action('woocommerce_thankyou', 'dfh_auto_complete_virtual_orders');
add_action('woocommerce_payment_complete', 'dfh_auto_complete_virtual_orders');

/**
 * Add a "Return to Course" link on the WooCommerce Order Received (Thank You) page.
 */
function dfh_add_return_to_course_link($order_id)
{
    // Convert order ID to order object
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    // Find if the order contains a course product
    $course_url = home_url(); // Fallback to home

    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $course = dfh_course_for_product($product_id);
        if ($course) {
            $course_url = get_permalink($course);
            break;
        }
    }
    ?>
    <p class="woocommerce-order-course-return text-center my-cushion">
        <a href="<?php echo esc_url($course_url); ?>" class="button button--strong button--accent">
            <span>Go to your course</span>
            <svg class="icon icon--dir" width="1em" height="1em" aria-hidden="true" focusable="false">
                <use href="#ArrowRight" />
            </svg>
        </a>
    </p>
    <?php
}
add_action('woocommerce_thankyou', 'dfh_add_return_to_course_link', 20);

/**
 * Register Downloadable Resources Custom Post Type.
 */
function dfh_register_download_post_type()
{
    register_post_type('download', array(
        'labels' => array(
            'name' => __('Downloads', 'dfh'),
            'singular_name' => __('Download', 'dfh'),
            'add_new_item' => __('Add New Download', 'dfh'),
        ),
        'public' => true,
        'has_archive' => false,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest' => true, // Enables the Gutenberg block editor
        'menu_icon' => 'dashicons-media-document',
    ));
}
add_action('init', 'dfh_register_download_post_type');

function dfh_register_download_file_meta_box()
{
    add_meta_box(
        'dfh_download_file_box',
        __('PDF Attachment', 'dfh'),
        'dfh_render_download_file_meta_box',
        'download',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'dfh_register_download_file_meta_box');

function dfh_render_download_file_meta_box($post)
{
    wp_nonce_field('dfh_save_download_file', 'dfh_download_file_nonce');

    // We store the Attachment ID instead of a raw URL
    $attachment_id = get_post_meta($post->ID, '_dfh_download_attachment_id', true);
    $pdf_url = $attachment_id ? wp_get_attachment_url($attachment_id) : '';
    ?>
    <div class="dfh-media-upload-wrapper">
        <input type="hidden" id="dfh_download_attachment_id" name="dfh_download_attachment_id"
            value="<?php echo esc_attr($attachment_id); ?>" />

        <div id="dfh-pdf-preview" style="margin-bottom: 1rem; font-weight: 500;">
            <?php echo $pdf_url ? 'Selected File: <a href="' . esc_url($pdf_url) . '" target="_blank">' . esc_url($pdf_url) . '</a>' : 'No PDF selected.'; ?>
        </div>

        <button type="button" class="button" id="dfh_upload_pdf_button">Select or Upload PDF</button>
        <button type="button" class="button" id="dfh_remove_pdf_button"
            style="color: #a00; <?php echo $pdf_url ? '' : 'display:none;'; ?>">Remove PDF</button>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            var file_frame;
            $('#dfh_upload_pdf_button').on('click', function (e) {
                e.preventDefault();
                if (file_frame) {
                    file_frame.open();
                    return;
                }
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: 'Select PDF for Download',
                    button: { text: 'Use this PDF' },
                    library: { type: 'application/pdf' },
                    multiple: false
                });
                file_frame.on('select', function () {
                    var attachment = file_frame.state().get('selection').first().toJSON();
                    $('#dfh_download_attachment_id').val(attachment.id);
                    $('#dfh-pdf-preview').html('Selected File: <a href="' + attachment.url + '" target="_blank">' + attachment.url + '</a>');
                    $('#dfh_remove_pdf_button').show();
                });
                file_frame.open();
            });

            $('#dfh_remove_pdf_button').on('click', function (e) {
                e.preventDefault();
                $('#dfh_download_attachment_id').val('');
                $('#dfh-pdf-preview').text('No PDF selected.');
                $(this).hide();
            });
        });
    </script>
    <?php
}

function dfh_save_download_file($post_id)
{
    if (!isset($_POST['dfh_download_file_nonce']) || !wp_verify_nonce($_POST['dfh_download_file_nonce'], 'dfh_save_download_file')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!current_user_can('edit_post', $post_id))
        return;

    if (isset($_POST['dfh_download_attachment_id'])) {
        $attachment_id = absint($_POST['dfh_download_attachment_id']);
        if ($attachment_id) {
            update_post_meta($post_id, '_dfh_download_attachment_id', $attachment_id);
        } else {
            delete_post_meta($post_id, '_dfh_download_attachment_id');
        }
    }
}
add_action('save_post_download', 'dfh_save_download_file');

function dfh_enqueue_admin_media_scripts($hook)
{
    global $post;
    if (($hook == 'post.new.php' || $hook == 'post.php') && 'download' === $post->post_type) {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'dfh_enqueue_admin_media_scripts');

/**
 * Register a meta box on the Lesson post type to link a Download resource.
 */
function dfh_register_lesson_download_meta_box()
{
    add_meta_box(
        'dfh_lesson_download_box',
        __('Linked Download Resource', 'dfh'),
        'dfh_render_lesson_download_meta_box',
        'lesson', // Adjust if your lesson post type slug is different
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'dfh_register_lesson_download_meta_box');

/**
 * Render the dropdown selector of published Download posts.
 */
function dfh_render_lesson_download_meta_box($post)
{
    wp_nonce_field('dfh_save_lesson_download', 'dfh_lesson_download_nonce');

    // Retrieve currently linked download IDs (stored as an array)
    $linked_download_ids = get_post_meta($post->ID, '_dfh_linked_download_ids', true);
    if (!is_array($linked_download_ids)) {
        $linked_download_ids = $linked_download_ids ? array($linked_download_ids) : array();
    }

    // Fetch all published download posts
    $downloads = get_posts(array(
        'post_type' => 'download',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));
    ?>
    <p>
        <label for="dfh_linked_download_ids">Select resources for this lesson (hold Cmd/Ctrl to select multiple):</label>
    </p>
    <p>
        <select name="dfh_linked_download_ids[]" id="dfh_linked_download_ids" multiple style="width: 100%; height: 120px;">
            <?php foreach ($downloads as $download): ?>
                <option value="<?php echo esc_attr($download->ID); ?>" <?php selected(in_array($download->ID, $linked_download_ids)); ?>>
                    <?php echo esc_html($download->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}

function dfh_save_lesson_download($post_id)
{
    if (!isset($_POST['dfh_lesson_download_nonce']) || !wp_verify_nonce($_POST['dfh_lesson_download_nonce'], 'dfh_save_lesson_download')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!current_user_can('edit_post', $post_id))
        return;

    if (isset($_POST['dfh_linked_download_ids']) && is_array($_POST['dfh_linked_download_ids'])) {
        $download_ids = array_map('absint', $_POST['dfh_linked_download_ids']);
        update_post_meta($post_id, '_dfh_linked_download_ids', $download_ids);
    } else {
        delete_post_meta($post_id, '_dfh_linked_download_ids');
    }
}
add_action('save_post_lesson', 'dfh_save_lesson_download');

/**
 * Handle certificate generation and download request.
 */
function dfh_handle_certificate_download()
{
    if (isset($_GET['action']) && 'download_certificate' === $_GET['action']) {
        $course_id = isset($_GET['course_id']) ? absint($_GET['course_id']) : 0;
        $user_id = get_current_user_id();

        // Security validation
        if (!$user_id || !wp_verify_nonce($_GET['nonce'], 'dfh_cert_' . $course_id)) {
            wp_die('Access denied or security check failed.', 'Error', array('response' => 403));
        }

        // Verify user actually completed the course
        $root_lessons = function_exists('get_field') ? get_field('course_root_lessons', $course_id) : get_post_meta($course_id, 'course_root_lessons', true);
        if (!is_array($root_lessons)) {
            $root_lessons = empty($root_lessons) ? array() : array($root_lessons);
        }
        $all_lessons = array();
        foreach ($root_lessons as $root_lesson) {
            $current_root_id = is_object($root_lesson) ? (int) $root_lesson->ID : (int) $root_lesson;
            if ($current_root_id) {
                $all_lessons[] = $current_root_id;
                $all_lessons = array_merge($all_lessons, dfh_get_ordered_lesson_tree($current_root_id));
            }
        }
        $all_lessons = array_values(array_unique(array_map('intval', $all_lessons)));
        $completed_lessons = dfh_get_completed_lessons($user_id);
        $completed_count = count(array_intersect(array_map('intval', $completed_lessons), $all_lessons));

        if ($completed_count < count($all_lessons) || count($all_lessons) === 0) {
            wp_die('You must complete all lessons before downloading your certificate.', 'Incomplete', array('response' => 403));
        }

        $user = get_userdata($user_id);
        // Use custom student name, fallback to display_name, then user_login
        $student_name = get_user_meta($user_id, 'dfh_student_name', true);
        if (empty($student_name)) {
            $student_name = $user->display_name ? $user->display_name : $user->user_login;
        }
        $course_title = get_the_title($course_id);
        $completion_date = date_i18n(get_option('date_format'));

        // Render a clean, print-ready certificate markup that automatically triggers print-to-PDF dialog
        ?>
        <!doctype html>
        <html lang="en">

        <head>
            <meta charset="UTF-8" />
            <title>
                Certificate of Completion - <?php echo esc_html($course_title); ?>
            </title>
            <link rel="stylesheet" href="https://use.typekit.net/lrj6kun.css" />
            <link rel="stylesheet" href="<?php echo esc_url(dfh_library_css('dfh-components.css')); ?>" />
            <link rel="stylesheet" href="<?php echo esc_url(dfh_library_css('themes/dfh.css')); ?>" />
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    font-family: var(--typography-font-family-sans, sans-serif);
                    background-image: repeating-linear-gradient(45deg,
                            var(--color-neutral-000),
                            var(--color-neutral-000) 1cm,
                            var(--color-neutral-200) 1cm,
                            var(--color-neutral-200) 2cm);
                    color: var(--color-neutral-900);
                    display: flex;
                    flex-direction: column;
                    gap: 5cm;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    padding: 1cm;
                }

                .certificate-wrapper {
                    width: 1000px;
                    padding: 4rem;
                    border: 8px solid var(--color-neutral-900);
                    text-align: center;
                    background: #fff;
                    box-sizing: border-box;
                }

                h1 {
                    font-family: var(--typography-font-family-display, sans-serif);
                    font-size: 3.5rem;
                    text-transform: uppercase;
                    letter-spacing: -0.05em;
                    line-height: 1;
                    margin-bottom: 0.5rem;
                }

                h2 {
                    font-size: 1.5rem;
                    font-weight: normal;
                    margin-bottom: 2rem;
                    color: #555;
                }

                .student-name {
                    font-family: var(--typography-font-family-mono, monospace);
                    font-size: 2.2rem;
                    font-weight: bold;
                    border-bottom: 2px solid var(--color-neutral-900);
                    display: inline-block;
                    padding: 0 2rem 0.5rem;
                    margin: 1.5rem 0;
                }

                .course-title {
                    font-family: var(--typography-font-family-mono, monospace);
                    font-size: 1.8rem;
                    font-weight: bold;
                    margin: 1rem 0 2rem;
                }

                .meta-footer {
                    display: flex;
                    justify-content: space-between;
                    margin-top: 4rem;
                    font-size: 1rem;
                    border-top: 1px solid var(--color-neutral-600);
                    padding-top: 1.5rem;
                }

                .logo {
                    border: 0.13em solid currentColor;
                    display: inline-block;
                    font-family: var(--typography-font-family-display, sans-serif);
                    font-size: 2rem;
                    letter-spacing: var(--display-title-letter-spacing);
                    line-height: 1;
                    padding: 0.169em 0.13em;
                    text-box: trim-both cap alphabetic;
                    text-transform: uppercase;
                    word-spacing: var(--display-title-word-spacing);
                }

                @media print {
                    .no-print {
                        display: none;
                    }

                    .certificate-wrapper {
                        border: 4px solid var(--color-neutral-900);
                        max-width: 100%;
                    }
                }
            </style>
        </head>

        <body onload="window.print();">
            <div class="certificate-wrapper">
                <h1>Certificate of Completion</h1>
                <h2>This is proudly presented to</h2>
                <div class="student-name"><?php echo esc_html($student_name); ?></div>
                <p>for successfully completing the course requirements for</p>
                <div class="course-title"><?php echo esc_html($course_title); ?></div>

                <div class="meta-footer">
                    <div>
                        <strong>Date:</strong>
                        <?php echo esc_html($completion_date); ?>
                    </div>
                    <div><strong>Design for Humans</strong></div>
                </div>
            </div>
            <span class="logo">Design for Humans</span>
        </body>

        </html>

        <?php
        exit;
    }
}
add_action('init', 'dfh_handle_certificate_download');

/**
 * Register Sample Lesson Custom Post Type.
 */
function dfh_register_sample_lesson_post_type()
{
    register_post_type('sample-lesson', array(
        'labels' => array(
            'name' => __('Sample Lessons', 'dfh'),
            'singular_name' => __('Sample Lesson', 'dfh'),
            'add_new_item' => __('Add New Sample Lesson', 'dfh'),
        ),
        'public' => true,
        'has_archive' => false,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'), // Custom fields enable the Footnotes block.
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-welcome-view-site',
        'rewrite' => array('slug' => 'sample-lesson'),
    ));
}
add_action('init', 'dfh_register_sample_lesson_post_type');

function dfh_register_course_sample_meta_box()
{
    add_meta_box(
        'dfh_course_sample_box',
        __('Featured Sample Lesson', 'dfh'),
        'dfh_render_course_sample_meta_box',
        'course',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'dfh_register_course_sample_meta_box');

function dfh_render_course_sample_meta_box($post)
{
    wp_nonce_field('dfh_save_course_sample', 'dfh_course_sample_nonce');
    $linked_sample_id = get_post_meta($post->ID, '_dfh_linked_sample_id', true);

    $samples = get_posts(array(
        'post_type' => 'sample-lesson',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));
    ?>
    <p>
        <select name="dfh_linked_sample_id" style="width: 100%;">
            <option value="">— None —</option>
            <?php foreach ($samples as $sample): ?>
                <option value="<?php echo esc_attr($sample->ID); ?>" <?php selected($linked_sample_id, $sample->ID); ?>>
                    <?php echo esc_html($sample->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}

function dfh_save_course_sample($post_id)
{
    if (!isset($_POST['dfh_course_sample_nonce']) || !wp_verify_nonce($_POST['dfh_course_sample_nonce'], 'dfh_save_course_sample'))
        return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!current_user_can('edit_post', $post_id))
        return;

    if (isset($_POST['dfh_linked_sample_id'])) {
        $sample_id = absint($_POST['dfh_linked_sample_id']);
        if ($sample_id) {
            update_post_meta($post_id, '_dfh_linked_sample_id', $sample_id);
        } else {
            delete_post_meta($post_id, '_dfh_linked_sample_id');
        }
    }
}
add_action('save_post_course', 'dfh_save_course_sample');

/**
 * Register Testimonial Custom Post Type.
 */
function dfh_register_testimonial_post_type()
{
    register_post_type('testimonial', array(
        'labels' => array(
            'name' => __('Testimonials', 'dfh'),
            'singular_name' => __('Testimonial', 'dfh'),
            'add_new_item' => __('Add New Testimonial', 'dfh'),
        ),
        'public' => false, // We only need them for backend management and frontend display, no single archive page needed
        'show_ui' => true,
        'has_archive' => false,
        'supports' => array('title', 'editor'), // Title = Student Name, Editor = Testimonial Quote
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-testimonial',
    ));
}

add_action('init', 'dfh_register_testimonial_post_type');
function dfh_register_testimonial_meta_box()
{
    add_meta_box(
        'dfh_testimonial_details_box',
        __('Student Details', 'dfh'),
        'dfh_render_testimonial_meta_box',
        'testimonial',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'dfh_register_testimonial_meta_box');


function dfh_render_testimonial_meta_box($post)
{
    wp_nonce_field('dfh_save_testimonial_meta', 'dfh_testimonial_nonce');
    $author_title = get_post_meta($post->ID, '_dfh_testimonial_author_title', true);
    $author_url = get_post_meta($post->ID, '_dfh_testimonial_author_url', true);
    ?>
    <p>
        <label for="dfh_testimonial_author_title"><strong>Author Title / Role / Company:</strong></label><br />
        <input type="text" id="dfh_testimonial_author_title" name="dfh_testimonial_author_title"
            value="<?php echo esc_attr($author_title); ?>" style="width: 100%; margin-top: 5px;"
            placeholder="e.g. Senior Frontend Developer" />
    </p>
    <p style="margin-top: 15px;">
        <label for="dfh_testimonial_author_url"><strong>Website / Portfolio URL (Optional):</strong></label><br />
        <input type="url" id="dfh_testimonial_author_url" name="dfh_testimonial_author_url"
            value="<?php echo esc_attr($author_url); ?>" style="width: 100%; margin-top: 5px;"
            placeholder="https://example.com" />
    </p>
    <?php
}

function dfh_save_testimonial_meta($post_id)
{
    if (!isset($_POST['dfh_testimonial_nonce']) || !wp_verify_nonce($_POST['dfh_testimonial_nonce'], 'dfh_save_testimonial_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!current_user_can('edit_post', $post_id))
        return;

    if (isset($_POST['dfh_testimonial_author_title'])) {
        update_post_meta($post_id, '_dfh_testimonial_author_title', sanitize_text_field($_POST['dfh_testimonial_author_title']));
    }
    if (isset($_POST['dfh_testimonial_author_url'])) {
        update_post_meta($post_id, '_dfh_testimonial_author_url', esc_url_raw($_POST['dfh_testimonial_author_url']));
    }
}
add_action('save_post_testimonial', 'dfh_save_testimonial_meta');

/**
 * Logged out, My account's front page (WooCommerce's login form) sends the
 * visitor to the course's own login page, and back again after. Its lost and
 * reset password sections stay. WooCommerce handles a posted login before
 * this runs, so only page loads are sent on.
 */
function dfh_account_login_redirect()
{
    if ('GET' === $_SERVER['REQUEST_METHOD'] && !is_user_logged_in() && function_exists('is_account_page') && is_account_page() && !is_wc_endpoint_url()) {
        wp_safe_redirect(add_query_arg('redirect_to', rawurlencode(wc_get_page_permalink('myaccount')), home_url('/login/')));
        exit;
    }
}
add_action('template_redirect', 'dfh_account_login_redirect');
