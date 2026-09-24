<header class="site-header__container">
    <div class="site-header my-account-header">
        <nav class="container site-header__inner">
            <ul class="site-breadcrumb">
                <li class="+home">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-title site-breadcrumb__home link">
                        <svg class="site-title__icon icon" width="32" height="32" aria-hidden="true">
                            <use href="#DesignForHumans" />
                        </svg>
                        <span class="site-title__brand brand-wordmark">Courses</span>
                    </a>
                </li>
                <?php if (is_user_logged_in()): ?>
                    <li><a class="link" href="<?php echo esc_url(home_url('/cart/')); ?>">My Cart</a></li>
                <?php elseif (!is_page('login')): ?>
                    <li>
                        <!-- Replace 'login' with the slug or URL of your new login page -->
                        <a class="link" href="<?php echo esc_url(home_url('/login/')); ?>">Log In</a>
                    </li>
                <?php endif; ?>
            </ul>
            <button type="button" class="dfh-button dfh-button--subtle site-header__nav-toggle my-account-header__nav-toggle closed"
                aria-controls="my-account-navigation" aria-expanded="false">
                <svg class="icon" width="32" height="32" aria-hidden="true">
                    <use href="#Navigation" />
                </svg>
                <span class="sr">Open My Account Navigation</span>
            </button>
        </nav>
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