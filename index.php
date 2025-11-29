<?php
/**
 * Main Template File
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 *
 * @package WritgoCMS
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main">
    <div class="content-area">
        <?php if ( is_home() && ! is_front_page() ) : ?>
            <header class="page-header">
                <h1 class="page-title"><?php single_post_title(); ?></h1>
            </header>
        <?php endif; ?>

        <?php if ( have_posts() ) : ?>
            <div class="blog-posts">
                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="entry-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'writgocms-card' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="entry-content-wrapper">
                            <header class="entry-header">
                                <?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>

                                <div class="entry-meta">
                                    <span class="posted-on">
                                        <time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
                                            <?php echo esc_html( get_the_date() ); ?>
                                        </time>
                                    </span>
                                    <span class="byline">
                                        <?php esc_html_e( 'by', 'writgocms' ); ?>
                                        <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
                                            <?php echo esc_html( get_the_author() ); ?>
                                        </a>
                                    </span>
                                    <?php
                                    $categories_list = get_the_category_list( ', ' );
                                    if ( $categories_list ) :
                                        ?>
                                        <span class="cat-links">
                                            <?php echo $categories_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </header>

                            <div class="entry-excerpt">
                                <?php the_excerpt(); ?>
                            </div>

                            <a href="<?php the_permalink(); ?>" class="read-more">
                                <?php esc_html_e( 'Read More', 'writgocms' ); ?>
                            </a>
                        </div>
                    </article>
                    <?php
                endwhile;
                ?>
            </div>

            <?php
            the_posts_pagination(
                array(
                    'prev_text' => '&larr; ' . esc_html__( 'Previous', 'writgocms' ),
                    'next_text' => esc_html__( 'Next', 'writgocms' ) . ' &rarr;',
                )
            );
            ?>

        <?php else : ?>
            <div class="no-results">
                <h2><?php esc_html_e( 'Nothing Found', 'writgocms' ); ?></h2>
                <p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try a search?', 'writgocms' ); ?></p>
                <?php get_search_form(); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php get_sidebar(); ?>
</main>

<?php
get_footer();
