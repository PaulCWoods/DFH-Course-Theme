<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta
        name="viewport"
        content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, viewport-fit=cover"
    >
    <?php
    // Build page title as "[Page title] | Design for Humans Courses"
    if (function_exists('is_singular') && is_singular()) {
        $page_title = single_post_title('', false);
    } elseif (function_exists('is_home') && (is_home() || is_front_page())) {
        $page_title = get_bloginfo('name');
    } elseif (function_exists('is_archive') && is_archive()) {
        $page_title = get_the_archive_title();
    } else {
        $page_title = function_exists('wp_get_document_title') ? wp_get_document_title() : get_bloginfo('name');
    }
    ?>
    <title><?php echo esc_html($page_title); ?> | Design for Humans Courses</title>
    <?php wp_head(); ?>

    <!-- Shared Design Tokens & Styles from your main blog -->
    <link rel="stylesheet" href="https://designforhumans.blog/styles/css/dfh-shared.css">
    <link rel="stylesheet" href="<?php echo esc_url( get_stylesheet_directory_uri() . '/css/dfh-course.css' ); ?>">

    <script src="https://cdn.jsdelivr.net/npm/@mux/mux-player"></script>
</head>
<?php
// Compute a top-level syllabus index for lesson pages (1-based). Defaults to 0.
$syllabus_index = 0;
if (function_exists('is_singular') && is_singular('lesson')) {
    if (function_exists('dfh_get_lesson_hierarchy_number')) {
        $code = dfh_get_lesson_hierarchy_number(get_the_ID());
        if (!empty($code)) {
            $parts = explode('.', $code);
            $syllabus_index = (int) $parts[0];
        }
    }
}
?>
<body <?php body_class(); ?> data-syllabus="<?php echo esc_attr($syllabus_index); ?>">
<svg style="position: absolute; width: 0; height: 0; overflow: hidden;" aria-hidden="true" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <defs>
        <symbol id="Starred" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <polygon points="60 83.79 27.55 107.36 39.95 69.22 7.5 45.64 47.61 45.64 60 7.5 72.39 45.64 112.5 45.64 80.05 69.22 92.45 107.36 60 83.79"></polygon>
            <path d="M60 33.39l4.79 14.73 1.8 5.53h21.3l-12.53 9.1-4.7 3.42 1.8 5.53 4.79 14.73-12.53-9.1-4.7-3.42-4.7 3.42-12.53 9.1 4.79-14.73 1.8-5.53-4.7-3.42-12.53-9.1h21.3l1.8-5.53 4.79-14.73v0zM60 7.5 47.61 45.64H7.5l32.45 23.57-12.39 38.14 32.45-23.57 32.45 23.57-12.39-38.14 32.45-23.57H72.41L60 7.5z"></path>
        </symbol>
        <symbol id="Star" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <path d="M60 33.39l4.79 14.73 1.8 5.53h21.3l-12.53 9.1-4.7 3.42 1.8 5.53 4.79 14.73-12.53-9.1-4.7-3.42-4.7 3.42-12.53 9.1 4.79-14.73 1.8-5.53-4.7-3.42-12.53-9.1h21.3l1.8-5.53 4.79-14.73v0zM60 7.5 47.61 45.64H7.5l32.45 23.57-12.39 38.14 32.45-23.57 32.45 23.57-12.39-38.14 32.45-23.57H72.41L60 7.5z"></path>
        </symbol>
        <symbol id="Progress" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <path d="M17.22 90.42C12.65 84 9.54 76.5 8.24 68.75l14.79-2.48c0.94 5.61 3.1 10.81 6.41 15.46z"></path>
            <path d="M23.03 53.74 8.24 51.26c1.3-7.75 4.41-15.25 8.98-21.67l12.22 8.7c-3.31 4.65-5.47 9.85-6.41 15.45z"></path>
            <path d="M51.26 111.76c-7.75-1.3-15.25-4.41-21.67-8.98l8.7-12.22c4.65 3.31 9.85 5.47 15.46 6.41z"></path>
            <path d="M38.29 29.44l-8.7-12.22c6.42-4.57 13.92-7.68 21.67-8.98l2.48 14.79c-5.61 0.94-10.81 3.1-15.45 6.41z"></path>
            <path d="M68.74 111.76 66.26 96.97C84.36 93.93 97.5 78.38 97.5 60S84.36 26.07 66.26 23.03l2.48-14.79C94.1 12.5 112.5 34.27 112.5 60s-18.4 47.51-43.76 51.76z"></path>
            <polygon points="52.5 85.61 32.2 65.3 42.8 54.7 52.5 64.39 77.2 39.7 87.8 50.3 52.5 85.61"></polygon>
        </symbol>
        <symbol id="Plus" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <rect width="15" height="90" x="52.5" y="15"></rect>
            <rect width="90" height="15" x="15" y="52.5"></rect>
        </symbol>
        <symbol id="Navigation" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <rect width="90" height="15" x="15" y="52.5"></rect>
            <rect width="90" height="15" x="15" y="22.5"></rect>
            <rect width="90" height="15" x="15" y="82.5"></rect>
        </symbol>
        <symbol id="Minus" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <rect width="90" height="15" x="15" y="52.5"></rect>
        </symbol>
        <symbol id="Home" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <path d="M100.84 58.74l-15-10 8.32-12.48 15 10zM19.16 58.74 10.84 46.26l15-10 8.32 12.48z"></path>
            <path d="M97.5 105h-75V38.49l37.5-25 37.5 25zM37.5 90h45V46.51l-22.5-15-22.5 15z"></path>
        </symbol>
        <symbol id="Close" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <rect transform="rotate(-45 60.001407 59.996604)" width="15" height="106.07" x="52.5" y="6.97"></rect>
            <rect transform="rotate(-45 60.001407 59.996604)" width="106.07" height="15" x="6.97" y="52.5"></rect>
        </symbol>
        <symbol id="Check" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <polygon points="42.86 100.61 11.84 69.59 22.45 58.98 42.86 79.39 97.55 24.7 108.16 35.3 42.86 100.61"></polygon>
        </symbol>
        <symbol id="Bookmarked" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <polygon points="90 22.5 90 97.5 60 82.5 30 97.5 30 22.5 90 22.5"></polygon>
            <path d="M97.5 109.64 60 90.89 22.5 109.64V15h75zM60 74.11 82.5 85.36V30h-45v55.36z"></path>
        </symbol>
        <symbol id="Bookmark" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <path d="M97.5 109.64 60 90.89 22.5 109.64V15h75zM60 74.11 82.5 85.36V30h-45v55.36z"></path>
        </symbol>
        <symbol id="ArrowUp" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <polygon points="100.2 65.76 60 32.26 19.8 65.76 10.2 54.24 60 12.74 109.8 54.24 100.2 65.76"></polygon>
            <rect width="15" height="82.5" x="52.5" y="22.5"></rect>
        </symbol>
        <symbol id="ArrowRight" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <polygon points="65.76 109.8 54.24 100.2 87.74 60 54.24 19.8 65.76 10.2 107.26 60 65.76 109.8"></polygon>
            <rect width="82.5" height="15" x="15" y="52.5"></rect>
        </symbol>
        <symbol id="ArrowLeft" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <polygon points="54.24 109.8 12.74 60 54.24 10.2 65.76 19.8 32.26 60 65.76 100.2 54.24 109.8"></polygon>
            <rect width="82.5" height="15" x="22.5" y="52.5"></rect>
        </symbol>
        <symbol id="ArrowDown" viewBox="0 0 120 120" fill="currentColor" data-name="Layer 1">
            <polygon points="60 107.26 10.2 65.76 19.8 54.24 60 87.74 100.2 54.24 109.8 65.76 60 107.26"></polygon>
            <rect width="15" height="82.5" x="52.5" y="15"></rect>
        </symbol>
    </defs>
</svg>
<?php wp_body_open(); ?>
<div class="site">