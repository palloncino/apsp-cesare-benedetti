<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); 

// Check if this page should show the dynamic menu BEFORE the loop
// Show sidebar for pages under "amministrazione-trasparente" or pages that are part of Amministrazione Trasparente
$should_show_menu = false;
$current_page = get_queried_object();

// List of Amministrazione Trasparente section slugs
$at_sections = array(
    'amministrazione-trasparente',
    'disposizioni-generali',
    'organizzazione',
    'consulenti-e-collaboratori',
    'personale',
    'bandi-di-concorso',
    'performance',
    'enti-controllati',
    'attivita-e-procedimenti',
    'provvedimenti',
    'bandi-di-gara-e-contratti',
    'sovvenzioni-contributi-sussidi-vantaggi-economici',
    'bilanci',
    'beni-immobili-e-gestione-patrimonio',
    'controlli-e-rilievi-sullamministrazione',
    'servizi-erogati',
    'pagamenti-dellamministrazione',
    'opere-pubbliche',
    'pianificazione-e-governo-del-territorio',
    'informazioni-ambientali',
    'strutture-sanitarie-private-accreditate',
    'interventi-straordinari-e-di-emergenza',
    'altri-contenuti'
);

if ( $current_page && isset( $current_page->post_name ) ) {
    $current_slug = $current_page->post_name;
    
    // Check if current page is one of the Amministrazione Trasparente sections
    if ( in_array( $current_slug, $at_sections ) ) {
        $should_show_menu = true;
    } else {
        // Check if current page is a child/descendant of "amministrazione-trasparente"
        $current_ancestors = get_post_ancestors( $current_page );
        foreach ( $current_ancestors as $ancestor_id ) {
            $ancestor = get_post( $ancestor_id );
            if ( $ancestor ) {
                // Check if ancestor is amministrazione-trasparente or any AT section
                if ( $ancestor->post_name === 'amministrazione-trasparente' || in_array( $ancestor->post_name, $at_sections ) ) {
                    $should_show_menu = true;
                    break;
                }
            }
        }
        
        // Also check if the page URL contains "amministrazione-trasparente" (for pages with different slugs)
        if ( !$should_show_menu ) {
            $page_url = get_permalink( $current_page->ID );
            if ( $page_url && stripos( $page_url, 'amministrazione-trasparente' ) !== false ) {
                $should_show_menu = true;
            }
        }
    }
}
?>

<div class="site-content">
    <div class="container">
        <?php if ( $should_show_menu ) : ?>
            <main id="main" class="site-main">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('page-content'); ?>>
                        
                        <?php if ( ! is_front_page() ) : ?>
                            <header class="entry-header">
                                <h1 class="page-title"><?php the_title(); ?></h1>
                                
                                <!-- Page Meta Information -->
                                <?php do_action( 'astra_after_page_title' ); ?>
                            </header>
                        <?php endif; ?>

                        <div class="entry-content">
                            <div class="row">
                                <div class="col-md-6 content-column">
                                    <div class="page-content-wrapper">
                                        <?php
                                        // Capture content to prevent it from breaking our structure
                                        ob_start();
                                        the_content();
                                        $content_output = ob_get_clean();
                                        // Ensure content doesn't close our wrapper divs prematurely
                                        // Remove any closing div tags at the end that might close our structure
                                        echo $content_output;

                                        wp_link_pages(
                                            array(
                                                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'astra' ),
                                                'after'  => '</div>',
                                            )
                                        );
                                        ?>
                                    </div><!-- .page-content-wrapper -->
                                </div><!-- .col-md-6 .content-column -->
                                
                                <div class="col-md-6 sidebar-column">
                                    <div class="dynamic-menu-sidebar">
                                        <?php echo do_shortcode( '[at_dynamic_menu title="Menu Amministrazione Trasparente" search="true"]' ); ?>
                                    </div>
                                </div><!-- .col-md-6 .sidebar-column -->
                            </div><!-- .row -->
                        </div><!-- .entry-content -->

                        <?php if ( get_edit_post_link() ) : ?>
                            <footer class="entry-footer">
                                <?php
                                edit_post_link(
                                    sprintf(
                                        wp_kses(
                                            /* translators: %s: Name of current post. Only visible to screen readers */
                                            __( 'Edit <span class="screen-reader-text">%s</span>', 'astra' ),
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
                        <?php endif; ?>
                    </article><!-- #post-<?php the_ID(); ?> -->

                    <?php
                    // If comments are open or we have at least one comment, load up the comment template.
                    if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;

                endwhile; // End of the loop.
                ?>
            </main><!-- #main -->
        <?php else : ?>
            <!-- Standard single-column layout for pages without menu -->
            <div class="row">
                <div class="col-md-12">
                    <main id="main" class="site-main">
                        <?php while ( have_posts() ) : the_post(); ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class('page-content'); ?>>
                                
                                <?php if ( ! is_front_page() ) : ?>
                                    <header class="entry-header">
                                        <h1 class="page-title"><?php the_title(); ?></h1>
                                        
                                        <!-- Page Meta Information -->
                                        <?php do_action( 'astra_after_page_title' ); ?>
                                    </header>
                                <?php endif; ?>

                                <div class="entry-content">
                                    <div class="page-content-wrapper">
                                        <?php
                                        the_content();

                                        wp_link_pages(
                                            array(
                                                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'astra' ),
                                                'after'  => '</div>',
                                            )
                                        );
                                        ?>
                                    </div><!-- .page-content-wrapper -->
                                </div><!-- .entry-content -->

                                <?php if ( get_edit_post_link() ) : ?>
                                    <footer class="entry-footer">
                                        <?php
                                        edit_post_link(
                                            sprintf(
                                                wp_kses(
                                                    /* translators: %s: Name of current post. Only visible to screen readers */
                                                    __( 'Edit <span class="screen-reader-text">%s</span>', 'astra' ),
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
                                <?php endif; ?>
                            </article><!-- #post-<?php the_ID(); ?> -->

                            <?php
                            // If comments are open or we have at least one comment, load up the comment template.
                            if ( comments_open() || get_comments_number() ) :
                                comments_template();
                            endif;

                        endwhile; // End of the loop.
                        ?>
                    </main><!-- #main -->
                </div><!-- .col-md-12 -->
            </div><!-- .row -->
        <?php endif; ?>
    </div><!-- .container -->
</div><!-- .site-content -->

<?php get_footer(); ?>
