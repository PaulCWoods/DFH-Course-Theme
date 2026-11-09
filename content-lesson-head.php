<?php
/**
 * The lesson header: the lesson's number, a breadcrumb up a level that
 * gives way to the lesson's title as the page scrolls, the video toggle and
 * the progress panel's Button. Used by the lesson and the sample lesson.
 *
 * $args: code, up_url, up_title, playback_id, progress (show the panel's
 * Button).
 */
$code = $args['code'] ?? '';
$playback_id = $args['playback_id'] ?? '';
?>
<a href="#main" class="skip-link">Skip to main content</a>
<header class="header header--sticky max-md:header--compact lesson-head__container">
    <div class="lesson-head header__bar">
        <div class="reading-progress color-scheme-dark" aria-hidden="true"></div>
        <div class="lesson-head__inner">
            <?php if ($code): ?>
                <span class="lesson-head__code-badge"><span class="sr-only">Lesson </span><?php echo esc_html($code); ?></span>
            <?php endif; ?>
            <nav class="lesson-head__nav stack" aria-label="Breadcrumb">
                <a class="lesson-head__nav-parent link link--plain" href="<?php echo esc_url($args['up_url']); ?>"><?php echo esc_html($args['up_title']); ?></a>
                <span class="lesson-head__nav-current" aria-hidden="true"><?php echo esc_html(get_the_title()); ?></span>
            </nav>
            <div class="lesson-head__controls max-lg:hide-labels">
                <?php if ($playback_id): ?>
                    <button type="button" id="video-stick-toggle" class="button button--subtle button--icon lesson-video__toggle"
                        aria-controls="lesson-video-player" aria-pressed="false">
                        <span class="sr-only">Keep the video in view</span>
                        <svg class="icon icon--off" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#Unlock" /></svg>
                        <svg class="icon icon--on" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#Lock" /></svg>
                    </button>
                    <script>
                        document.getElementById('video-stick-toggle').addEventListener('click', (event) => {
                            const button = event.currentTarget;
                            const on = button.getAttribute('aria-pressed') !== 'true';
                            button.setAttribute('aria-pressed', String(on));
                            document.documentElement.classList.toggle('video-stick', on);
                        });
                    </script>
                <?php endif; ?>
                <?php if (!empty($args['progress'])): ?>
                    <button type="button" class="button button--plain" commandfor="course-progress" command="show-modal">
                        <span>Progress</span>
                        <svg class="icon" width="1em" height="1em" aria-hidden="true" focusable="false"><use href="#Navigation" /></svg>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
