<?php

/**
 * Template Name: Sample Lesson
 * Template Post Type: sample-lesson
 * 
 * Standalone sample lesson template without progress tracking, bookmarking, or navigation.
 */

get_header();

$playback_id = function_exists('get_field') ? get_field('mux_playback_id') : get_post_meta(get_the_ID(), 'mux_playback_id', true);

// Get associated course from ACF field or postmeta
$associated_course_id = function_exists('get_field') ? get_field('sample_lesson_course') : get_post_meta(get_the_ID(), 'sample_lesson_course', true);
$associated_course_id = !empty($associated_course_id) ? absint($associated_course_id) : 0;
?>

<header class="lesson-head site-header" aria-label="Lesson navigation">
    <div class="lesson-head__inner container">
        <nav class="lesson-head__nav">
            <?php if ($associated_course_id && get_post($associated_course_id)): ?>
                <a class="lesson-head__breadcrumb link"
                    href="<?php echo esc_url(get_permalink($associated_course_id)); ?>"><?php echo esc_html(get_the_title($associated_course_id)); ?></a>
            <?php else: ?>
                <a class="lesson-head__breadcrumb link" href="<?php echo esc_url(home_url()); ?>">Home</a>
            <?php endif; ?>
        </nav>
        <div class="lesson-head__controls">
            <?php if ($playback_id): ?>
                <button type="button" id="video-stick-toggle" class="button subtle lesson-video__toggle video-stick"
                    aria-controls="lesson-video-player" aria-pressed="true">
                    <span class="sr">Unstick video</span>
                    <svg class="icon" width="32" height="32" aria-hidden="true">
                        <use href="#Lock" />
                    </svg>
                </button>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="site-main lesson-page" id="main">
    <article id="post-<?php the_ID(); ?>" <?php post_class('lesson-article'); ?>>

        <header class="lesson-header prose">
            <div class="container">
                <span class="lesson-header__kicker +pre small-heading">Free sample</span>
                <h1 class="lesson-header__title">
                    <?php the_title(); ?>
                </h1>
            </div>
        </header>

        <?php
        // Video playback
        if ($playback_id): ?>
            <div class="lesson-video">
                <div class="lesson-video__container container">
                    <mux-player id="lesson-video-player" playback-id="<?php echo esc_attr($playback_id); ?>"
                        accent-color="#2eab93" metadata-video-title="<?php echo esc_attr(get_the_title()); ?>"
                        style="width:100%;height:auto;" thumbnail-time="2">
                    </mux-player>
                </div>
            </div>
        <?php endif; ?>

        <div class="lesson-content">
            <div class="container lesson-main">
                <div class="lesson-body prose +sample">
                    <?php the_content(); ?>
                    <hr class="sr" />
                    <p class="lesson-body__sample-text">
                        <span>
                            To read more, consider
<?php if ($associated_course_id && get_post($associated_course_id)): ?>
    <a href="<?php echo esc_url(get_permalink($associated_course_id)); ?>">purchasing</a>
<?php else: ?>
    <a href="<?php echo esc_url(home_url()); ?>">purchasing</a>
<?php endif; ?>
                        this course.
                        </span>
                    </p>
                </div>
                <aside class="lesson-aside">
                    <?php
                    // Gather stats, external links, and downloads; render aside only if any exist
                    $stats_meta = function_exists('get_field') ? get_field('lesson_stats') : get_post_meta(get_the_ID(), 'lesson_stats', true);
                    $external_links = function_exists('get_field') ? get_field('lesson_external_links') : get_post_meta(get_the_ID(), 'lesson_external_links', true);
                    $download_ids = get_post_meta(get_the_ID(), '_dfh_linked_download_ids', true);

                    if (!empty($stats_meta) || !empty($external_links) || (!empty($download_ids) && is_array($download_ids))): ?>

                        <?php
                        // Stats
                        if (!empty($stats_meta)):
                            $slines = explode("\n", $stats_meta);
                            ?>
                            <section class="lesson-resources lesson-stats" aria-describedby="lesson-stats__heading">
                                <h2 class="subheading lesson-resources__heading" id="lesson-stats__heading">Key statistics</h2>
                                <dl class="lesson-stats__list">
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
                                <h2 class="subheading lesson-resources__heading" id="lesson-links-heading">Further reading &
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
                            </section>
                            <?php
                        endif;

                        // Downloads
                        if (!empty($download_ids) && is_array($download_ids)):
                            echo '<section class="lesson-resources lesson-downloads" aria-describedby="lesson-downloads-heading"><h2 class="subheading lesson-resources__heading" id="lesson-downloads-heading">Lesson downloads</h2><ul>';

                            foreach ($download_ids as $download_id) {
                                $download_post = get_post($download_id);
                                $attachment_id = get_post_meta($download_id, '_dfh_download_attachment_id', true);
                                $pdf_url = $attachment_id ? wp_get_attachment_url($attachment_id) : '';

                                if ($download_post && $pdf_url) {
                                    ?>
                                    <li>
                                        <div class="lesson-download">
                                            <?php if (has_post_thumbnail($download_id)): ?>
                                                <div class="lesson-download__thumb">
                                                    <?php echo get_the_post_thumbnail($download_id, 'thumbnail'); ?>
                                                </div>
                                            <?php endif; ?>
                                            <svg class="icon dir lesson-download__icon" width="32" height="32" aria-hidden="true">
                                                <use href="#Download" />
                                            </svg>
                                            <div class="lesson-download__info">
                                                <h3>
                                                    <a class="link" href="<?php echo esc_url($pdf_url); ?>"
                                                        download><?php echo esc_html($download_post->post_title); ?> (PDF)</a>
                                                </h3>
                                                <?php if (trim($download_post->post_excerpt) !== ''): ?>
                                                    <p class="tc-muted"><?php echo esc_html($download_post->post_excerpt); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                    <?php
                                }
                            }

                            echo '</ul></section>';
                        endif;
                        ?>
                    <?php endif; ?>
                </aside>
            </div>
        </div>

    </article><!-- #post-<?php the_ID(); ?> -->
</main><!-- #primary -->

<script src="https://cdn.jsdelivr.net/npm/@mux/mux-player"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const videoStickToggle = document.getElementById('video-stick-toggle');

        if (videoStickToggle) {
            videoStickToggle.addEventListener('click', function () {
                const isSticky = videoStickToggle.classList.contains('video-stick');

                videoStickToggle.classList.toggle('video-stick', !isSticky);
                videoStickToggle.classList.toggle('video-unstick', isSticky);
                videoStickToggle.setAttribute('aria-pressed', String(!isSticky));
                videoStickToggle.querySelector('.sr').textContent = isSticky ? 'Stick video' : 'Unstick video';
                videoStickToggle.querySelector('use').setAttribute('href', isSticky ? '#Unlock' : '#Lock');
            });
        }
    });
</script>

<?php
get_footer();
