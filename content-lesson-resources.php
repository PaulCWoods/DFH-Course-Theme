<?php
/**
 * A lesson's resources, in its aside: key statistics, further reading and
 * downloads, each shown only when the lesson has some. Used by the lesson
 * and the sample lesson.
 */
$lesson_id = $args['lesson_id'] ?? get_the_ID();

$stats_meta = get_post_meta($lesson_id, 'lesson_stats', true);
if (!empty($stats_meta)): ?>
    <section class="stack" aria-labelledby="lesson-stats-heading">
        <h2 class="subheading" id="lesson-stats-heading">Key statistics</h2>
        <dl class="telemetry-group telemetry-group--fill" aria-labelledby="lesson-stats-heading">
            <?php foreach (explode("\n", $stats_meta) as $line):
                $parts = array_map('trim', explode('|', $line));
                $label = $parts[0] ?? '';
                $value = $parts[1] ?? '';
                $percent = $parts[2] ?? '';
                if ($label === '' && $value === '') {
                    continue;
                } ?>
                <div class="telemetry"<?php echo is_numeric($percent) ? ' style="--value: ' . esc_attr(min(100, max(0, (float) $percent))) . '%;"' : ''; ?>>
                    <dt class="small-text text-muted"><?php echo esc_html($label); ?></dt>
                    <dd class="small-heading"><?php echo esc_html($value); ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </section>
<?php endif; ?>
<?php
$external_links = get_post_meta($lesson_id, 'lesson_external_links', true);
if (!empty($external_links)): ?>
    <section class="stack" aria-labelledby="lesson-links-heading">
        <h2 class="subheading" id="lesson-links-heading">Further reading</h2>
        <ul class="stack stack--tight p-0 m-0" role="list" style="--block-link-prominence: subtle;">
            <?php foreach (explode("\n", $external_links) as $line):
                $parts = array_map('trim', explode('|', $line));
                if (empty($parts[0]) || empty($parts[1])) {
                    continue;
                } ?>
                <li>
                    <a class="block-link block-link--sm" href="<?php echo esc_url($parts[1]); ?>" target="_blank" rel="noopener">
                        <span class="block-link__text"><?php echo esc_html($parts[0]); ?><span class="sr-only"> (opens in a new tab)</span></span>
                        <svg class="block-link__arrow icon" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#External" /></svg>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>
<?php
$download_ids = get_post_meta($lesson_id, '_dfh_linked_download_ids', true);
if (!empty($download_ids) && is_array($download_ids)): ?>
    <section class="stack" aria-labelledby="lesson-downloads-heading">
        <h2 class="subheading" id="lesson-downloads-heading">Downloads</h2>
        <ul class="grid p-0 m-0" role="list" style="--grid-min: 14rem;">
            <?php foreach ($download_ids as $download_id):
                $download_post = get_post($download_id);
                $attachment_id = get_post_meta($download_id, '_dfh_download_attachment_id', true);
                $pdf_url = $attachment_id ? wp_get_attachment_url($attachment_id) : '';
                if (!$download_post || !$pdf_url) {
                    continue;
                } ?>
                <li class="card card--compact">
                    <?php if (has_post_thumbnail($download_id)): ?>
                        <div class="card__thumb"><?php echo get_the_post_thumbnail($download_id, 'medium_large', array('alt' => '')); ?></div>
                    <?php endif; ?>
                    <header class="card__header">
                        <h3 class="card__title small-heading">
                            <a class="link link--plain link--stretched" href="<?php echo esc_url($pdf_url); ?>" download><?php echo esc_html($download_post->post_title); ?> <span class="text-muted">(PDF)</span></a>
                        </h3>
                        <svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#Download" /></svg>
                    </header>
                    <?php if (trim($download_post->post_excerpt) !== ''): ?>
                        <div class="card__body">
                            <p class="small-text text-muted"><?php echo esc_html($download_post->post_excerpt); ?></p>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>
