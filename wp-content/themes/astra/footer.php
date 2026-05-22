<?php
/**
 * Footer — Cesare Benedetti uses the custom footer plugin (wp_footer hook).
 *
 * @package Astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php astra_content_bottom(); ?>
	</div><!-- ast-container -->
	</div><!-- #content -->
<?php
	astra_content_after();
	astra_footer_before();
	astra_footer_after();
?>
	</div><!-- #page -->
<?php
	astra_body_bottom();
	wp_footer();
?>
	</body>
</html>
