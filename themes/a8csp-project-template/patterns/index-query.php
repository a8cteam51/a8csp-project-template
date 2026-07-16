<?php
/**
 * Title: Index Query
 * Slug: a8csp-project-template/index-query
 * Inserter: no
 *
 * The index template's post list lives in this pattern rather than in `templates/index.html`
 * because template HTML cannot carry translatable strings; the pattern's PHP can.
 */

?>
<!-- wp:query {"queryId":0,"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"align":"wide"} -->
<div class="wp-block-query alignwide">
	<!-- wp:post-template -->
		<!-- wp:post-title {"isLink":true} /-->

		<!-- wp:post-date /-->

		<!-- wp:post-excerpt {"moreText":"<?php echo esc_attr_x( 'Continue reading', 'read-more link text', 'a8csp-project-template' ); ?>"} /-->
	<!-- /wp:post-template -->

	<!-- wp:query-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"space-between"}} -->
		<!-- wp:query-pagination-previous /-->

		<!-- wp:query-pagination-numbers /-->

		<!-- wp:query-pagination-next /-->
	<!-- /wp:query-pagination -->

	<!-- wp:query-no-results -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'No posts were found.', 'a8csp-project-template' ); ?></p>
		<!-- /wp:paragraph -->
	<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
