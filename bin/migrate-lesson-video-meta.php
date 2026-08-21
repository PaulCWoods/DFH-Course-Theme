<?php
// Migration helper: migrate legacy `lesson_video_url` postmeta to `mux_playback_id`.
// Usage (from WP root):
//   wp eval-file wp-content/themes/dfh-course-theme/bin/migrate-lesson-video-meta.php
// The script is idempotent: it will not overwrite existing `mux_playback_id` values.

$processed = 0;
$updated = 0;
$skipped = 0;

$args = array(
    'post_type' => 'lesson',
    'posts_per_page' => -1,
    'fields' => 'ids',
);
$lessons = get_posts($args);

if (empty($lessons)) {
    echo "No lesson posts found.\n";
    return;
}

foreach ($lessons as $post_id) {
    $processed++;
    $legacy = get_post_meta($post_id, 'lesson_video_url', true);
    if (empty($legacy)) {
        $skipped++;
        continue;
    }

    $existing = get_post_meta($post_id, 'mux_playback_id', true);
    if (!empty($existing)) {
        // Already migrated or manually set — skip
        $skipped++;
        continue;
    }

    $playback = '';

    // If it's a URL, try to extract the last path segment
    if (filter_var($legacy, FILTER_VALIDATE_URL)) {
        $parts = parse_url($legacy);
        if (!empty($parts['path'])) {
            $segments = array_values(array_filter(explode('/', $parts['path'])));
            $last = end($segments);
            // Strip file extension if present
            $last = preg_replace('/\.[a-zA-Z0-9]+$/', '', $last);
            // Remove query/fragments
            $last = preg_replace('/[?#].*$/', '', $last);
            $playback = trim($last);
        }
    } else {
        // Not a URL — try to sanitize as an ID (strip unexpected chars)
        $playback = preg_replace('/[^A-Za-z0-9_-]/', '', $legacy);
    }

    if (empty($playback)) {
        $skipped++;
        continue;
    }

    update_post_meta($post_id, 'mux_playback_id', $playback);
    echo "Migrated post {$post_id} -> {$playback}\n";
    $updated++;
}

echo "Done. Processed: {$processed}, Updated: {$updated}, Skipped: {$skipped}\n";
