<header class="header header--sticky header--translucent max-md:header--compact">
    <a href="#main" class="skip-link header__skip-link">Skip to main content</a>
    <div class="header__bar">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="link link--plain header__brand">
            <svg class="icon header__brand-icon" width="1em" height="1em" aria-hidden="true" focusable="false">
                <use href="#BrandDesignForHumans"></use>
            </svg>
            <span class="header__brand-wordmark">Courses</span>
        </a>
        <nav class="header__nav tab-list tab-list--plain tab-list--scrollable site-trail" aria-label="Breadcrumb">
            <ul>
                <?php if (is_user_logged_in()): ?>
                    <li>
                        <a class="tab" href="<?php echo esc_url(home_url('/my-account/')); ?>">Account</a>
                    </li>
                    <li>
                        <a class="tab" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Log Out</a>
                    </li>
                <?php elseif (!is_page('login')): ?>
                    <li>
                        <!-- Replace 'login' with the slug or URL of your new login page -->
                        <a class="tab" href="<?php echo esc_url(home_url('/login/')); ?>">Log In</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>