<?php
/**
 * The template for displaying archive pages
 *
 * This template handles the display of archive pages including
 * category, tag, author, and date-based archives. It provides
 * a consistent layout for listing multiple posts.
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
		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<?php
				the_archive_title( '<h1 class="page-title">', '</h1>' );
				the_archive_description( '<div class="archive-description">', '</div>' );
				?>
			</header><!-- .page-header -->

			<div class="posts-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>

					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="entry-thumbnail">
								<a href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail( 'medium_large' ); ?>
								</a>
							</div>
						<?php endif; ?>

						<header class="entry-header">
							<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

							<div class="entry-meta">
								<?php
								printf(
									'<span class="posted-on">%1$s <time datetime="%2$s">%3$s</time></span>',
									esc_html__( 'Posted on', 'writgocms' ),
									esc_attr( get_the_date( DATE_W3C ) ),
									esc_html( get_the_date() )
								);

								printf(
									'<span class="byline"> %1$s <span class="author vcard"><a href="%2$s">%3$s</a></span></span>',
									esc_html__( 'by', 'writgocms' ),
									esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
									esc_html( get_the_author() )
								);
								?>
							</div><!-- .entry-meta -->
						</header><!-- .entry-header -->

						<div class="entry-summary">
							<?php the_excerpt(); ?>
						</div><!-- .entry-summary -->

						<footer class="entry-footer">
							<?php
							$categories_list = get_the_category_list( esc_html__( ', ', 'writgocms' ) );
							if ( $categories_list ) {
								printf(
									'<span class="cat-links">%1$s %2$s</span>',
									esc_html__( 'Categories:', 'writgocms' ),
									$categories_list // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								);
							}

							$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'writgocms' ) );
							if ( $tags_list ) {
								printf(
									'<span class="tags-links">%1$s %2$s</span>',
									esc_html__( 'Tags:', 'writgocms' ),
									$tags_list // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								);
							}
							?>

							<a href="<?php the_permalink(); ?>" class="read-more">
								<?php esc_html_e( 'Read More', 'writgocms' ); ?>
							</a>
						</footer><!-- .entry-footer -->
					</article><!-- #post-<?php the_ID(); ?> -->

				<?php endwhile; ?>
			</div><!-- .posts-list -->

			<?php
			the_posts_pagination(
				array(
					'prev_text'          => esc_html__( '&laquo; Previous', 'writgocms' ),
					'next_text'          => esc_html__( 'Next &raquo;', 'writgocms' ),
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Page', 'writgocms' ) . ' </span>',
				)
			);

		else :
			?>

			<section class="no-results not-found">
				<header class="page-header">
					<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'writgocms' ); ?></h1>
				</header><!-- .page-header -->

				<div class="page-content">
					<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'writgocms' ); ?></p>
					<?php get_search_form(); ?>
				</div><!-- .page-content -->
			</section><!-- .no-results -->

		<?php endif; ?>
	</div><!-- .content-area -->
</main><!-- #primary -->

<?php
get_sidebar();
get_footer();
