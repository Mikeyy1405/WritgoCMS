<?php
/**
 * WooCommerce Compatibility Template
 *
 * This template provides WooCommerce integration for the theme.
 * It wraps WooCommerce content within the theme's layout structure,
 * ensuring a consistent look and feel across the entire site.
 *
 * @link https://woocommerce.com/document/third-party-custom-theme-compatibility/
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WritgoCMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main woocommerce-content">
	<div class="content-area">
		<?php woocommerce_content(); ?>
	</div><!-- .content-area -->
</main><!-- #primary -->

<?php
get_sidebar();
get_footer();
