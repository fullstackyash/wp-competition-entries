<?php
/**
 * Register meta fields for custom post type.
 *
 * @package wp-competition-entries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Class for registering the custom post type meta fields.
 */
class WCE_META_FIELDS {


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'wce_add_meta_box' ) );
		add_action( 'save_post', array( $this, 'wce_save_meta_data' ), 10, 2 );
	}

	/**
	 * Adds the meta box container.
	 *
	 * @return void
	 */
	public function wce_add_meta_box(): void {
		add_meta_box(
			'entry_details',
			__( 'Entry Details', 'wp-competition-entries' ),
			array( $this, 'wce_render_entry_meta_box' ),
			ENTRIES_POST_TYPE,
			'side',
			'high'
		);
	}

	/**
	 * Render custom meta box.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function wce_render_entry_meta_box( $post ): void {

		// Add an nonce field for security.
		wp_nonce_field( 'wce_render_meta_box_action', 'wce_render_meta_box_nonce_field' );

		// Reterive current meta vales.
		$wce_first_name     = get_post_meta( $post->ID, 'wce_first_name', true );
		$wce_last_name      = get_post_meta( $post->ID, 'wce_last_name', true );
		$wce_email          = get_post_meta( $post->ID, 'wce_email', true );
		$wce_phone          = get_post_meta( $post->ID, 'wce_phone', true );
		$wce_description    = get_post_meta( $post->ID, 'wce_description', true );
		$wce_competition_id = get_post_meta( $post->ID, 'wce_competition_id', true );
		?>

		<div class="entry_settings">
			<div class="first_name">
				<p>
					<label for="wce_first_name">
						<?php esc_html_e( 'First Name: ', 'wp-competition-entries' ); ?>
					</label>
				</p>
				<p>    
					<input type="text" value="<?php echo esc_attr( $wce_first_name ); ?>" id="wce_first_name" name="wce_first_name" />
				</p>    			
			</div>
		<div class="last_name">
				<p>
					<label for="wce_last_name">
						<?php esc_html_e( 'Last Name: ', 'wp-competition-entries' ); ?>
					</label>
				</p>
				<p>    
					<input type="text" value="<?php echo esc_attr( $wce_last_name ); ?>" id="wce_last_name" name="wce_last_name" />
				</p>    			
			</div>
		<div class="email">
				<p>
					<label for="wce_email">
						<?php esc_html_e( 'Email: ', 'wp-competition-entries' ); ?>
					</label>
				</p>
				<p>    
					<input type="text" value="<?php echo esc_attr( $wce_email ); ?>" id="wce_email" name="wce_email" />
				</p>    			
			</div>
		<div class="phone">
				<p>
					<label for="wce_phone">
						<?php esc_html_e( 'Phone: ', 'wp-competition-entries' ); ?>
					</label>
				</p>
				<p>    
					<input type="text" value="<?php echo esc_attr( $wce_phone ); ?>" id="wce_phone" name="wce_phone" />
				</p>    			
			</div>
		<div class="description">
				<p>
					<label for="wce_description">
						<?php esc_html_e( 'Description: ', 'wp-competition-entries' ); ?>
					</label>
				</p>
				<p>    
					<textarea id="wce_description" name="wce_description" ><?php echo esc_attr( $wce_description ); ?></textarea>
				</p>    			
			</div>
		<div class="competition_id">
				<p>
					<label for="wce_competition_id">
						<?php esc_html_e( 'Competition ID: ', 'wp-competition-entries' ); ?>
					</label>
				</p>
				<p>    
					<input type="text" value="<?php echo esc_attr( $wce_competition_id ); ?>" id="wce_competition_id" name="wce_competition_id" />
				</p>    			
			</div>
		</div>
		<?php
	}

	/**
	 * Save meta field values when the post is saved.
	 *
	 * @param int     $post_id The ID of the post being saved.
	 * @param WP_Post $post The post object.
	 */
	public function wce_save_meta_data( $post_id, $post ) {

		/* bail out if this is an autosave. */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		/* Verify the nonce before proceeding. */
		if ( ! isset( $_POST['wce_render_meta_box_nonce_field'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wce_render_meta_box_nonce_field'] ), 'wce_render_meta_box_action' ) ) {
			return $post_id;
		}

		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Check if the current user has permission to edit the post. */
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return $post_id;
		}

		if ( isset( $_POST['wce_first_name'] ) ) {
			/* Get the posted data and sanitize it. */
			$price = sanitize_text_field( wp_unslash( $_POST['wce_first_name'] ) );
			/* Add, Update, Delete Meta Values. */
			$this->wce_crud_post_meta( $post_id, 'wce_first_name', $price );
		}
		if ( isset( $_POST['wce_last_name'] ) ) {
			/* Get the posted data and sanitize it. */
			$rating = sanitize_text_field( wp_unslash( $_POST['wce_last_name'] ) );
			/* Add, Update, Delete Meta Values. */
			$this->wce_crud_post_meta( $post_id, 'wce_last_name', $rating );
		}
		if ( isset( $_POST['wce_email'] ) ) {
			/* Get the posted data and sanitize it. */
			$rating = sanitize_text_field( wp_unslash( $_POST['wce_email'] ) );
			/* Add, Update, Delete Meta Values. */
			$this->wce_crud_post_meta( $post_id, 'wce_email', $rating );
		}
		if ( isset( $_POST['wce_phone'] ) ) {
			/* Get the posted data and sanitize it. */
			$rating = sanitize_text_field( wp_unslash( $_POST['wce_phone'] ) );
			/* Add, Update, Delete Meta Values. */
			$this->wce_crud_post_meta( $post_id, 'wce_phone', $rating );
		}
		if ( isset( $_POST['wce_description'] ) ) {
			/* Get the posted data and sanitize it. */
			$rating = sanitize_textarea_field( wp_unslash( $_POST['wce_description'] ) );
			/* Add, Update, Delete Meta Values. */
			$this->wce_crud_post_meta( $post_id, 'wce_description', $rating );
		}
		if ( isset( $_POST['wce_competition_id'] ) ) {
			/* Get the posted data and sanitize it. */
			$rating = sanitize_text_field( wp_unslash( $_POST['wce_competition_id'] ) );
			/* Add, Update, Delete Meta Values. */
			$this->wce_crud_post_meta( $post_id, 'wce_competition_id', $rating );
		}
	}


	/**
	 * Perform crud options for meta.
	 *
	 * @param int    $post_id The ID of the post being saved.
	 * @param string $meta_key The meta key of field.
	 * @param string $new_meta_value The new meta value of field.
	 */
	public function wce_crud_post_meta( $post_id, $meta_key, $new_meta_value ) {

		/* Get the meta value of the custom field key. */
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		/* If a new meta value was added and there was no previous value, add it. */
		if ( $new_meta_value && '' === $meta_value ) {
			add_post_meta( $post_id, $meta_key, $new_meta_value, true );
		} elseif ( $new_meta_value && $new_meta_value !== $meta_value ) {  /* If the new meta value does not match the old value, update it. */
			update_post_meta( $post_id, $meta_key, $new_meta_value );
		} elseif ( '' === $new_meta_value && $meta_value ) { /* If there is no new meta value but an old value exists, delete it. */
			delete_post_meta( $post_id, $meta_key, $meta_value );
		}
	}
}

$wce_meta_fields = new WCE_META_FIELDS();