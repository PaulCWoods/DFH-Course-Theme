<?php

/**
 * Template Name: Sample Lesson
 * Template Post Type: sample-lesson
 *
 * A free sample: the lesson's header, video, content and resources, with no
 * progress, bookmarks or course navigation. The content fades out into an
 * invitation to buy the course.
 */

$playback_id = function_exists('get_field') ? get_field('mux_playback_id') : get_post_meta(get_the_ID(), 'mux_playback_id', true);
$course_id = function_exists('get_field') ? get_field('sample_lesson_course') : get_post_meta(get_the_ID(), 'sample_lesson_course', true);
$course_id = $course_id && get_post(absint($course_id)) ? absint($course_id) : 0;
$course_url = $course_id ? get_permalink($course_id) : home_url();

get_header();
get_template_part('content', 'lesson-head', array(
    'up_url' => $course_url,
    'up_title' => $course_id ? get_the_title($course_id) : 'Courses home',
    'playback_id' => $playback_id,
));
?>
<main class="main main--flush" id="main">
    <article id="post-<?php the_ID(); ?>" <?php post_class('stack gap-0 min-h-full'); ?>>
        <header class="lesson-header prose font-sizing-fluid">
            <div class="wrapper">
                <p class="small-heading">Free sample</p>
                <h1 class="lesson-header__title"><?php the_title(); ?></h1>
                <?php
                $lesson_subtitle = function_exists('get_field') ? get_field('subtitle') : get_post_meta(get_the_ID(), 'subtitle', true);
                if ($lesson_subtitle): ?>
                    <p class="small-heading"><?php echo esc_html($lesson_subtitle); ?></p>
                <?php endif; ?>
            </div>
        </header>
        <?php get_template_part('content', 'lesson-video', array('playback_id' => $playback_id)); ?>
        <div class="lesson-content">
            <div class="wrapper article__main">
                <div class="lesson-body article__body prose font-sizing-fluid +sample">
                    <?php the_content(); ?>
                    <p class="lesson-body__sample-text">
                        <span>To read more, <a href="<?php echo esc_url($course_url); ?>">get the course</a>.</span>
                    </p>
                </div>
                <aside class="lesson-aside article__aside stack" aria-label="Lesson resources">
                    <?php get_template_part('content', 'lesson-resources', array('lesson_id' => get_the_ID())); ?>
                </aside>
            </div>
        </div>
    </article>
</main>
<?php if ($playback_id): ?>
    <script src="https://cdn.jsdelivr.net/npm/@mux/mux-player"></script>
<?php endif; ?>
<?php
get_footer();
