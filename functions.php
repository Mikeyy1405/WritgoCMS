<?php
/**
 * WritgoCMS Theme Functions
 *
 * @package WritgoCMS
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define theme constants
define( 'WRITGOCMS_VERSION', '1.0.0' );
define( 'WRITGOCMS_DIR', get_template_directory() );
define( 'WRITGOCMS_URI', get_template_directory_uri() );

/**
 * Theme Setup
 */
function writgocms_setup() {
    // Add default posts and comments RSS feed links to head
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails
    add_theme_support( 'post-thumbnails' );

    // Register Navigation Menus
    register_nav_menus(
        array(
            'primary' => esc_html__( 'Primary Menu', 'writgocms' ),
            'footer'  => esc_html__( 'Footer Menu', 'writgocms' ),
        )
    );

    // Add theme support for HTML5
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );

    // Add theme support for selective refresh for widgets
    add_theme_support( 'customize-selective-refresh-widgets' );

    // Add support for custom logo
    add_theme_support(
        'custom-logo',
        array(
            'height'      => 250,
            'width'       => 250,
            'flex-width'  => true,
            'flex-height' => true,
        )
    );

    // Add support for editor styles
    add_theme_support( 'editor-styles' );

    // Add support for Block Styles
    add_theme_support( 'wp-block-styles' );

    // Add support for responsive embedded content
    add_theme_support( 'responsive-embeds' );

    // Add WooCommerce support
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'writgocms_setup' );

/**
 * Enqueue scripts and styles.
 */
function writgocms_scripts() {
    wp_enqueue_style( 'writgocms-style', get_stylesheet_uri(), array(), WRITGOCMS_VERSION );
}
add_action( 'wp_enqueue_scripts', 'writgocms_scripts' );

/**
 * Register widget areas.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function writgocms_widgets_init() {
    register_sidebar(
        array(
            'name'          => esc_html__( 'Sidebar', 'writgocms' ),
            'id'            => 'sidebar-1',
            'description'   => esc_html__( 'Add widgets here.', 'writgocms' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        )
    );

    register_sidebar(
        array(
            'name'          => esc_html__( 'Footer 1', 'writgocms' ),
            'id'            => 'footer-1',
            'description'   => esc_html__( 'Add widgets here.', 'writgocms' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        )
    );

    register_sidebar(
        array(
            'name'          => esc_html__( 'Footer 2', 'writgocms' ),
            'id'            => 'footer-2',
            'description'   => esc_html__( 'Add widgets here.', 'writgocms' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        )
    );

    register_sidebar(
        array(
            'name'          => esc_html__( 'Footer 3', 'writgocms' ),
            'id'            => 'footer-3',
            'description'   => esc_html__( 'Add widgets here.', 'writgocms' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        )
    );
}
add_action( 'widgets_init', 'writgocms_widgets_init' );

/**
 * Custom comment callback for comment display.
 *
 * This function formats individual comments in a customized way,
 * providing better structure for styling.
 *
 * @param WP_Comment $comment The comment object.
 * @param array      $args    An array of arguments.
 * @param int        $depth   The depth of the comment.
 */
function writgocms_comment_callback( $comment, $args, $depth ) {
    $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
    ?>
    <<?php echo esc_html( $tag ); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent', $comment ); ?>>
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
            <footer class="comment-meta">
                <div class="comment-author vcard">
                    <?php
                    if ( 0 !== $args['avatar_size'] ) {
                        echo get_avatar( $comment, $args['avatar_size'] );
                    }
                    ?>
                    <?php
                    printf(
                        '<b class="fn">%s</b>',
                        get_comment_author_link( $comment )
                    );
                    ?>
                </div><!-- .comment-author -->

                <div class="comment-metadata">
                    <a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
                        <time datetime="<?php comment_time( 'c' ); ?>">
                            <?php
                            printf(
                                /* translators: 1: comment date, 2: comment time */
                                esc_html__( '%1$s at %2$s', 'writgocms' ),
                                get_comment_date( '', $comment ),
                                get_comment_time()
                            );
                            ?>
                        </time>
                    </a>
                    <?php edit_comment_link( esc_html__( 'Edit', 'writgocms' ), '<span class="edit-link">', '</span>' ); ?>
                </div><!-- .comment-metadata -->

                <?php if ( '0' === $comment->comment_approved ) : ?>
                    <p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'writgocms' ); ?></p>
                <?php endif; ?>
            </footer><!-- .comment-meta -->

            <div class="comment-content">
                <?php comment_text(); ?>
            </div><!-- .comment-content -->

            <?php
            comment_reply_link(
                array_merge(
                    $args,
                    array(
                        'add_below' => 'div-comment',
                        'depth'     => $depth,
                        'max_depth' => $args['max_depth'],
                        'before'    => '<div class="reply">',
                        'after'     => '</div>',
                    )
                )
            );
            ?>
        </article><!-- .comment-body -->
    <?php
}

// AIML Integration
require_once WRITGOCMS_DIR . '/inc/class-aiml-provider.php';
require_once WRITGOCMS_DIR . '/inc/admin-aiml-settings.php';
require_once WRITGOCMS_DIR . '/inc/gutenberg-aiml-block.php';
require_once WRITGOCMS_DIR . '/inc/classic-editor-button.php';
