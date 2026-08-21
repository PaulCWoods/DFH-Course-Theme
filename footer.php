            <footer class="site-footer">
                <div class="container site-footer__inner">
                    <nav class="site-footer__nav" aria-label="Footer">
                        <ul>
                            <li>
                                <a class="link" href="<?php echo esc_url(home_url( "/privacy-policy" )); ?>">Privacy Policy</a>
                            </li>
                            <li>
                                <a class="link" href="<?php echo esc_url(home_url( "/terms-and-conditions" )); ?>">Terms & Conditions</a>
                            </li>
                            <li>
                                <a target="_blank" class="link" href="https://designforhumans.blog">Blog</a>
                            </li>
                        </ul>
                    </nav>
                    <p>&copy; <?php echo date('Y'); ?> Design For Humans. All rights reserved.</p>
                </div>
            </footer>
        </div>
        <?php wp_footer(); ?>
    </body>
</html>