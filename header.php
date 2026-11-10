<!DOCTYPE html>
<html class="viewport-fit-cover theme-course" <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport"
        content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, viewport-fit=cover">
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

    <!-- The library and the DFH theme from components.designforhumans.blog, then the
         course and its lesson colours (sub-brands: theme-course on <html>, a lesson's
         class beside its data-lesson or data-syllabus), the DFH site components the
         blog and the docs share (DFH Shared Styles), then the course's own CSS -->
    <link rel="stylesheet" href="https://use.typekit.net/lrj6kun.css">
<?php foreach (array('dfh-components.css', 'themes/dfh.css', 'themes/course.css', 'themes/course-aqua.css', 'themes/course-purple.css', 'themes/course-olive.css') as $sheet) : ?>
    <link rel="stylesheet" href="<?php echo esc_url(dfh_library_css($sheet)); ?>">
<?php endforeach; ?>
    <link rel="stylesheet" href="<?php echo esc_url(dfh_shared_css()); ?>">

    <?php /* The file's modified time, so a rebuilt file isn't served from a cache */ ?>
    <link rel="stylesheet" href="<?php echo esc_url(get_stylesheet_directory_uri() . '/css/dfh-course.css?v=' . filemtime(get_stylesheet_directory() . '/css/dfh-course.css')); ?>">

    <!-- Modern scalable SVG favicon with dark mode support -->
    <link rel="icon" type="image/svg+xml"
        href="<?php echo get_template_directory_uri(); ?>/assets/favicons/favicon.svg">

    <!-- Legacy fallback ICO (using sizes="any" tells modern browsers to prefer the SVG above) -->
    <link rel="icon" type="image/x-icon" sizes="32x32 48x48"
        href="<?php echo get_template_directory_uri(); ?>/assets/favicons/favicon.ico">

    <!-- Apple Touch Icon for iOS home screen bookmarks -->
    <link rel="apple-touch-icon" sizes="180x180"
        href="<?php echo get_template_directory_uri(); ?>/assets/favicons/apple-touch-icon.png">

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

<body <?php body_class(dfh_lesson_theme_class($syllabus_index)); ?> data-syllabus="<?php echo esc_attr($syllabus_index); ?>">
    <?php get_template_part('content', 'icon-sprite'); ?>
    <?php wp_body_open(); ?>
    <div class="app">