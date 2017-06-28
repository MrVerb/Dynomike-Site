<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package dynomike
 */

?>

	</div><!-- #content -->
<div class="footer-wrap">	
<div class="wrapper">
	<div class="dt-12">
		<footer id="colophon" class="site-footer" role="contentinfo">
			<div class="copyright">
			<img src="/wp-content/uploads/2016/08/footer-logo.png"></br>
			Copyright &copy; 2016 by GreevingCards LLC</div>
			<div class="site-info">				
				<?php printf( esc_html__( 'Website Design %1$s by %2$s.' ), '', '<a href="http://ubermotif.com" rel="designer">Uber Motif</a>' ); ?>
			</div><!-- .site-info -->
		</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>
	</div>
		</div>
</div>
</body>
</html>
