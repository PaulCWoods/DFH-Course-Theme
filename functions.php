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
        'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'page-attributes'), // page-attributes adds Parent Lesson dropdown
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

function dfh_render_lesson_children($parent_id, $level = 1, $active_lesson = null)
{
    $children = get_posts(array(
        'post_type' => 'lesson',
        'post_parent' => $parent_id,
        'posts_per_page' => -1,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
    ));

    if (!$children)
        return '';

    if (null === $active_lesson) {
        $active_lesson = function_exists('dfh_get_student_current_lesson') ? dfh_get_student_current_lesson() : null;
    }

    function_exists('dfh_is_lesson_completed') ? '' : null;

    $lvl = intval($level);
    $output = '<ul class="lesson-list l' . $lvl . '">';
    foreach ($children as $child) {
        $is_current = ($child->ID === get_the_ID());
        $lesson_item_class = $is_current ? ' class="current lesson-item l' . $lvl . '"' : ' class="lesson-item l' . $lvl . '"';
        $code = dfh_get_lesson_hierarchy_number($child->ID);
        $code_html = $code ? '<span class="lesson-item__code">' . esc_html($code) . '</span> ' : '';
        $data_lesson = $code ? $code : (string) $child->ID;

        $completed = function_exists('dfh_is_lesson_completed') ? dfh_is_lesson_completed($child->ID) : false;
        $bookmarked = function_exists('dfh_is_lesson_bookmarked') ? dfh_is_lesson_bookmarked($child->ID) : false;
        $bookmark_html = $bookmarked ? '<span class="lesson-item__bookmark" title="Bookmarked by you"><svg class="icon" width="32" height="32" aria-hidden="true"><use href="#Bookmarked" /></svg><span class="sr">Bookmarked</span></span>' : '';
        // A lesson is considered "started" if it's completed or it's the user's current active lesson.
        $started = $completed || ($active_lesson && ((int) $active_lesson === (int) $child->ID));

        if (!$started) {
            // Not started: render as non-clickable span and omit the progress chip.
            $output .= '<li class="lesson-list__item l' . $lvl . '" data-lesson="' . esc_attr($data_lesson) . '"><span class="lesson-item l' . $lvl . '">' . $code_html . '<span class="lesson-item__label l' . $lvl . '">' . esc_html(get_the_title($child->ID)) . '</span>' . $bookmark_html . '</span>';
        } else {
            $chip_label = $completed ? 'Complete' : 'In Progress';
            $chip_class = $completed ? 'chip complete' : 'chip in-progress';
            $output .= '<li class="lesson-list__item l' . $lvl . '" data-lesson="' . esc_attr($data_lesson) . '"><span ' . $lesson_item_class . '>' . $code_html . '<a href="' . esc_url(get_permalink($child->ID)) . '" class="lesson-item__label link l' . $lvl . '">' . esc_html(get_the_title($child->ID)) . '</a>' . $bookmark_html . '<span class="' . $chip_class . '">' . esc_html($chip_label) . '</span></span>';
        }
        $output .= dfh_render_lesson_children($child->ID, $lvl + 1, $active_lesson);
        $output .= '</li>';
    }
    $output .= '</ul>';

    return $output;
}

function dfh_render_lesson_tree($roots = null, $level = 1, $active_lesson = null)
{
    if (empty($roots)) {
        return dfh_render_lesson_children(0, $level, $active_lesson);
    }

    if (!is_array($roots)) {
        $roots = array($roots);
    }

    $root_ids = array();
    foreach ($roots as $r) {
        $id = is_object($r) ? (int) $r->ID : (int) $r;
        if ($id)
            $root_ids[] = $id;
    }
    $root_ids = array_values(array_unique($root_ids));

    $filtered_roots = array();
    foreach ($root_ids as $id) {
        $ancestors = get_post_ancestors($id);
        $is_descendant = false;
        foreach ($root_ids as $other) {
            if ($other === $id)
                continue;
            if (in_array($other, $ancestors)) {
                $is_descendant = true;
                break;
            }
        }
        if (!$is_descendant)
            $filtered_roots[] = $id;
    }

    $lvl = intval($level);
    if (null === $active_lesson) {
        $active_lesson = function_exists('dfh_get_student_current_lesson') ? dfh_get_student_current_lesson() : null;
    }
    $output = '<ul class="lesson-list l' . $lvl . '">';
    foreach ($filtered_roots as $r_id) {
        $is_current = ($r_id === get_the_ID());
        $lesson_item_class = $is_current ? ' class="current lesson-item l' . $lvl . '"' : ' class="lesson-item l' . $lvl . '"';
        $code = dfh_get_lesson_hierarchy_number($r_id);
        $code_html = $code ? '<span class="lesson-item__code">' . esc_html($code) . '</span> ' : '';
        $data_lesson = $code ? $code : (string) $r_id;

        $completed = function_exists('dfh_is_lesson_completed') ? dfh_is_lesson_completed($r_id) : false;
        $bookmarked_root = function_exists('dfh_is_lesson_bookmarked') ? dfh_is_lesson_bookmarked($r_id) : false;
        $bookmark_html_root = $bookmarked_root ? '<span class="lesson-item__bookmark" title="Bookmarked by you"><svg class="icon" width="32" height="32" aria-hidden="true"><use href="#Bookmarked" /></svg><span class="sr">Bookmarked</span></span>' : '';
        // A lesson is considered "started" if it's completed or it's the user's current active lesson.
        $started = $completed || ($active_lesson && ((int) $active_lesson === (int) $r_id));

        if (!$started) {
            // Not started: render as non-clickable span and omit the progress chip.
            $output .= '<li class="lesson-list__item l' . $lvl . '" data-lesson="' . esc_attr($data_lesson) . '"><span class="lesson-item l' . $lvl . '">' . $code_html . '<span class="lesson-item__label l' . $lvl . '">' . esc_html(get_the_title($r_id)) . '</span>' . $bookmark_html_root . '</span>';
        } else {
            $chip_label = $completed ? 'Complete' : 'In Progress';
            $chip_class = $completed ? 'chip complete' : 'chip in-progress';
            $output .= '<li class="lesson-list__item l' . $lvl . '" data-lesson="' . esc_attr($data_lesson) . '"><span ' . $lesson_item_class . '>' . $code_html . '<a href="' . esc_url(get_permalink($r_id)) . '" class="lesson-item__label link l' . $lvl . '">' . esc_html(get_the_title($r_id)) . '</a>' . $bookmark_html_root . '<span class="' . $chip_class . '">' . esc_html($chip_label) . '</span></span>';
        }
        $output .= dfh_render_lesson_children($r_id, $lvl + 1, $active_lesson);
        $output .= '</li>';
    }
    $output .= '</ul>';

    return $output;
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
 * 4. Helper Function: Check if User Has Access (With Admin Safety Override)
 */
function dfh_user_has_course_access($user_id = 0)
{
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
function dfh_grant_course_access_on_purchase( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    $user_id = $order->get_user_id();
    if ( ! $user_id ) {
        return; 
    }

    foreach ( $order->get_items() as $item ) {
        $product_id = $item->get_product_id();

        // Check if this product is linked to any course
        $courses = get_posts( array(
            'post_type'   => 'course',
            'meta_key'    => '_dfh_product_id',
            'meta_value'  => $product_id,
            'numberposts' => 1,
        ) );

        if ( ! empty( $courses ) ) {
            update_user_meta( $user_id, 'dfh_course_enrolled', '1' );
        }
    }
}
// Listen to both processing and completed states so the user gets instant access
add_action( 'woocommerce_order_status_processing', 'dfh_grant_course_access_on_purchase' );
add_action( 'woocommerce_order_status_completed', 'dfh_grant_course_access_on_purchase' );

/**
 * Completely disable WooCommerce default stylesheets.
 */
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

/**
 * Automatically complete orders for virtual products.
 */
function dfh_auto_complete_virtual_orders( $order_id ) {
    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    // Only affect orders that are currently processing
    if ( 'processing' !== $order->get_status() ) {
        return;
    }

    $has_downloadable_or_virtual = false;

    foreach ( $order->get_items() as $item ) {
        $product = $item->get_product();
        if ( $product && ( $product->is_virtual() || $product->is_downloadable() ) ) {
            $has_downloadable_or_virtual = true;
            break;
        }
    }

    if ( $has_downloadable_or_virtual ) {
        $order->update_status( 'completed' );
    }
}
add_action( 'woocommerce_thankyou', 'dfh_auto_complete_virtual_orders' );
add_action( 'woocommerce_payment_complete', 'dfh_auto_complete_virtual_orders' );

/**
 * Add a "Return to Course" link on the WooCommerce Order Received (Thank You) page.
 */
function dfh_add_return_to_course_link( $order_id ) {
    // Convert order ID to order object
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }
    
    // Find if the order contains a course product
    $course_url = home_url(); // Fallback to home
    
    foreach ( $order->get_items() as $item ) {
        $product_id = $item->get_product_id();
        $courses = get_posts( array(
            'post_type'   => 'course',
            'meta_key'    => '_dfh_product_id',
            'meta_value'  => $product_id,
            'numberposts' => 1,
        ) );
        if ( ! empty( $courses ) ) {
            $course_url = get_permalink( $courses[0]->ID );
            break;
        }
    }
    ?>
    <div class="woocommerce-order-course-return" style="margin: 2rem 0; text-align: center;">
        <a href="<?php echo esc_url( $course_url ); ?>" class="button +strong">
            Go to Your Course
            <svg class="icon dir" width="32" height="32" aria-hidden="true" style="margin-left: 0.5rem; vertical-align: middle;">
                <use href="#ArrowRight" />
            </svg>
        </a>
    </div>
    <?php
}
add_action( 'woocommerce_thankyou', 'dfh_add_return_to_course_link', 20 );

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
            <link rel="stylesheet" href="https://designforhumans.blog/styles/css/dfh-shared.css" />
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    font-family: var(--typography-font-family-sans, sans-serif);
                    background-image: repeating-linear-gradient(45deg,
                            var(--color-muted-100),
                            var(--color-muted-100) 1cm,
                            var(--color-muted-200) 1cm,
                            var(--color-muted-200) 2cm);
                    color: var(--color-muted-900);
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
                    border: 8px solid var(--color-muted-900);
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
                    border-bottom: 2px solid var(--color-muted-900);
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
                    border-top: 1px solid var(--color-muted-600);
                    padding-top: 1.5rem;
                }

                .logo {
                    border: 0.13em solid currentColor;
                    display: inline-block;
                    font-family: var(--typography-font-family-display, sans-serif);
                    font-size: 2rem;
                    letter-spacing: var(--ui-typography-display-title-letter-spacing);
                    line-height: 1;
                    padding: 0.169em 0.13em;
                    text-box: trim-both cap alphabetic;
                    text-transform: uppercase;
                    word-spacing: var(--ui-typography-display-title-word-spacing);
                }

                @media print {
                    .no-print {
                        display: none;
                    }

                    .certificate-wrapper {
                        border: 4px solid var(--color-muted-900);
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
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
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