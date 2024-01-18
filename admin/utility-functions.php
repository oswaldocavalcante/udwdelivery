<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function lalamove_get_wc_rest_api_key() {
	global $wpdb;
	$table       = $wpdb->prefix . 'woocommerce_api_keys';
	$kw          = Lalamove_App::$wc_llm_rest_sql_keyword;
	$search_text = '%' . $kw . '%';

	return $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM $table WHERE description like %s order by `key_id` desc LIMIT 1", $search_text )
	);
}

function lalamove_remove_rest_api_key() {
	try {
		global $wpdb;
		$table       = $wpdb->prefix . 'woocommerce_api_keys';
		$kw          = Lalamove_App::$wc_llm_rest_sql_keyword;
		$search_text = '%' . $kw . '%';
		$wpdb->query(
			$wpdb->prepare( "DELETE FROM $table WHERE description like %s", $search_text )
		);
	} catch ( Exception $e ) {
		lalamove_error_log( array( 'Lalamove_deactivate_remove_api_key_failed' => $e->getMessage() ) );
	}
	try {
		$zones = WC_Shipping_Zones::get_zones();
		foreach ( $zones as $zone ) {
			$the_zone         = new WC_Shipping_Zone( $zone['zone_id'] );
			$shipping_methods = $the_zone->get_shipping_methods();
			foreach ( $shipping_methods as $shipping_method ) {
				if ( $shipping_method->id === 'LALAMOVE_CARRIER_SERVICE' ) {
					$the_zone->delete_shipping_method( $shipping_method->instance_id );
				}
			}
		}
	} catch ( Exception $e ) {
		lalamove_error_log( array( 'Lalamove_deactivate_rm_shipping_method_failed' => $e->getMessage() ) );
	}
}

function lalamove_base64_url_encode( $input ) {
	return str_replace( '=', '', strtr( base64_encode( $input ), '+/', '-_' ) );
}

function lalamove_sign_hmac_key( $input, $secret ) {
	return lalamove_base64_url_encode( hash_hmac( 'sha256', $input, $secret, true ) );
}

function lalamove_gen_jwt() {
	$secret_obj = lalamove_get_wc_rest_api_key();

	if ( ! $secret_obj ) {
		return null;
	}

	$secret = $secret_obj->consumer_secret;

	$header = array(
		'alg' => 'HS256',
		'typ' => 'JWT',
	);

	$current_user = wp_get_current_user();
	$countries    = WC()->countries;
	$payload      = array(
		'dest'            => get_option( 'siteurl' ),
		'exp'             => time() + 600,
		'country_id'      => lalamove_get_country_id(),
		'return_url'      => admin_url() . 'admin.php?page=' . Lalamove_App::$menu_slug,
		'store_address'   => $countries->get_base_address(),
		'store_address_2' => $countries->get_base_address_2(),
		'store_city'      => $countries->get_base_city(),
		'store_postcode'  => $countries->get_base_postcode(),
		'store_country'   => $countries->get_base_country(),
		'store_state'     => $countries->get_base_state(),
		'first_name'      => $current_user->first_name,
		'last_name'       => $current_user->last_name,
		'email'           => $current_user->user_email,
	);

	$base64header  = lalamove_base64_url_encode( wp_json_encode( $header, JSON_UNESCAPED_UNICODE ) );
	$base64payload = lalamove_base64_url_encode( wp_json_encode( $payload, JSON_UNESCAPED_UNICODE ) );
	$token         = $base64header . '.' . $base64payload . '.' . lalamove_sign_hmac_key( $base64header . '.' . $base64payload, $secret );
	return $token;
}

function lalamove_get_country( $woocommerce_store_country ) {
	if ( array_key_exists( $woocommerce_store_country, Lalamove_App::$wc_llm_dc ) ) {
		return Lalamove_App::$wc_llm_dc[ $woocommerce_store_country ];
	}
	return null;
}

function lalamove_get_dc(): string {
	$wc_country       = strtolower( WC()->countries->get_base_country() );
	$lalamove_country = lalamove_get_country( $wc_country );
	if ( $lalamove_country ) {
		return $lalamove_country[0];
	}
	return '';
}

function lalamove_get_country_id(): int {
	$wc_country       = strtolower( WC()->countries->get_base_country() );
	$lalamove_country = lalamove_get_country( $wc_country );
	if ( $lalamove_country ) {
		return $lalamove_country[1];
	}
	return 0;
}

function lalamove_get_order_id( $wc_order_id ) {
	$wc_order = wc_get_order( $wc_order_id );
	if ( $wc_order->meta_exists( '_lalamove_order_ids' ) ) {
		return $wc_order->get_meta( '_lalamove_order_ids', true );
	}
	return null;
}

function lalamove_get_order_detail( $lalamove_order_id ) {
	$endpoint = lalamove_get_service_url() . '/api/orders/plugin/' . $lalamove_order_id;
	try {
		$response = wp_remote_get(
			$endpoint,
			array(
				'timeout'  => 10,
				'blocking' => true,
				'headers'  => array(
					'Content-Type'  => 'application/json',
					'Cache-Control' => 'no-cache',
					'llm-country'   => lalamove_get_country_id(),
					'Authorization' => 'Bearer ' . lalamove_gen_jwt(),
				),
			)
		);
		if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ?? 0 ) {
			error_log( wp_json_encode( array( 'error' => 'Service temporarily unavailable' ) ) );
			return null;
		}
		return json_decode( wp_remote_retrieve_body( $response ) );
	} catch ( \Exception $exception ) {
		error_log( wp_json_encode( array( 'error' => $exception->getMessage() ) ) );
		return null;
	}
}

function lalamove_get_service_url() {
	$region = lalamove_get_dc();

	if ( 'br' === $region ) {
		return Lalamove_App::$wc_llm_api_host['br'];
	}
	return Lalamove_App::$wc_llm_api_host['sg'];
}

function lalamove_get_auth_callback_url() {
	$url = lalamove_get_service_url();
	if ( $url ) {
		return $url . '/auth/callback';
	}
}

function lalamove_get_order_status_string( $status ) {
	if ( ! is_null( $status ) ) {
		foreach ( Lalamove_App::$llm_order_status as $display_name => $status_list ) {
			if ( in_array( $status, $status_list ) ) {
				return $display_name;
			}
		}
	}
	return 'Unfulfilled';
}

function lalamove_get_current_admin_url() {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return '';
	}
	$parts = wp_parse_url( admin_url( basename( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) );
	return $parts['scheme'] . '://' . $parts['host'] . $parts['path'];
}

function lalamove_get_current_plugin_param( $key ) {
	if ( empty( $_SERVER['QUERY_STRING'] ) ) {
		return '';
	}
	parse_str( $_SERVER['QUERY_STRING'], $query );
	if ( empty( $query[ $key ] ) ) {
		return '';
	}
	return $query[ $key ];
}

function lalamove_get_current_user_name() {
	$current_user = wp_get_current_user();
	$name         = $current_user->first_name . ' ' . $current_user->last_name;
	return trim( $name );
}

function lalamove_get_lalamove_order_id() {
	return '(no value yet)';
}

function lalamove_get_single_order_id( $wc_order_id ) {
	$lalamove_order_ids = lalamove_get_order_id( $wc_order_id );
	return is_null( $lalamove_order_ids ) ? null : $lalamove_order_ids[0];
}

function lalamove_get_send_again_with_status() {
	return array_merge( Lalamove_App::$llm_order_status['Cancelled'], Lalamove_App::$llm_order_status['Rejected'], Lalamove_App::$llm_order_status['Expired'] );
}

function lalamove_error_log( $log ) {
	if ( ! WP_DEBUG || empty( $log ) ) {
		return;
	}
	$out = 'LALAMOVE_ERR_LOG: ';
	if ( is_array( $log ) || is_object( $log ) ) {
		$out = $out . wp_json_encode( $log );
	} elseif ( is_string( $log ) ) {
		$out = $out . $log;
	}
	error_log( $out );
}

