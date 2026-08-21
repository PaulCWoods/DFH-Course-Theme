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

// 1. Mux Meta Box Registration
function dfh_add_lesson_mux_meta_box()
{
    add_meta_box(
        'dfh_mux_playback_id_box',
        'Mux Video Settings',
        'dfh_render_mux_meta_box_html',
        'lesson',
        'normal',
        'high'
    );
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
        <p style="font-size: 12px; color: #666; margin-top: 5px;">Paste the playback ID generated from your Mux dashboard for this lesson.</p>
    </div>
    <?php
}

// 2. External Links Meta Box Registration
function dfh_add_lesson_links_meta_box()
{
    add_meta_box(
        'dfh_lesson_links_box',
        'Lesson External Links',
        'dfh_render_links_meta_box_html',
        'lesson',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'dfh_add_lesson_links_meta_box');

function dfh_render_links_meta_box_html($post)
{
    wp_nonce_field('dfh_save_links_meta', 'dfh_links_nonce');
    $external_links = get_post_meta($post->ID, 'lesson_external_links', true);
    ?>
    <div style="margin-bottom: 10px;">
        <label style="display: block; font-weight: 600; margin-bottom: 5px;">External Links (One per line: Title | URL):</label>
        <textarea name="lesson_external_links" rows="4" style="width: 100%; padding: 8px;"
            placeholder="W3C Accessibility Guidelines | https://www.w3.org/WAI/standards-guidelines/&#10;A11y Project Checklist | https://www.a11yproject.com/checklist/"><?php echo esc_textarea($external_links); ?></textarea>
        <p style="font-size: 12px; color: #666; margin-top: 5px;">Format each link as `Link Title | https://url` on a new line.</p>
    </div>
    <?php
}

// 3. Lesson Stats Meta Box Registration
function dfh_add_lesson_stats_meta_box()
{
    add_meta_box(
        'dfh_lesson_stats_box',
        'Lesson Stats',
        'dfh_render_stats_meta_box_html',
        'lesson',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'dfh_add_lesson_stats_meta_box');

function dfh_render_stats_meta_box_html($post)
{
    wp_nonce_field('dfh_save_stats_meta', 'dfh_stats_nonce');
    $stats = get_post_meta($post->ID, 'lesson_stats', true);
    ?>
    <div style="margin-bottom: 10px;">
        <label style="display: block; font-weight: 600; margin-bottom: 5px;">Lesson Stats (one per line: Label | Value):</label>
        <textarea name="lesson_stats" rows="4" style="width: 100%; padding: 8px;" placeholder="Students | 120"><?php echo esc_textarea($stats); ?></textarea>
        <p style="font-size: 12px; color: #666; margin-top: 5px;">Each line should contain a label, a pipe, then the number/value.</p>
    </div>
    <?php
}

/**
 * Unified Save Routine for Lesson Meta Boxes (Mux, Links, Stats, Downloads)
 */
function dfh_save_lesson_meta_boxes($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
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

    // Save Downloads Placeholder
    if (isset($_POST['dfh_downloads_nonce']) && wp_verify_nonce($_POST['dfh_downloads_nonce'], 'dfh_save_downloads_meta')) {
        if (isset($_POST['lesson_downloads'])) {
            update_post_meta($post_id, 'lesson_downloads', sanitize_textarea_field($_POST['lesson_downloads']));
        }
    }
}
add_action('save_post', 'dfh_save_lesson_meta_boxes');

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
function dfh_get_ordered_lesson_tree( $parent_id = 0, &$flattened = array() ) {
    $args = array(
        'post_type'      => 'lesson',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
        'post_parent'    => $parent_id,
    );
    
    $children = get_posts( $args );

    foreach ( $children as $child ) {
        $flattened[] = $child->ID;
        dfh_get_ordered_lesson_tree( $child->ID, $flattened );
    }

    return $flattened;
}

/**
 * Find the Next and Previous lesson relative to a current lesson ID in the tree
 */
function dfh_get_adjacent_lesson( $current_lesson_id ) {
    $all_lessons = dfh_get_ordered_lesson_tree( 0 );
    $currentIndex = array_search( (int) $current_lesson_id, array_map( 'intval', $all_lessons ), true );

    $result = array(
        'next'     => null,
        'previous' => null,
    );

    if ( false !== $currentIndex ) {
        if ( isset( $all_lessons[$currentIndex + 1] ) ) {
            $result['next'] = $all_lessons[$currentIndex + 1];
        }
        if ( isset( $all_lessons[$currentIndex - 1] ) ) {
            $result['previous'] = $all_lessons[$currentIndex - 1];
        }
    }

    return $result;
}

function dfh_render_lesson_children($parent_id, $level = 1)
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

    function_exists('dfh_is_lesson_completed') ? '' : null;

    $lvl = intval($level);
    $output = '<ul class="lesson-list l' . $lvl . '">';
    foreach ($children as $child) {
        $is_current = ($child->ID === get_the_ID());
        $link_class = $is_current ? ' class="current lesson-item l' . $lvl . '"' : ' class="lesson-item l' . $lvl . '"';
        $code = dfh_get_lesson_hierarchy_number($child->ID);
        $code_html = $code ? '<span class="lesson-item-code">' . esc_html($code) . '</span> ' : '';
        $data_lesson = $code ? $code : (string) $child->ID;
        
        $completed = function_exists('dfh_is_lesson_completed') ? dfh_is_lesson_completed($child->ID) : false;
        $bookmarked = function_exists('dfh_is_lesson_bookmarked') ? dfh_is_lesson_bookmarked($child->ID) : false;
        $bookmark_html = $bookmarked ? '<span class="lesson-item-bookmark"><svg class="icon" width="32" height="32" title="Bookmarked by you" aria-hidden="true"><use href="#Bookmarked" /></svg><span class="sr">Bookmarked</span></span>' : '';
        $active = function_exists('dfh_get_student_current_lesson') ? dfh_get_student_current_lesson() : null;
        // A lesson is considered "started" if it's completed or it's the user's current active lesson.
        $started = $completed || ($active && ((int) $active === (int) $child->ID));

        if ( ! $started ) {
            // Not started: render as non-clickable span and omit the progress chip.
            $output .= '<li class="lesson-list-item l' . $lvl . '" data-lesson="' . esc_attr($data_lesson) . '"><span class="lesson-item l' . $lvl . '">' . $code_html . '<span class="lesson-item-label l' . $lvl . '">' . esc_html(get_the_title($child->ID)) . '</span>' . $bookmark_html . '</span>';
        } else {
            $chip_label = $completed ? 'Complete' : 'In Progress';
            $chip_class = $completed ? 'chip complete' : 'chip in-progress';
            $output .= '<li class="lesson-list-item l' . $lvl . '" data-lesson="' . esc_attr($data_lesson) . '"><a ' . $link_class . ' href="' . esc_url(get_permalink($child->ID)) . '">' . $code_html . '<span class="lesson-item-label l' . $lvl . '">' . esc_html(get_the_title($child->ID)) . '</span>' . $bookmark_html . '<span class="' . $chip_class . '">' . esc_html($chip_label) . '</span></a>';
        }
        $output .= dfh_render_lesson_children($child->ID, $lvl + 1);
        $output .= '</li>';
    }
    $output .= '</ul>';

    return $output;
}

function dfh_render_lesson_tree($roots = null, $level = 1)
{
    if (empty($roots)) {
        return dfh_render_lesson_children(0, $level);
    }

    if (!is_array($roots)) {
        $roots = array($roots);
    }

    $root_ids = array();
    foreach ($roots as $r) {
        $id = is_object($r) ? (int) $r->ID : (int) $r;
        if ($id) $root_ids[] = $id;
    }
    $root_ids = array_values(array_unique($root_ids));

    $filtered_roots = array();
    foreach ($root_ids as $id) {
        $ancestors = get_post_ancestors($id);
        $is_descendant = false;
        foreach ($root_ids as $other) {
            if ($other === $id) continue;
            if (in_array($other, $ancestors)) {
                $is_descendant = true;
                break;
            }
        }
        if (!$is_descendant) $filtered_roots[] = $id;
    }

    $lvl = intval($level);
    $output = '<ul class="lesson-list l' . $lvl . '">';
    foreach ($filtered_roots as $r_id) {
        $is_current = ($r_id === get_the_ID());
        $link_class = $is_current ? ' class="current lesson-item l' . $lvl . '"' : ' class="lesson-item l' . $lvl . '"';
        $code = dfh_get_lesson_hierarchy_number($r_id);
        $code_html = $code ? '<span class="lesson-item-code">' . esc_html($code) . '</span> ' : '';
        $data_lesson = $code ? $code : (string) $r_id;
        
        $bookmarked_root = function_exists('dfh_is_lesson_bookmarked') ? dfh_is_lesson_bookmarked($r_id) : false;
        $bookmark_html_root = $bookmarked_root ? '<span class="lesson-item-bookmark"><svg class="icon" width="32" height="32" title="Bookmarked by you" aria-hidden="true"><use href="#Bookmarked" /></svg><span class="sr">Bookmarked</span></span>' : '';
        $output .= '<li class="lesson-list-item l' . $lvl . '" data-lesson="' . esc_attr($data_lesson) . '"><a' . $link_class . ' href="' . esc_url(get_permalink($r_id)) . '">' . $code_html . '<span class="lesson-item-label l' . $lvl . '">' . esc_html(get_the_title($r_id)) . '</span>' . $bookmark_html_root . '</a>';
        $output .= dfh_render_lesson_children($r_id, $lvl + 1);
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
function dfh_add_student_role() {
    add_role(
        'course_student',
        __( 'Course Student', 'dfh' ),
        array(
            'read'         => true,
            'edit_posts'   => false,
            'upload_files' => false,
        )
    );
}
add_action( 'init', 'dfh_add_student_role' );

/**
 * 2. Add Manual Enrollment Checkbox to User Profile Admin Screen
 */
function dfh_show_extra_user_profile_fields( $user ) {
    if ( ! current_user_can( 'administrator' ) ) {
        return;
    }
    
    $is_enrolled = get_user_meta( $user->ID, 'dfh_course_enrolled', true );
    ?>
    <h3>Course Access Control</h3>
    <table class="form-table">
        <tr>
            <th><label for="dfh_course_enrolled">Course Enrollment</label></th>
            <td>
                <label>
                    <input type="checkbox" name="dfh_course_enrolled" id="dfh_course_enrolled" value="1" <?php checked( $is_enrolled, '1' ); ?>>
                    Grant access to Design for Humans course and lessons
                </label>
                <p class="description">Check this box to grant manual student access.</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'dfh_show_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'dfh_show_extra_user_profile_fields' );

/**
 * 3. Save Enrollment Checkbox Data
 */
function dfh_save_extra_user_profile_fields( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }

    if ( isset( $_POST['dfh_course_enrolled'] ) && '1' === $_POST['dfh_course_enrolled'] ) {
        update_user_meta( $user_id, 'dfh_course_enrolled', '1' );
    } else {
        delete_user_meta( $user_id, 'dfh_course_enrolled' );
    }
}
add_action( 'personal_options_update', 'dfh_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'dfh_save_extra_user_profile_fields' );

/**
 * 4. Helper Function: Check if User Has Access (With Admin Safety Override)
 */
function dfh_user_has_course_access( $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    if ( ! $user_id ) {
        return false;
    }

    if ( user_can( $user_id, 'administrator' ) ) {
        return true;
    }

    $is_enrolled = get_user_meta( $user_id, 'dfh_course_enrolled', true );
    return ( '1' === $is_enrolled );
}

/**
 * 5. Get Completed Lessons Array
 */
function dfh_get_completed_lessons( $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    if ( ! $user_id ) {
        return array();
    }
    
    $completed = get_user_meta( $user_id, 'dfh_completed_lessons', true );
    return is_array( $completed ) ? $completed : array();
}

/**
 * 6. Check if Specific Lesson is Completed
 */
function dfh_is_lesson_completed( $lesson_id, $user_id = 0 ) {
    $completed = dfh_get_completed_lessons( $user_id );
    return in_array( (int) $lesson_id, array_map( 'intval', $completed ), true );
}

/**
 * 7. AJAX Handler to Mark Lesson Complete & Return Next URL
 */
function dfh_ajax_mark_lesson_complete() {
    check_ajax_referer( 'dfh_progress_nonce', 'nonce' );

    $user_id   = get_current_user_id();
    $lesson_id = isset( $_POST['lesson_id'] ) ? intval( $_POST['lesson_id'] ) : 0;

    if ( ! $user_id || ! $lesson_id ) {
        wp_send_json_error( array( 'message' => 'Invalid request.' ) );
    }
    // Record completion and timestamps using helper (handles per-lesson and per-course timestamps)
    dfh_mark_lesson_completed( $lesson_id, $user_id );

    // Determine next lesson in tree sequence
    $adjacent = dfh_get_adjacent_lesson( $lesson_id );
    $next_url = '';

    if ( ! empty( $adjacent['next'] ) ) {
        $next_url = get_permalink( $adjacent['next'] );
    } else {
        // If no next lesson, try to return to the Course page that contains this lesson
        $courses = dfh_get_courses_for_lesson( $lesson_id );
        if ( ! empty( $courses ) ) {
            $next_url = get_permalink( (int) $courses[0] );
        } else {
            // Fallback to course archive
            $next_url = get_post_type_archive_link( 'course' );
        }
    }

    wp_send_json_success( array(
        'message'  => 'Lesson marked as complete.',
        'next_url' => $next_url,
    ) );
}
add_action( 'wp_ajax_dfh_mark_complete', 'dfh_ajax_mark_lesson_complete' );


/**
 * Find the student's current active lesson (the first uncompleted lesson in sequence).
 * Returns the lesson Post ID, or the first lesson if none started, or false if all complete.
 */
function dfh_get_student_current_lesson( $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    // Get all published lessons in hierarchical order
    $all_lessons = dfh_get_ordered_lesson_tree( 0 );
    if ( empty( $all_lessons ) ) {
        return false;
    }

    $completed = dfh_get_completed_lessons( $user_id );

    // Find the first lesson in the tree that is NOT in the completed array
    foreach ( $all_lessons as $lesson_id ) {
        if ( ! in_array( (int) $lesson_id, array_map( 'intval', $completed ), true ) ) {
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
function dfh_mark_lesson_started( $lesson_id, $user_id = 0 ) {
    if ( ! $lesson_id ) {
        return false;
    }
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    if ( ! $user_id ) {
        return false;
    }

    $timestamps = get_user_meta( $user_id, 'dfh_started_lessons', true );
    if ( ! is_array( $timestamps ) ) {
        $timestamps = array();
    }

    if ( empty( $timestamps[ $lesson_id ] ) ) {
        $timestamps[ $lesson_id ] = (int) current_time( 'timestamp' );
        update_user_meta( $user_id, 'dfh_started_lessons', $timestamps );
    }

    return true;
}

/**
 * Internal: mark lesson completed for user and record a completion timestamp.
 * Returns true if the lesson was newly marked as complete, false if it already existed.
 */
function dfh_mark_lesson_completed( $lesson_id, $user_id = 0 ) {
    if ( ! $lesson_id ) {
        return false;
    }
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    if ( ! $user_id ) {
        return false;
    }

    $completed = dfh_get_completed_lessons( $user_id );
    $was_new = false;
    if ( ! in_array( (int) $lesson_id, array_map( 'intval', $completed ), true ) ) {
        $completed[] = (int) $lesson_id;
        update_user_meta( $user_id, 'dfh_completed_lessons', $completed );
        $was_new = true;
    }

    // Record per-lesson completion timestamp
    $timestamps = get_user_meta( $user_id, 'dfh_completed_lessons_timestamps', true );
    if ( ! is_array( $timestamps ) ) {
        $timestamps = array();
    }
    $timestamps[ $lesson_id ] = (int) current_time( 'timestamp' );
    update_user_meta( $user_id, 'dfh_completed_lessons_timestamps', $timestamps );

    // After marking lesson complete, check and timestamp any course completions
    dfh_mark_course_completed_if_needed( $lesson_id, $user_id );

    return $was_new;
}

/**
 * Get the started timestamp (integer) for a lesson for a user, or null.
 */
function dfh_get_lesson_started_timestamp( $lesson_id, $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    $timestamps = get_user_meta( $user_id, 'dfh_started_lessons', true );
    if ( is_array( $timestamps ) && isset( $timestamps[ $lesson_id ] ) ) {
        return (int) $timestamps[ $lesson_id ];
    }
    return null;
}

/**
 * Get the completed timestamp (integer) for a lesson for a user, or null.
 */
function dfh_get_lesson_completed_timestamp( $lesson_id, $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    $timestamps = get_user_meta( $user_id, 'dfh_completed_lessons_timestamps', true );
    if ( is_array( $timestamps ) && isset( $timestamps[ $lesson_id ] ) ) {
        return (int) $timestamps[ $lesson_id ];
    }
    return null;
}

/**
 * Find courses that include the given lesson (by checking each course's root lessons).
 * Returns an array of course post IDs.
 */
function dfh_get_courses_for_lesson( $lesson_id ) {
    $courses = get_posts( array(
        'post_type'      => 'course',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ) );

    $matched = array();
    foreach ( $courses as $course_id ) {
        // Support both ACF stored arrays and plain postmeta
        $roots = array();
        if ( function_exists( 'get_field' ) ) {
            $roots = get_field( 'course_root_lessons', $course_id );
        }
        if ( empty( $roots ) ) {
            $roots = get_post_meta( $course_id, 'course_root_lessons', true );
        }
        if ( ! is_array( $roots ) ) {
            // Could be single ID or CSV; normalize
            if ( empty( $roots ) ) {
                $roots = array();
            } elseif ( is_numeric( $roots ) ) {
                $roots = array( (int) $roots );
            } elseif ( is_string( $roots ) ) {
                $parts = preg_split( '/\s*,\s*|\s+/u', trim( $roots ) );
                $roots = array_map( 'intval', $parts );
            } else {
                $roots = array();
            }
        }

        if ( empty( $roots ) ) {
            continue;
        }

        $ancestors = get_post_ancestors( $lesson_id );
        foreach ( $roots as $root_id ) {
            $root_id = (int) $root_id;
            if ( $root_id === (int) $lesson_id || in_array( $root_id, $ancestors, true ) ) {
                $matched[] = $course_id;
                break;
            }
        }
    }

    return array_values( array_unique( $matched ) );
}

/**
 * For each course that the lesson belongs to, if all its lessons are completed by the user,
 * set/update a per-user course completion timestamp stored in `dfh_course_completed_at` (assoc array course_id => timestamp).
 */
function dfh_mark_course_completed_if_needed( $lesson_id, $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    if ( ! $user_id ) {
        return;
    }

    $courses = dfh_get_courses_for_lesson( $lesson_id );
    if ( empty( $courses ) ) {
        return;
    }

    $completed = dfh_get_completed_lessons( $user_id );

    foreach ( $courses as $course_id ) {
        // Gather all lessons for this course from its root lessons
        $roots = get_field( 'course_root_lessons', $course_id );
        if ( empty( $roots ) ) {
            $roots = get_post_meta( $course_id, 'course_root_lessons', true );
        }
        if ( ! is_array( $roots ) ) {
            if ( is_numeric( $roots ) ) {
                $roots = array( (int) $roots );
            } elseif ( is_string( $roots ) ) {
                $parts = preg_split( '/\s*,\s*|\s+/u', trim( $roots ) );
                $roots = array_map( 'intval', $parts );
            } else {
                $roots = array();
            }
        }

        $course_lessons = array();
        foreach ( $roots as $r ) {
            $course_lessons = array_merge( $course_lessons, dfh_get_ordered_lesson_tree( (int) $r ) );
        }
        $course_lessons = array_values( array_unique( $course_lessons ) );

        if ( empty( $course_lessons ) ) {
            continue;
        }

        // If every lesson in course_lessons is in $completed, mark course completed
        $all_done = true;
        $completed_int = array_map( 'intval', $completed );
        foreach ( $course_lessons as $lid ) {
            if ( ! in_array( (int) $lid, $completed_int, true ) ) {
                $all_done = false;
                break;
            }
        }

        if ( $all_done ) {
            $course_timestamps = get_user_meta( $user_id, 'dfh_course_completed_at', true );
            if ( ! is_array( $course_timestamps ) ) {
                $course_timestamps = array();
            }
            if ( empty( $course_timestamps[ $course_id ] ) ) {
                $course_timestamps[ $course_id ] = (int) current_time( 'timestamp' );
                update_user_meta( $user_id, 'dfh_course_completed_at', $course_timestamps );
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
function dfh_get_bookmarked_lessons( $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    if ( ! $user_id ) {
        return array();
    }
    
    $bookmarks = get_user_meta( $user_id, 'dfh_bookmarked_lessons', true );
    return is_array( $bookmarks ) ? $bookmarks : array();
}

/**
 * Check if a specific lesson is bookmarked
 */
function dfh_is_lesson_bookmarked( $lesson_id, $user_id = 0 ) {
    $bookmarks = dfh_get_bookmarked_lessons( $user_id );
    return in_array( (int) $lesson_id, array_map( 'intval', $bookmarks ), true );
}

/**
 * AJAX Handler to Toggle Bookmark State
 */
function dfh_ajax_toggle_bookmark() {
    check_ajax_referer( 'dfh_bookmark_nonce', 'nonce' );

    $user_id   = get_current_user_id();
    $lesson_id = isset( $_POST['lesson_id'] ) ? intval( $_POST['lesson_id'] ) : 0;

    if ( ! $user_id || ! $lesson_id ) {
        wp_send_json_error( array( 'message' => 'Invalid request.' ) );
    }

    $bookmarks = dfh_get_bookmarked_lessons( $user_id );
    $is_bookmarked = false;

    // Toggle logic: remove if exists, add if missing
    $key = array_search( $lesson_id, array_map( 'intval', $bookmarks ), true );
    if ( false !== $key ) {
        unset( $bookmarks[$key] );
        $bookmarks = array_values( $bookmarks ); // Reindex array
        $is_bookmarked = false;
    } else {
        $bookmarks[] = $lesson_id;
        $is_bookmarked = true;
    }

    update_user_meta( $user_id, 'dfh_bookmarked_lessons', $bookmarks );

    wp_send_json_success( array( 
        'is_bookmarked' => $is_bookmarked,
        'message'       => $is_bookmarked ? 'Lesson bookmarked.' : 'Bookmark removed.' 
    ));
}
add_action( 'wp_ajax_dfh_toggle_bookmark', 'dfh_ajax_toggle_bookmark' );


/**
 * Automatically assign the 'course_student' role to newly registered users.
 *
 * @param int $user_id ID of the newly registered user.
 */
function dfh_set_default_user_role( $user_id ) {
    $user = new WP_User( $user_id );
    
    // Ensure we don't accidentally override administrator registrations
    if ( in_array( 'administrator', $user->roles, true ) ) {
        return;
    }

    // Set role to course_student
    $user->set_role( 'course_student' );
}
add_action( 'user_register', 'dfh_set_default_user_role' );


/**
 * Style the native WordPress login/lost password screen to match theme branding.
 */
function dfh_custom_login_styles() {
    ?>
    <style type="text/css">
        .wp-login-logo { display: none; }

        body.login {
            background-color: #f9f9f9; /* Match your background token */
        }
        body.login div#login h1 a {
            background-image: url('<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo.svg' ); ?>');
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
add_action( 'login_enqueue_scripts', 'dfh_custom_login_styles' );

/**
 * Redirect course students to the course page upon login instead of the WP Dashboard.
 * Administrators are still allowed into the dashboard.
 *
 * @param string  $redirect_to           The default redirect destination.
 * @param string  $requested_redirect_to The requested redirect destination.
 * @param WP_User $user                  The logged-in user object.
 * @return string                        The modified redirect URL.
 */
function dfh_redirect_student_after_login( $redirect_to, $requested_redirect_to, $user ) {
    // Make sure $user is a valid WP_User object
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        // If they are a course student (and NOT an administrator), redirect to the course
        if ( in_array( 'course_student', $user->roles, true ) && ! in_array( 'administrator', $user->roles, true ) ) {
            return home_url( '/course/' ); // Adjust if your course archive/slug differs
        }
    }
    
    return $redirect_to;
}
add_filter( 'login_redirect', 'dfh_redirect_student_after_login', 10, 3 );

/**
 * Hide the WordPress admin bar for course students.
 * Administrators will still see it.
 */
function dfh_hide_admin_bar_for_students( $show ) {
    if ( current_user_can( 'course_student' ) && ! current_user_can( 'administrator' ) ) {
        return false;
    }
    return $show;
}
add_filter( 'show_admin_bar', 'dfh_hide_admin_bar_for_students' );