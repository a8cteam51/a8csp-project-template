<?php declare( strict_types=1 );
/**
 * Integration coverage for the Book Count block.
 *
 * @package  A8C\SpecialProjects\ProjectTemplate
 */

/**
 * The Book Count block registers from the features plugin's build manifest and renders on the
 * server only while the features plugin is enabled.
 */
final class BookCountBlockTest extends \PHPUnit\Framework\TestCase {
	// region TESTS.

	/**
	 * Confirms the block is registered as a dynamic block.
	 *
	 * @return  void
	 */
	public function test_book_count_block_is_registered(): void {
		$this->skip_when_features_plugin_disabled();

		$block_type = \WP_Block_Type_Registry::get_instance()->get_registered( 'a8csp-project-template/book-count' );

		self::assertInstanceOf( \WP_Block_Type::class, $block_type );
		self::assertTrue( $block_type->is_dynamic() );
	}

	/**
	 * Confirms the block renders the published Book count, drafts excluded, as a link to the Book archive.
	 *
	 * The suite runs against a fresh site, where a count stuck at zero would read right too, so the
	 * test adds a published and a draft Book of its own and deletes both again.
	 *
	 * @return  void
	 */
	public function test_book_count_block_renders_the_count_linked_to_the_archive(): void {
		$this->skip_when_features_plugin_disabled();

		$published_count = (int) wp_count_posts( 'a8csp_template_book' )->publish;

		$book_ids = array();
		try {
			foreach ( array( 'publish', 'draft' ) as $post_status ) {
				$book_id = wp_insert_post(
					array(
						'post_type'   => 'a8csp_template_book',
						'post_status' => $post_status,
						'post_title'  => "Book Count test ({$post_status})",
					),
					true
				);
				self::assertIsInt( $book_id );
				$book_ids[] = $book_id;
			}

			$output = do_blocks( '<!-- wp:a8csp-project-template/book-count /-->' );
		} finally {
			foreach ( $book_ids as $book_id ) {
				wp_delete_post( $book_id, true );
			}
		}

		self::assertStringContainsString( 'wp-block-a8csp-project-template-book-count', $output );
		self::assertMatchesRegularExpression( '#<a href="' . \preg_quote( esc_url( (string) get_post_type_archive_link( 'a8csp_template_book' ) ), '#' ) . '">' . \preg_quote( number_format_i18n( $published_count + 1 ), '#' ) . ' books?</a>#', $output );
	}

	/**
	 * Confirms the block stays unregistered while the features plugin is disabled.
	 *
	 * @return  void
	 */
	public function test_book_count_block_is_not_registered_while_the_features_plugin_is_disabled(): void {
		if ( ! \file_exists( __DIR__ . '/../../mu-plugins/a8csp-project-template-features/.disabled' ) ) {
			self::markTestSkipped( 'The features plugin is enabled; this test covers its disabled state.' );
		}

		// The built block's presence is this test's own validity precondition.
		self::assertFileExists( __DIR__ . '/../../mu-plugins/a8csp-project-template-features/blocks/build/book-count/block.json' );
		self::assertFalse( \WP_Block_Type_Registry::get_instance()->is_registered( 'a8csp-project-template/book-count' ) );
	}

	// endregion.

	// region HELPERS.

	/**
	 * Skips the calling test when the features plugin is disabled. The check reads the `.disabled`
	 * marker rather than a loaded function, so an enabled plugin that fails to load runs the tests.
	 *
	 * @return  void
	 */
	private function skip_when_features_plugin_disabled(): void {
		if ( \file_exists( __DIR__ . '/../../mu-plugins/a8csp-project-template-features/.disabled' ) ) {
			self::markTestSkipped( 'The features plugin is disabled (mu-plugins/a8csp-project-template-features/.disabled); delete that file to enable it.' );
		}
	}

	// endregion.
}
