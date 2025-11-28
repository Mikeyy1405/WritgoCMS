<?php
/**
 * The template for displaying single posts
 *
 * @package WritgoCMS
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main">
    <?php
    while ( have_posts() ) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

                <div class="entry-meta">
                    <span class="posted-on">
                        <?php
                        printf(
                            /* translators: %s: Post date */
                            esc_html__( 'Posted on %s', 'writgocms' ),
                            '<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>'
                        );
                        ?>
                    </span>
                    <span class="byline">
                        <?php
                        printf(
                            /* translators: %s: Author name */
                            esc_html__( 'by %s', 'writgocms' ),
                            '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a>'
                        );
                        ?>
                    </span>
                    <?php if ( has_category() ) : ?>
                        <span class="cat-links">
                            <?php
                            printf(
                                /* translators: %s: Category list */
                                esc_html__( 'in %s', 'writgocms' ),
                                get_the_category_list( ', ' )
                            );
                            ?>
                        </span>
                    <?php endif; ?>
                </div><!-- .entry-meta -->
            </header><!-- .entry-header -->

            <?php if ( has_post_thumbnail() ) : ?>
                <div class="post-thumbnail">
                    <?php the_post_thumbnail( 'large' ); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content">
                <?php
                the_content();

                wp_link_pages(
                    array(
                        'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'writgocms' ),
                        'after'  => '</div>',
                    )
                );
                ?>
            </div><!-- .entry-content -->

            <footer class="entry-footer">
                <?php if ( has_tag() ) : ?>
                    <span class="tags-links">
                        <?php
                        printf(
                            /* translators: %s: Tag list */
                            esc_html__( 'Tagged: %s', 'writgocms' ),
                            get_the_tag_list( '', ', ' )
                        );
                        ?>
                    </span>
                <?php endif; ?>

                <?php
                edit_post_link(
                    sprintf(
                        wp_kses(
                            /* translators: %s: Name of current post. Only visible to screen readers */
                            __( 'Edit <span class="screen-reader-text">%s</span>', 'writgocms' ),
                            array(
                                'span' => array(
                                    'class' => array(),
                                ),
                            )
                        ),
                        wp_kses_post( get_the_title() )
                    ),
                    '<span class="edit-link">',
                    '</span>'
                );
                ?>
            </footer><!-- .entry-footer -->
        </article><!-- #post-<?php the_ID(); ?> -->

        <?php
        // Post navigation
        the_post_navigation(
            array(
                'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'writgocms' ) . '</span> <span class="nav-title">%title</span>',
                'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'writgocms' ) . '</span> <span class="nav-title">%title</span>',
            )
        );

        // If comments are open or we have at least one comment, load up the comment template.
        if ( comments_open() || get_comments_number() ) :
            comments_template();
        endif;

    endwhile; // End of the loop.
    ?>
</main><!-- #primary -->

<?php
get_sidebar();
get_footer();
