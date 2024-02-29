<?php
/**
 * Plugin Name:     WP Competition Entries
 * Plugin URI:      https://github.com/fullstackyash/wp-competition-entries
 * Description:     This is a wordpress plugin that takes user entries to available competitions
 * Author:          Yash Chopra
 * Author URI:      https://github.com/fullstackyash
 * Text Domain:     wp-competition-entries
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         wp-competition-entries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'WP_COMPETITION_ENTRIES_DIR' ) ) {
	define( 'WP_COMPETITION_ENTRIES_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

if ( ! defined( 'WP_COMPETITION_ENTRIES__FILE__' ) ) {
	define( 'WP_COMPETITION_ENTRIES__FILE__', __FILE__ );
}

require WP_COMPETITION_ENTRIES_DIR . '/includes/constants.php';
require WP_COMPETITION_ENTRIES_DIR . '/includes/admin/class-loader.php';
require WP_COMPETITION_ENTRIES_DIR . '/includes/admin/class-wce-post-type.php';
require WP_COMPETITION_ENTRIES_DIR . '/includes/admin/class-wce-meta-fields.php';
require WP_COMPETITION_ENTRIES_DIR . '/includes/shortcodes/class-wce-shortcode.php';
