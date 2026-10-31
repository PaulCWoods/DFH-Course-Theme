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
                    <li><a class="tab" href="<?php echo esc_url(home_url('/cart/')); ?>">My Cart</a></li>
                <?php elseif (!is_page('login')): ?>
                    <li>
                        <!-- Replace 'login' with the slug or URL of your new login page -->
                        <a class="tab" href="<?php echo esc_url(home_url('/login/')); ?>">Log In</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <button type="button" class="button button--subtle site-header__nav-toggle my-account-header__nav-toggle closed"
            aria-controls="my-account-navigation" aria-expanded="false">
            <svg class="icon" width="32" height="32" aria-hidden="true">
                <use href="#Navigation" />
            </svg>
            <span class="visually-hidden">Open My Account Navigation</span>
        </button>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.querySelector('.my-account-header__nav-toggle');
        const navigation = document.querySelector('.woocommerce-MyAccount-navigation');

        if (!toggle || !navigation) return;

        navigation.id = 'my-account-navigation';
        toggle.addEventListener('click', function () {
            const isClosed = toggle.classList.contains('closed');

            toggle.classList.toggle('closed', !isClosed);
            toggle.classList.toggle('open', isClosed);
            toggle.setAttribute('aria-expanded', String(isClosed));
            toggle.querySelector('.sr').textContent = isClosed
                ? 'Close My Account Navigation'
                : 'Open My Account Navigation';
            toggle.querySelector('use').setAttribute('href', isClosed ? '#Close' : '#Navigation');
        });
    });
</script>