<?php
/**
 * Loader file.
 *
 * @package wp-competition-entries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Loader class for plugin activation and deactivation hooks.
 */
class Loader {

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( WP_COMPETITION_ENTRIES__FILE__, array( $this, 'wce_activate' ) );
		register_deactivation_hook( WP_COMPETITION_ENTRIES__FILE__, array( $this, 'wce_deactivate' ) );
	}

	/**
	 * Activate the plugin.
	 *
	 * @return void
	 */
	public function wce_activate(): void {
		$wce_post_type = new WCE_POST_TYPE();
		// Trigger our function that registers the custom post type.
		$wce_post_type->wce_setup_post_type();
		// Clear the permalinks after the post type has been registered.
		flush_rewrite_rules();
	}

	/**
	 * Deactivation hook.
	 *
	 * @return void
	 */
	public function wce_deactivate(): void {
		// Unregister the post type, so the rules are no longer in memory.
		unregister_post_type( COMPETITION_POST_TYPE );
		unregister_post_type( ENTRIES_POST_TYPE );
		// Clear the permalinks to remove our post type's rules from the database.
		flush_rewrite_rules();
	}
}

$loader = new Loader();
