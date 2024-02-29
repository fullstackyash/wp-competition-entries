<?php
/**
 * WP Competitions Entries Shortcode.
 *
 * @package wp-competition-entries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class for adding shortcode.
 */
class WCE_SHORTCODE {


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'wce_enqueue_assets' ) );
		add_shortcode( 'wce_competition_list', array( $this, 'wce_competition_list_shortcode' ) );
		add_action( 'wp_ajax_wce_competition_list', array( $this, 'wce_competition_list_render_results' ) );
		add_action( 'wp_ajax_nopriv_wce_competition_list', array( $this, 'wce_competition_list_render_results' ) );
		add_shortcode( 'wce_entry_form', array( $this, 'wce_entry_form' ) );
		add_action( 'wp_ajax_wce_submit_entry_form', array( $this, 'wce_submit_entry_form' ) );
		add_action( 'wp_ajax_nopriv_wce_submit_entry_form', array( $this, 'wce_submit_entry_form' ) );
	}

	/**
	 * Competition Shortcode.
	 *
	 * @return mixed
	 */
	public function wce_competition_list_shortcode() {
		ob_start();
		?>
		<div class="competition_list_wrapper alignwide">
		<?php
		$this->wce_competition_list_render_results();
		?>
		</div>
		<?php
		return ob_get_clean();
	}


	/**
	 * Competition list results.
	 *
	 * @return string
	 */
	public function wce_competition_list_render_results() {
		if ( ! empty( $_POST ) && ! isset( $_POST['_ajaxnonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['_ajaxnonce'] ) ) ) {
			return false;
		}

		$page     = ! empty( $_POST['page'] ) ? filter_input( INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT ) : 1;
		$cur_page = $page;
		$page     = --$page;
		// Set the number of results to display.
		$per_page = 2;
		$start    = $page * $per_page;

		$args = array(
			'post_type'      => COMPETITION_POST_TYPE,
			'post_status'    => 'publish',
			'orderby'        => 'post_date',
			'order'          => 'DESC',
			'posts_per_page' => $per_page,
			'offset'         => $start,
		);

		$competition_query = new WP_Query( $args );
		$competitions      = $competition_query->posts;
		$count             = $competition_query->found_posts;
		?>
		<div class="competition_list">
			<table>               
				<tr>
					<th><?php esc_html_e( 'No', 'wp-competition-entries' ); ?></th>
					<th><?php esc_html_e( 'Competition Title', 'wp-competition-entries' ); ?></th>
					<th><?php esc_html_e( 'Description', 'wp-competition-entries' ); ?></th>
					<th><?php esc_html_e( 'Image', 'wp-competition-entries' ); ?></th>
				</tr>
				
				<?php
				$competition_count = 0 === $page ? $page + 1 : 1 + ( $per_page * $page );
				if ( ! empty( $competitions ) ) :
					foreach ( $competitions as $competition ) {
						$description = get_the_excerpt( $competition->ID );
						$image       = get_the_post_thumbnail( $competition->ID )

						?>
						<tr>
							<td><?php echo esc_html( $competition_count ); ?></td>
							<td><a target="_blank" href="<?php the_permalink( $competition->ID ); ?>"><?php echo esc_html( $competition->post_title ); ?></a></td>
							<td><?php echo wp_kses_post( $description ); ?></td>
							<td><?php echo wp_kses_post( $image ); ?></td>
						</tr>
						<?php
						++$competition_count;
					}
				else :
					?>
					<tr>
						<td>
						<?php esc_html_e( 'No Records Found', 'wp-competition-entries' ); ?>
						</td>
						<td></td><td></td><td></td><td></td><td></td>                       
					</tr>
					<?php
				endif;
				?>
						   
			</table>
		</div>
		<?php
		$this->wce_pagination( $count, $per_page, $cur_page );
		if ( ! empty( $_POST ) ) {
			$html = ob_get_clean();
			wp_send_json_success( $html );
		}
	}

	/**
	 * Pagination function.
	 *
	 * @param int $count total post count.
	 * @param int $per_page total post count in a page.
	 * @param int $cur_page current page.
	 *
	 * @return void
	 */
	public function wce_pagination( $count, $per_page, $cur_page ): void {
		$previous_btn = true;
		$next_btn     = true;
		$first_btn    = false;
		$last_btn     = false;

		$no_of_paginations = ceil( $count / $per_page );

		if ( $cur_page >= 7 ) {
			$start_loop = $cur_page - 3;
			if ( $no_of_paginations > $cur_page + 3 ) {
				$end_loop = $cur_page + 3;
			} elseif ( $cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6 ) {
				$start_loop = $no_of_paginations - 6;
				$end_loop   = $no_of_paginations;
			} else {
				$end_loop = $no_of_paginations;
			}
		} else {
			$start_loop = 1;
			if ( $no_of_paginations > 7 ) {
				$end_loop = 7;
			} else {
				$end_loop = $no_of_paginations;
			}
		}
		// Pagination Buttons logic.
		$pag_container  = '';
		$pag_container .= "
        <div class='wce-universal-pagination'>
            <ul>";

		if ( $first_btn && $cur_page > 1 ) {
			$pag_container .= "<li p='1' class='active'>First</li>";
		} elseif ( $first_btn ) {
			$pag_container .= "<li p='1' class='inactive'>First</li>";
		}

		if ( $previous_btn && $cur_page > 1 ) {
			$pre            = $cur_page - 1;
			$pag_container .= "<li p='$pre' class='active'>Previous</li>";
		} elseif ( $previous_btn ) {
			$pag_container .= "<li class='inactive'>Previous</li>";
		}
		for ( $i = $start_loop; $i <= $end_loop; $i++ ) {

			if ( $cur_page == $i ) {
				$pag_container .= "<li p='$i' class = 'selected' >{$i}</li>";
			} else {
				$pag_container .= "<li p='$i' class='active'>{$i}</li>";
			}
		}

		if ( $next_btn && $cur_page < $no_of_paginations ) {
			$nex            = $cur_page + 1;
			$pag_container .= "<li p='$nex' class='active'>Next</li>";
		} elseif ( $next_btn ) {
			$pag_container .= "<li class='inactive'>Next</li>";
		}

		if ( $last_btn && $cur_page < $no_of_paginations ) {
			$pag_container .= "<li p='$no_of_paginations' class='active'>Last</li>";
		} elseif ( $last_btn ) {
			$pag_container .= "<li p='$no_of_paginations' class='inactive'>Last</li>";
		}

		$pag_container = $pag_container . '
            </ul>
        </div>';

		// We echo the final output.
		echo '<div class = "wce-pagination-nav">' . wp_kses(
			$pag_container,
			array(
				'li'  => array(
					'p'     => array(),
					'class' => array(),
				),
				'ul'  => array(),
				'div' => array( 'class' => array() ),
			)
		) . '</div>';
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function wce_enqueue_assets(): void {

		$plugin_name = basename( WP_COMPETITION_ENTRIES_DIR );
		// Enqueue style.
		wp_register_style(
			'wce-frontend-style',
			plugins_url( $plugin_name . '/src/styles/wce-frontend.css' ),
			array(),
			filemtime( WP_COMPETITION_ENTRIES_DIR . '/src/styles/wce-frontend.css' )
		);
		wp_enqueue_style( 'wce-frontend-style' );

		// Enqueue script.
		wp_register_script( 'wce-frontend-script', plugins_url( $plugin_name . '/src/scripts/wce-frontend.js' ), array(), filemtime( WP_COMPETITION_ENTRIES_DIR . '/src/scripts/wce-frontend.js' ), true );
		wp_localize_script(
			'wce-frontend-script',
			'ajaxload_params',
			array(
				// 'ajax_url' => site_url() . '/wp-admin/admin-ajax.php',
				'ajax_url' => admin_url( 'admin-ajax.php', 'http' ),
				'nonce'    => wp_create_nonce( 'ajax-nonce' ),
			)
		);

		wp_enqueue_script( 'wce-frontend-script' );
	}

	/**
	 * Displays entry form on the competition page.
	 *
	 * @param array $atts, Attributes.
	 * @return void
	 * @since 1.0.0
	 */
	public function wce_entry_form( $atts ) {
		global $post;
		$post_id = $post->ID;
		ob_start();
		$html = '';
		?>
		<div class="entry-form">
			<div class="success"></div>
			<form method="post" id="entry-form">
				<input type="hidden" value="<?php echo esc_attr( $post_id ); ?>" id="competition-id" />
				<table>
					<tbody>
						<tr class="form-field">
							<td class="label-td">
								<label for="first-name">
									<?php esc_html_e( 'First Name', 'wp-competition-entries' ); ?>
									<span class="req">*</span>
								</label>
							</td>
							<td class="field-td">
								<input type="text" id="first-name" required class="input-field" />
								<span class="err-msg"></span>
							</td>
						</tr>
						<tr class="form-field">
							<td class="label-td">
								<label for="last-name">
									<?php esc_html_e( 'Last Name', 'wp-competition-entries' ); ?>
									<span class="req">*</span>
								</label>
							</td>
							<td class="field-td">
								<input type="text" id="last-name" required class="input-field" />
								<span class="err-msg"></span>
							</td>
						</tr>
						<tr class="form-field">
							<td class="label-td">
								<label for="email">
									<?php esc_html_e( 'Email', 'wp-competition-entries' ); ?>
									<span class="req">*</span>
								</label>
							</td>
							<td class="field-td">
								<input type="email" id="email" required class="input-field" />
								<span class="err-msg"></span>
							</td>
						</tr>
						<tr class="form-field">
							<td class="label-td">
								<label for="phone">
									<?php esc_html_e( 'Phone', 'wp-competition-entries' ); ?>
									<span class="req">*</span>
								</label>
							</td>
							<td class="field-td">
								<input type="text" id="phone" required class="input-field" />
								<span class="err-msg"></span>
							</td>
						</tr>
						<tr class="form-field">
							<td class="label-td">
								<label for="description">
									<?php esc_html_e( 'Description', 'wp-competition-entries' ); ?>
								</label>
							</td>
							<td class="field-td">
								<textarea id="description" class="input-area"></textarea>
							</td>
						</tr>
					<tbody>
				</table>
				<button type="submit" class="submit-btn" id="entry-submit"><?php esc_html_e( 'Submit Entry', 'wp-competition-entries' ); ?></button>
			</form>
		</div>
		<?php
		$html = ob_get_clean();
		return $html;
	}

	/**
	 * Send entries to post.
	 *
	 * @return void
	 */
	public function wce_submit_entry_form() {
		// Check nonce.
		if ( ! isset( $_POST['_ajaxnonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['_ajaxnonce'] ), 'ajax-load-event' ) ) {
			return false;

		}

		$postData = filter_input_array( INPUT_POST );
		if ( empty( $postData ) ) {
			return false;
		}

		$html            = '';
		$user_email      = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_EMAIL );
		$competition_id  = filter_input( INPUT_POST, 'competitionId', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$user_first_name = filter_input( INPUT_POST, 'firstName', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$user_phone      = filter_input( INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$user_last_name  = filter_input( INPUT_POST, 'lastName', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$user_message    = filter_input( INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! empty( $user_email ) && ! empty( $user_first_name ) && ! empty( $user_last_name ) && ! empty( $user_phone ) ) {
			$args    = array(
				'post_title'   => $user_first_name . '-' . $user_last_name,
				'post_status'  => 'publish',
				'post_type'    => 'entries',
				'post_content' => $user_message,
				'post_author'  => get_option( 'admin_email' ),
			);
			$post_id = wp_insert_post( $args, true );
			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, 'wce_first_name', sanitize_text_field( $user_first_name ) );
				update_post_meta( $post_id, 'wce_last_name', sanitize_text_field( $user_last_name ) );
				update_post_meta( $post_id, 'wce_email', sanitize_text_field( $user_email ) );
				update_post_meta( $post_id, 'wce_phone', sanitize_text_field( $user_phone ) );
				update_post_meta( $post_id, 'wce_competition_id', sanitize_text_field( $competition_id ) );
				update_post_meta( $post_id, 'wce_description', sanitize_text_field( $user_message ) );
				$html .= __( 'Thank you for your interest. We will contact you soon..', 'wp-competition-entries' );
			} else {
				$html .= '<span class="unex-error">' . __( 'Your entries could not be submitted due to an unexpected error. Please contact the administrator.', 'wp-competition-entries' ) . '</span>';
			}
		} else {
			$html .= '<span class="unex-error">' . __( 'One or more fields are invalid or empty', 'wp-competition-entries' ) . '</span>';
		}

		wp_send_json_success( $html );
	}
}

$wce_shortcode = new WCE_SHORTCODE();
