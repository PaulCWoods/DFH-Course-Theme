<header class="site-header">
    <div class="container site-header__inner">
        <nav class="site-navigation">
            <ul class="site-breadcrumb">
                <li>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="link site-breadcrumb__home">
                        <svg class="icon" width="32" height="32" aria-hidden="true">
                            <use href="#DesignForHumans" />
                        </svg>
                        Courses Home
                    </a>
                </li>
                <?php if (is_user_logged_in()): ?>
                    <li>
                        <a class="link" href="<?php echo esc_url(home_url('/my-account/')); ?>">My Account</a>
                    </li>
                <?php elseif (!is_page('login')): ?>
                    <li>
                        <!-- Replace 'login' with the slug or URL of your new login page -->
                        <a class="link" href="<?php echo esc_url(home_url('/login/')); ?>">Log In</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>