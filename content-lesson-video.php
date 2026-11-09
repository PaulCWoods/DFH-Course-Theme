<?php
/**
 * The lesson's video, when it has one (Mux). Used by the lesson and the
 * sample lesson; the header's toggle keeps it in view on narrow touch
 * screens.
 */
$playback_id = $args['playback_id'] ?? '';
?>
<?php if ($playback_id): ?>
    <div class="lesson-video">
        <div class="lesson-video__container wrapper wrapper--flush">
            <mux-player id="lesson-video-player" playback-id="<?php echo esc_attr($playback_id); ?>"
                accent-color="#2eab93" metadata-video-title="<?php echo esc_attr(get_the_title()); ?>"
                style="width:100%;height:auto;" thumbnail-time="2">
            </mux-player>
        </div>
    </div>
<?php endif; ?>
