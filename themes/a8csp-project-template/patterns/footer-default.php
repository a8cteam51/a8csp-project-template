<?php
/**
 * Title: Default Footer
 * Slug: a8csp-project-template/footer-default
 * Categories: footer
 * Block Types: core/template-part/footer
 */

?>
<!-- wp:group {"backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|30","bottom":"var:preset|spacing|40","left":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--30)">
	<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"}} -->
		<div class="wp-block-group">
			<!-- wp:paragraph {"fontSize":"small"} -->
			<p class="has-small-font-size"><?php esc_html_e( 'Built with WordPress.', 'a8csp-project-template' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"a8csp_template/current-year"}}},"fontSize":"small"} -->
			<p class="has-small-font-size"></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:navigation {"overlayMenu":"never","fontSize":"small","layout":{"type":"flex","justifyContent":"right"}} -->
			<!-- wp:page-list /-->
		<!-- /wp:navigation -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
