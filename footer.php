<?php
/**
 * The footer template for WritgoCMS
 *
 * @package WritgoCMS
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

    <footer id="colophon" class="site-footer">
        <div class="footer-content">
            <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
                <div class="footer-widgets">
                    <?php dynamic_sidebar( 'footer-1' ); ?>
                </div>
            <?php endif; ?>

            <nav class="footer-navigation">
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'footer',
                        'menu_id'        => 'footer-menu',
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    )
                );
                ?>
            </nav>
        </div>

        <div class="site-info">
            <span class="copyright">
                &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.
                <?php esc_html_e( 'All rights reserved.', 'writgocms' ); ?>
            </span>
            <span class="theme-credit">
                <?php
                printf(
                    /* translators: Theme name link */
                    esc_html__( 'Theme: %s', 'writgocms' ),
                    '<a href="https://github.com/Mikeyy1405/WritgoCMS">WritgoCMS</a>'
                );
                ?>
            </span>
        </div><!-- .site-info -->
    </footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
