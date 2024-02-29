<?php
/**
 * Register post type.
 *
 * @package wp-competition-entries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class for registering the custom post type.
 */
class WCE_POST_TYPE {


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'wce_setup_post_type' ) );
		add_filter( 'manage_' . ENTRIES_POST_TYPE . '_posts_columns', array( $this, 'wce_add_custom_columns' ) );
		add_action( 'manage_' . ENTRIES_POST_TYPE . '_posts_custom_column', array( $this, 'wce_add_custom_columns_content' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'wce_competition_detail_template' ) );
		add_action( 'init', array( $this, 'wce_custom_rewrite_rule' ) );
		add_action( 'init', array( $this, 'wce_custom_rewrite_tag' ) );
		add_action( 'query_vars', array( $this, 'wce_custom_query_var' ) );
	}

	/**
	 * Add Custom Columns for entries post type.
	 *
	 * @param array $columns columns Array.
	 * @return array
	 */
	public function wce_add_custom_columns( $columns ): array {

		$columns['first_name']     = __( 'First Name', 'wp-competition-entries' );
		$columns['last_name']      = __( 'Last Name', 'wp-competition-entries' );
		$columns['email']          = __( 'Email', 'wp-competition-entries' );
		$columns['phone']          = __( 'Phone', 'wp-competition-entries' );
		$columns['competition_id'] = __( 'Competition ID', 'wp-competition-entries' );

		return $columns;
	}

	/**
	 * Add content for custom columns for entries post type.
	 *
	 * @param array $column Columns Array.
	 * @param int   $post_id  Post id.
	 * @return void
	 */
	public function wce_add_custom_columns_content( $column, $post_id ): void {
		$first_name     = get_post_meta( $post_id, 'wce_first_name', true );
		$last_name      = get_post_meta( $post_id, 'wce_last_name', true );
		$email          = get_post_meta( $post_id, 'wce_email', true );
		$phone          = get_post_meta( $post_id, 'wce_phone', true );
		$competition_id = get_post_meta( $post_id, 'wce_competition_id', true );

		if ( 'first_name' === $column ) {
			echo ! empty( $first_name ) ? esc_html( $first_name ) : '';
		}

		if ( 'last_name' === $column ) {
			echo ! empty( $last_name ) ? esc_html( $last_name ) : '';
		}

		if ( 'email' === $column ) {
			echo ! empty( $email ) ? esc_html( $email ) : '';
		}

		if ( 'phone' === $column ) {
			echo ! empty( $phone ) ? esc_html( $phone ) : '';
		}

		if ( 'competition_id' === $column ) {
			echo ! empty( $competition_id ) ? wp_kses_post( '<a target="_blank" href="' . admin_url() . 'post.php?post=' . $competition_id . '&action=edit" >' . $competition_id . '</a>' ) : '';
		}
	}

	/**
	 * Register the custom post type.
	 *
	 * @return void
	 */
	public function wce_setup_post_type(): void {
		register_post_type(
			COMPETITION_POST_TYPE,
			array(
				'labels'             => array(
					'name'          => __( 'Competitions', 'wp-competition-entries' ),
					'singular_name' => __( 'Competition', 'wp-competition-entries' ),
					'search_items'  => __( 'Search Competitions', 'wp-competition-entries' ),
					'menu_name'     => __( 'Competitions', 'wp-competition-entries' ),
				),
				'public'             => true,
				'has_archive'        => true,
				'show_in_menu'       => true,
				'show_ui'            => true,
				'menu_icon'          => 'dashicons-tickets-alt',
				'supports'           => array( 'title', 'thumbnail', 'editor', 'featured-image', 'custom-fields' ),
				'show_in_rest'       => true,
				'publicly_queryable' => true,
				'rewrite'            => true,

			)
		);

		register_post_type(
			ENTRIES_POST_TYPE,
			array(
				'labels'             => array(
					'name'          => __( 'Entries', 'wp-competition-entries' ),
					'singular_name' => __( 'Entry', 'wp-competition-entries' ),
					'search_items'  => __( 'Search Entries', 'wp-competition-entries' ),
					'menu_name'     => __( 'Entries', 'wp-competition-entries' ),
				),
				'public'             => false,
				'has_archive'        => false,
				'show_in_menu'       => true,
				'show_ui'            => true,
				'menu_icon'          => 'dashicons-list-view',
				'supports'           => array( 'title', 'thumbnail', 'editor', 'featured-image', 'custom-fields' ),
				'show_in_rest'       => true,
				'publicly_queryable' => true,
				'rewrite'            => true,

			)
		);
	}

	/**
	 * Custom competition template to render on frontend.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function wce_competition_detail_template( $content ): string {
		if ( is_singular( COMPETITION_POST_TYPE ) ) {
			if ( get_query_var( 'submit-entry' ) === 'true' ) {
				// Show the Entry Form.
				$content = do_shortcode( '[wce_entry_form]' );
			} else {
				$content .= '<a href=' . get_the_permalink() . 'submit-entry class="submit-entry-btn">Submit Entry</a>';
			}
		}
		return $content;
	}

	/**
	 * Custom rewrite rule.
	 *
	 * @return void
	 */
	public function wce_custom_rewrite_rule() {
		add_rewrite_rule( '^competitions/([^/]+)/submit-entry/?$', 'index.php?post_type=competitions&name=$matches[1]&submit-entry=true', 'top' );
	}

	/**
	 * Custom rewrite tag.
	 *
	 * @return void
	 */
	public function wce_custom_rewrite_tag() {
		add_rewrite_tag( '%title', '([^&]+)' );
	}

	/**
	 * Custom query vars.
	 *
	 * @param array $query_vars Query vars.
	 * @return array
	 */
	public function wce_custom_query_var( $query_vars ) {
		$query_vars[] = 'submit-entry';
		return $query_vars;
	}
}

$wce_post_type = new WCE_POST_TYPE();
