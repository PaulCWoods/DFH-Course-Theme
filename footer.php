<footer class="footer footer--stacked">
    <div class="footer__bar">
        <nav aria-label="Footer">
            <ul class="cluster row-gap-0" role="list">
                <li>
                    <a class="link link--plain" href="<?php echo esc_url(home_url()); ?>">Courses Home</a>
                </li>
                <li>
                    <a class="link link--plain" href="<?php echo esc_url(home_url("/privacy-policy")); ?>">Privacy
                        Policy</a>
                </li>
                <li>
                    <a class="link link--plain" href="<?php echo esc_url(home_url("/terms-and-conditions")); ?>">Terms
                        & Conditions</a>
                </li>
                <li>
                    <a target="_blank" class="link link--plain" href="https://designforhumans.blog">Blog</a>
                </li>
                <li>
                    <a target="_blank" class="link link--plain" href="https://designforhumans.blog/contact">Contact
                        me</a>
                </li>
            </ul>
        </nav>
        <p class="small-text">&copy; <?php echo date('Y'); ?> Design For Humans. All rights reserved.</p>
        <span class="footer__brand footer__brand-wordmark" aria-hidden="true">Design For Humans</span>
    </div>
</footer>
</div>
<?php wp_footer(); ?>
</body>

</html>