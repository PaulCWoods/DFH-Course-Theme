<header class="site-header">
    <div class="container site-header-inner">
        <nav class="site-navigation">
            <ul class="site-breadcrumb container">
                <li>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="link site-breadcrumb__home">
                        <svg class="icon dir" width="32" height="32" aria-hidden="true">
                            <use href="#Home" />
                        </svg>
                        Courses Home
                    </a>
                </li>
                <?php if (is_user_logged_in()): ?>
                    <li>
                        <a class="link" href="<?php echo esc_url(wp_logout_url(home_url())); ?>">Log Out</a>
                    </li>
                <?php elseif (!is_page('login')): ?>
                    <li>
                        <!-- Replace 'login' with the slug or URL of your new login page -->
                        <a class="link" href="<?php echo esc_url(home_url('/login/')); ?>" class="login-link">Log In</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>