<?php

/*

Plugin Name: Auto Post Expire

description: Sets an expiration date for posts, auto moving them to draft after the set period.

Version: 1.2

Author: NinjaTech

Author URI: https://ninjatech.agency/

Text Domain: ninja-auto-post-expire

License: GPLv2 or later

License URI: http://www.gnu.org/licenses/gpl-2.0.html

*/



// Exit if accessed directly.

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



global $wpdb;



if ( ! defined( 'NJTAPE_AUTO_POST_EXPIRY_DIR' ) ) {

	define( 'NJTAPE_AUTO_POST_EXPIRY_DIR', __DIR__ );

}



define('NJTAPE_AUTO_POST_EXPIRE_VERSION', '1.0');



// Add menu

if ( ! function_exists( 'njtape_settings_page_main' ) ) {

	function njtape_settings_page_main() {

	  add_options_page('Post Expire Settings', 'Post Expire Settings', 'manage_options', 'njtape_settings_page', 'njtape_settings_page_main_fun');

	}

	add_action('admin_menu', 'njtape_settings_page_main');

}



$njtape_post_expire_option = get_option('njtape_post_expire_option');

if($njtape_post_expire_option == 'yes') {

	//add expiry column to posts

	add_filter(

		'manage_post_posts_columns',

		function ( $columns ) {

			return array_merge( $columns, array( 'post_expire' => __( 'Expiry Date', 'ninja-auto-post-expire' ) ) );

		}

	);



	//expiry column value

	add_action(

		'manage_post_posts_custom_column',

		function ( $column_key, $post_id ) {

			if ( 'post_expire' === $column_key ) {

				$expired_date = get_post_meta( $post_id, '_njtape_expiration_date', true );



				if ( $expired_date ) {

					$expire_draft_date = njtape_post_expiry_datetime_calculate( $post_id );

					if ( 1 === $expire_draft_date ) {

						echo '<span style="color:red">';

						echo esc_attr($expired_date);

						echo '</span>';

					} else {

						echo '<span  style="color:green">';

						echo esc_attr($expired_date);

						echo '</span>';

					}

				}

			}

		},

	10, 2);

}



if ( ! function_exists( 'njtape_add_expiry_column_other_posts' ) ) {

	function njtape_add_expiry_column_other_posts() {

		$all_post_types = njtape_get_all_post_types_list();

		foreach ($all_post_types as $key => $value) {

			$temp_key = 'njtape_'.$key.'_expire_option';

			$$temp_key = get_option('njtape_'.$key.'_expire_option');

			if($$temp_key == 'yes') {

				//add expiry column to posts

				add_filter(

					'manage_'.$key.'_posts_columns',

					function ( $columns ) {

						return array_merge( $columns, array( 'post_expire' => __( 'Expiry Date', 'ninja-auto-post-expire' ) ) );

					}

				);



				//expiry column value

				add_action(

					'manage_'.$key.'_posts_custom_column',

					function ( $column_key, $post_id ) {

						if ( 'post_expire' === $column_key ) {

							$expired_date = get_post_meta( $post_id, '_njtape_expiration_date', true );



							if ( $expired_date ) {

								$expire_draft_date = njtape_post_expiry_datetime_calculate( $post_id );

								if ( 1 === $expire_draft_date ) {

									echo '<span style="color:red">';

									echo esc_attr($expired_date);

									echo '</span>';

								} else {

									echo '<span  style="color:green">';

									echo esc_attr($expired_date);

									echo '</span>';

								}

							}

						}

					},

				10, 2);

			}

		}

	}

	add_action('admin_init', 'njtape_add_expiry_column_other_posts');

}



//calculate expiry date

if ( ! function_exists( 'njtape_post_expiry_datetime_calculate' ) ) {

	function njtape_post_expiry_datetime_calculate( $post_id ) {

		$tz = get_option( 'timezone_string' );

		if ( empty( $tz ) ) {

			$offset = get_option( 'gmt_offset' );

			$tz     = $offset ? sprintf( 'Etc/GMT%+d', $offset ) : 'UTC';

		}



		$field_date   = get_post_meta( $post_id, '_njtape_expiration_date', true );

		$current_time = current_time( 'mysql' );

		

		$first_date  = new DateTime( $current_time, new DateTimeZone( $tz ) );

		$second_date = new DateTime( $field_date, new DateTimeZone( $tz ) );

		

		$interval = $first_date->diff( $second_date );

		return $interval->invert;

	}

}



// Add options

if ( ! function_exists( 'njtape_post_expire_settings' ) ) {

	function njtape_post_expire_settings() 

	{

		register_setting('njtape_expire_settings', 'njtape_post_expire_option', 'sanitize_text_field');



		$all_post_types = njtape_get_all_post_types_list();

		foreach ($all_post_types as $key => $value) {

			$temp_key = 'njtape_'.$key.'_expire_option';

	  	register_setting('njtape_expire_settings', $temp_key, 'sanitize_text_field');

	  }

	}

	add_action( 'admin_init', 'njtape_post_expire_settings' );

}



if ( ! function_exists( 'njtape_settings_page_main_fun' ) ) {

	function njtape_settings_page_main_fun()

	{

		?>

		<div class="wrap">

			<h1><?php echo esc_html__( 'Expiry settings', 'ninja-auto-post-expire' ); ?></h1>

			<p><?php echo esc_html__( 'Configure options for different post types.', 'ninja-auto-post-expire' ); ?></p>

			<form method="post" action="options.php">

				<?php settings_fields( 'njtape_expire_settings' ); ?>

				<?php do_settings_sections( 'njtape_expire_settings' ); ?>

				<table class="form-table">

					<!-- CSS section -->

					<tr valign="top">

						<th scope="row"><?php echo esc_html__( 'Post', 'ninja-auto-post-expire' ); ?> </th>

						<td>

							<?php

								$njtape_post_expire_option = get_option('njtape_post_expire_option');

								if(!$njtape_post_expire_option) {

									$njtape_post_expire_option = 'no';

								}

							?>

							<select id="njtape_post_expire_option" name="njtape_post_expire_option">

								<option value="yes" <?php if($njtape_post_expire_option == 'yes') { echo 'selected="selected"'; } ?>>Yes</option>

								<option value="no" <?php if($njtape_post_expire_option == 'no') { echo 'selected="selected"'; } ?>>No</option>

							</select>

						</td>

					</tr>

					<?php

						$all_post_types = njtape_get_all_post_types_list();

						foreach ($all_post_types as $key => $value) {

							$post_label = str_replace('_', ' ', $value);

					?>

						<tr valign="top">

							<th scope="row" style="text-transform: capitalize;"><?php echo esc_attr($post_label); ?> </th>

							<td>

								<?php

									$temp_key = 'njtape_'.$key.'_expire_option';

									$$temp_key = get_option('njtape_'.$key.'_expire_option');

									if(!$$temp_key) {

										$$temp_key = 'no';

									}

								?>

								<select id="<?php echo esc_attr($temp_key); ?>" name="<?php echo esc_attr($temp_key); ?>">

									<option value="yes" <?php if($$temp_key == 'yes') { echo 'selected="selected"'; } ?>>Yes</option>

									<option value="no" <?php if($$temp_key == 'no') { echo 'selected="selected"'; } ?>>No</option>

								</select>

							</td>

						</tr>

					<?php } ?>

				</table>

				<p class="submit submitbox auto_post_expire-setting-btn">

					<?php 

						submit_button( __( 'Save', 'ninja-auto-post-expire' ), 'primary', 'auto_post_expire-save-settings', false);

					?>

				</p>

			</form>

		</div>

		<?php

	}

}



/*Body class - Options page*/

if ( ! function_exists( 'njtape_admin_body_class' ) ) {

	function njtape_admin_body_class( $classes ) {

		global $pagenow;

		$screen = get_current_screen();



		if ( in_array( $pagenow, array( 'options-general.php' ), true ) && $screen->id === 'settings_page_njtape_settings_page' ) {

			$classes .= ' njt-option-page';

		}



		return $classes;

	}

	add_filter( 'admin_body_class', 'njtape_admin_body_class' );

}



/*Styles - Options page*/

if ( ! function_exists( 'njtape_css_option_page' ) ) {

	function njtape_css_option_page(){

		global $pagenow;

		$screen = get_current_screen(); 



		if ( in_array( $pagenow, array( 'options-general.php' ), true ) &&  $screen->id === 'settings_page_njtape_settings_page' ) {

				wp_enqueue_style('njt-ape-backend-css');

		}

	}

	add_action( 'admin_head', 'njtape_css_option_page' );

}



// Add expiry meta box for posts

if ( ! function_exists( 'njtape_auto_post_expire_expiration_meta_box' ) ) {

	function njtape_auto_post_expire_expiration_meta_box( $post ) {

		$njtape_post_expire_option = get_option('njtape_post_expire_option');

		if($njtape_post_expire_option == 'yes') {

			add_meta_box('njtape_expiration_date_meta', __( 'Expiry Date', 'ninja-auto-post-expire' ), 'njtape_auto_post_expire_create_expiration_meta_box_callback', 'post', 'side', 'low');

		}

		$all_post_types = njtape_get_all_post_types_list();

		foreach ($all_post_types as $key => $value) {

			$temp_key = 'njtape_'.$key.'_expire_option';

			$$temp_key = get_option('njtape_'.$key.'_expire_option');

			if($$temp_key == 'yes') {

				add_meta_box('njtape_expiration_date_meta', __( 'Expiry Date', 'ninja-auto-post-expire' ), 'njtape_auto_post_expire_create_expiration_meta_box_callback', $key, 'side', 'low');

			}

		}

		add_action( 'admin_enqueue_scripts', 'njtape_auto_post_expire_add_datepicker_scripts' );

	}

	add_action( 'add_meta_boxes', 'njtape_auto_post_expire_expiration_meta_box' );

}



if ( ! function_exists( 'njtape_auto_post_expire_create_expiration_meta_box_callback' ) ) {

	function njtape_auto_post_expire_create_expiration_meta_box_callback( $post ) {

		//nonce field

		wp_nonce_field( 'njtape_expiration_date_nonce', 'njtape_expiration_date_nonce' );



		$exp_date = get_post_meta( $post->ID, '_njtape_expiration_date', true );

		echo '<input type="text" name="njtape_expiration_date" id="njtape_expiration_date" class="date-vc" size="35" value="' . esc_attr( $exp_date ) . '" >';

	}

}



add_action( 'save_post', 'njtape_auto_post_expire_save_expiration_meta_box_data' );



// Include datepicker css and js

if ( ! function_exists( 'njtape_auto_post_expire_add_datepicker_scripts' ) ) {

	function njtape_auto_post_expire_add_datepicker_scripts() {

		wp_enqueue_style('njt-ape-dtpickercss', plugin_dir_url( __FILE__ ) . 'backend/datetime/css/jquery.datetimepicker.min.css',

			array(), NJTAPE_AUTO_POST_EXPIRE_VERSION, 'all');

		wp_enqueue_style('njt-ape-styles', plugin_dir_url( __FILE__ ) . 'backend/css/style.css', array(), NJTAPE_AUTO_POST_EXPIRE_VERSION, 'all');

		wp_enqueue_script('njt-ape-dtpicker', plugin_dir_url( __FILE__ ) . 'backend/datetime/js/jquery.datetimepicker.js', array( 'jquery' ), NJTAPE_AUTO_POST_EXPIRE_VERSION, true);

		wp_enqueue_script('njt-ape-backend', plugin_dir_url( __FILE__ ) . 'backend/js/plugin.js', array( 'jquery' ), NJTAPE_AUTO_POST_EXPIRE_VERSION, true);

	}

}



if ( ! function_exists( 'njtape_auto_post_expire_add_inline_style' ) ) {

	function njtape_auto_post_expire_add_inline_style() {

		wp_register_style('njt-ape-backend-css', plugin_dir_url( __FILE__ ) . 'backend/css/backend.css', array(), NJTAPE_AUTO_POST_EXPIRE_VERSION, 'all');

	}

	add_action( 'admin_enqueue_scripts', 'njtape_auto_post_expire_add_inline_style' );

}



//Save meta box expiry field value

if ( ! function_exists( 'njtape_auto_post_expire_save_expiration_meta_box_data' ) ) {

	function njtape_auto_post_expire_save_expiration_meta_box_data( $post_id ) {

		// Check if our nonce is set.

		if ( ! isset( $_POST['njtape_expiration_date_nonce'] ) ) {

			return;

		}



		// Verify for nonce value

		if ( ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['njtape_expiration_date_nonce'])), 'njtape_expiration_date_nonce' ) ) {

			return;

		}



		// autosave

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {

			return;

		}



		// check for permission

		if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {

				return;

			}

		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {

				return;

		}



		// check field value

		if ( ! isset( $_POST['njtape_expiration_date'] ) ) {

			return;

		}

		$exp_date = sanitize_text_field( wp_unslash($_POST['njtape_expiration_date']) );



		// Update the meta field in the database.

		update_post_meta( $post_id, '_njtape_expiration_date', $exp_date );

	}

}



/* Action links*/

if ( ! function_exists( 'njtape_post_expire_action_links' ) ) {

	function njtape_post_expire_action_links ( $links ) {

		$settings_link = array(

			 '<a href="' . admin_url( 'options-general.php?page=njtape_settings_page' ) . '">Settings</a>'

		);

		return array_merge( $links, $settings_link );

	}

	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'njtape_post_expire_action_links' );

}



// Get all post types

if ( ! function_exists( 'njtape_get_all_post_types_list' ) ) {

	function njtape_get_all_post_types_list() {

		$args = array( '_builtin' => false );

		$all_post_types = get_post_types( $args );



		unset($all_post_types['product_variation']);

		unset($all_post_types['shop_order_refund']);

		unset($all_post_types['shop_coupon']);

		unset($all_post_types['shop_order_placehold']);

		unset($all_post_types['shop_order']);

		unset($all_post_types['acf-taxonomy']);

		unset($all_post_types['acf-post-type']);

		unset($all_post_types['acf-ui-options-page']);

		unset($all_post_types['acf-field-group']);

		unset($all_post_types['acf-field']);

		unset($all_post_types['wpcf7_contact_form']);

		unset($all_post_types['mc4wp-form']);

		unset($all_post_types['patterns_ai_data']);



		return $all_post_types;

	}

}



// cron file

require_once NJTAPE_AUTO_POST_EXPIRY_DIR . '/cron-function.php';