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

    <!-- Modern scalable SVG favicon with dark mode support -->
    <link rel="icon" type="image/svg+xml" href="<?php echo get_template_directory_uri(); ?>/assets/favicons/favicon.svg">

    <!-- Legacy fallback ICO (using sizes="any" tells modern browsers to prefer the SVG above) -->
    <link rel="icon" type="image/x-icon" sizes="32x32 48x48" href="<?php echo get_template_directory_uri(); ?>/assets/favicons/favicon.ico">

    <!-- Apple Touch Icon for iOS home screen bookmarks -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/assets/favicons/apple-touch-icon.png">

    <!-- Web Manifest -->
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/site.webmanifest">

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
<?php get_template_part('content', 'icon-sprite'); ?>
<?php wp_body_open(); ?>
<a href="#main" class="skip">Skip to main content</a>
<div class="site">