<?php
/**
 * The template for displaying search results pages
 *
 * This template handles the display of search results.
 * It shows a list of posts matching the search query
 * with excerpts and pagination.
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
				<h1 class="page-title">
					<?php
					printf(
						/* translators: %s: search query. */
						esc_html__( 'Search Results for: %s', 'writgocms' ),
						'<span>' . get_search_query() . '</span>'
					);
					?>
				</h1>
			</header><!-- .page-header -->

			<div class="search-results-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>

					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="entry-thumbnail">
								<a href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail( 'medium' ); ?>
								</a>
							</div>
						<?php endif; ?>

						<div class="entry-content-wrapper">
							<header class="entry-header">
								<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

								<?php if ( 'post' === get_post_type() ) : ?>
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
								<?php endif; ?>
							</header><!-- .entry-header -->

							<div class="entry-summary">
								<?php the_excerpt(); ?>
							</div><!-- .entry-summary -->

							<footer class="entry-footer">
								<span class="post-type-label">
									<?php
									$post_type_obj = get_post_type_object( get_post_type() );
									if ( $post_type_obj ) {
										echo esc_html( $post_type_obj->labels->singular_name );
									}
									?>
								</span>

								<a href="<?php the_permalink(); ?>" class="read-more">
									<?php esc_html_e( 'Read More', 'writgocms' ); ?>
								</a>
							</footer><!-- .entry-footer -->
						</div><!-- .entry-content-wrapper -->
					</article><!-- #post-<?php the_ID(); ?> -->

				<?php endwhile; ?>
			</div><!-- .search-results-list -->

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
					<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'writgocms' ); ?></p>
					<?php get_search_form(); ?>
				</div><!-- .page-content -->
			</section><!-- .no-results -->

		<?php endif; ?>
	</div><!-- .content-area -->
</main><!-- #primary -->

<?php
get_sidebar();
get_footer();
