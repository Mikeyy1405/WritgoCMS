<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * This template is displayed when WordPress cannot find a matching
 * page or post for the requested URL. It provides a user-friendly
 * error message and helpful navigation options.
 *
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

<main id="primary" class="site-main">
	<div class="content-area">
		<section class="error-404 not-found">
			<header class="page-header">
				<h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'writgocms' ); ?></h1>
			</header><!-- .page-header -->

			<div class="page-content">
				<p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'writgocms' ); ?></p>

				<?php get_search_form(); ?>

				<div class="error-404-widgets">
					<?php
					the_widget(
						'WP_Widget_Recent_Posts',
						array(
							'title'  => esc_html__( 'Recent Posts', 'writgocms' ),
							'number' => 5,
						),
						array(
							'before_widget' => '<div class="widget widget_recent_entries">',
							'after_widget'  => '</div>',
							'before_title'  => '<h2 class="widget-title">',
							'after_title'   => '</h2>',
						)
					);
					?>

					<div class="widget widget_categories">
						<h2 class="widget-title"><?php esc_html_e( 'Most Used Categories', 'writgocms' ); ?></h2>
						<ul>
							<?php
							wp_list_categories(
								array(
									'orderby'    => 'count',
									'order'      => 'DESC',
									'show_count' => 1,
									'title_li'   => '',
									'number'     => 10,
								)
							);
							?>
						</ul>
					</div><!-- .widget -->

					<?php
					/* translators: %1$s: smiley */
					$writgocms_archive_content = '<p>' . sprintf( esc_html__( 'Try looking in the monthly archives. %1$s', 'writgocms' ), convert_smilies( ':)' ) ) . '</p>';

					the_widget(
						'WP_Widget_Archives',
						array(
							'title'    => esc_html__( 'Archives', 'writgocms' ),
							'count'    => 1,
							'dropdown' => 1,
						),
						array(
							'before_widget' => '<div class="widget widget_archive">' . $writgocms_archive_content,
							'after_widget'  => '</div>',
							'before_title'  => '<h2 class="widget-title">',
							'after_title'   => '</h2>',
						)
					);

					the_widget(
						'WP_Widget_Tag_Cloud',
						array(
							'title' => esc_html__( 'Tags', 'writgocms' ),
						),
						array(
							'before_widget' => '<div class="widget widget_tag_cloud">',
							'after_widget'  => '</div>',
							'before_title'  => '<h2 class="widget-title">',
							'after_title'   => '</h2>',
						)
					);
					?>
				</div><!-- .error-404-widgets -->
			</div><!-- .page-content -->
		</section><!-- .error-404 -->
	</div><!-- .content-area -->
</main><!-- #primary -->

<?php
get_sidebar();
get_footer();
