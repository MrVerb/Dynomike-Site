<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package dynomike
 */

?>
<?php if (function_exists('z_taxonomy_image')) z_taxonomy_image(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<div class="row post_row">
	<header class="entry-header">
		<div class="dt-3">
			<div class="post_img">
				<?php the_post_thumbnail(  ) ?>
			</div>
		</div>
	</header><!-- .entry-header -->
	<div class="dt-9">
	<div class="entry-content">
		<?php
		if (is_single() ) : 
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h1 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h1>' );
		endif;
		
			the_content( sprintf(
				/* translators: %s: Name of current post. */
				wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'dynomike' ), array( 'span' => array( 'class' => array() ) ) ),
				the_title( '<span class="screen-reader-text">"', '"</span>', false )
			) );

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'dynomike' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->
	</div>
</div><!--- end row -->
	<footer class="entry-footer">
		
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->
