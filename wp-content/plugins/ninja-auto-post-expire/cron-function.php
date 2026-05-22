<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	if ( ! function_exists( 'njtape_post_expiry_add_custom_cron' ) ) {
		function njtape_post_expiry_add_custom_cron( $schedules ) {
			$schedules['every_five_minutes'] = array(
				'interval' => 300,
				'display'  => __( 'Every 5 Minutes', 'ninja-auto-post-expire' ),
			);
			return $schedules;
		}
		add_filter( 'cron_schedules', 'njtape_post_expiry_add_custom_cron' );
	}

	// Schedule an action if it's not already scheduled.
	if ( ! wp_next_scheduled( 'njtape_post_expiry_add_custom_cron' ) ) {
		wp_schedule_event( time(), 'every_five_minutes', 'njtape_post_expiry_add_custom_cron' );
	}

	if ( ! function_exists( 'njtape_post_expiry_custom_cron_event_func' ) ) {
		function njtape_post_expiry_custom_cron_event_func() {
			global $post;

			$njtape_post_expire_option = get_option('njtape_post_expire_option');
			if($njtape_post_expire_option == 'yes') {
				$args = array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					// 'meta_query' => array(
					// 	array(
					// 		'key' => 'njtape_expiration_date',
					// 		'compare' => 'EXISTS',
					// 	),
					// ),
				);

				// The Query.
				$query = new WP_Query( $args );
				while ( $query->have_posts() ) :
					$query->the_post();

					$exp_date = get_post_meta( $post->ID, '_njtape_expiration_date', true );
					if ( ! empty( $exp_date ) ) {
						$date_value = njtape_post_expiry_datetime_calculate( $post->ID );
						if ( $date_value == 1 ) {
							$update_post = array(
								'ID'          => $post->ID,
								'post_status' => 'draft',
								'post_type'   => 'post',
							);
							wp_update_post( $update_post );
						}
					}
				endwhile;
			}

			$all_post_types = njtape_get_all_post_types_list();
			foreach ($all_post_types as $key => $value) {
				$temp_key = 'njtape_'.$key.'_expire_option';
				$$temp_key = get_option('njtape_'.$key.'_expire_option');
				if($$temp_key == 'yes') {
					$args = array(
						'post_type'      => $key,
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						// 'meta_query' => array(
						// 	array(
						// 		'key' => 'njtape_expiration_date',
						// 		'compare' => 'EXISTS',
						// 	),
						// ),
					);

					// The Query.
					$query = new WP_Query( $args );
					while ( $query->have_posts() ) :
						$query->the_post();

						$exp_date = get_post_meta( $post->ID, '_njtape_expiration_date', true );
						if ( ! empty( $exp_date ) ) {
							$date_value = njtape_post_expiry_datetime_calculate( $post->ID );
							if ( $date_value == 1 ) {
								$update_post = array(
									'ID'          => $post->ID,
									'post_status' => 'draft',
									'post_type'   => $key,
								);
								wp_update_post( $update_post );
							}
						}
					endwhile;
				}
			}
		}
		add_action( 'njtape_post_expiry_add_custom_cron', 'njtape_post_expiry_custom_cron_event_func' );
	}