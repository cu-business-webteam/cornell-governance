<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Wayback {

	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Save' ) ) {
		class Save {
			/**
			 * @var Save $instance holds the single instance of this class
			 * @access private
			 */
			private static Save $instance;

			/**
			 * @var string $api_base the API base URL for retrieving lists of snapshots
			 * @access private
			 */
			private string $api_base = 'https://web.archive.org/save/';

			/**
			 * Creates the Save object
			 *
			 * @access private
			 * @since  0.1
			 */
			private function __construct() {
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Save
			 * @since   0.1
			 */
			public static function instance(): Save {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Trigger a Wayback Machine snapshot
			 *
			 * @param int|\WP_Post the post for which the snapshot is being requested
			 *
			 * @access public
			 * @since  0.6.2
			 * @return string|\WP_Error the content location header on success; error on failure
			 */
			public function trigger_snapshot( $post ) {
				if ( is_numeric( $post ) ) {
					$post_id = $post;
				} else {
					$post_id = $post->ID;
				}

				$url = get_permalink( $post_id );

				return $this->trigger_snapshot_by_url( $url );
			}

			/**
			 * Trigger a Wayback Machine snapshot by resource URL
			 *
			 * @param string $url the URL of the item for which the snapshot is being requested
			 *
			 * @access public
			 * @return string|\WP_Error the content location on success; error on failure
			 *@since  0.6.2
			 */
			public function trigger_snapshot_by_url( string $url ) {
				// Ping archive machine.
				$save_url = trailingslashit( $this->api_base ) . $url;

				$env = Helpers::get_environment();
				if ( 'production' !== $env ) {
					return sprintf( 'If this were a production environment, we would have queried the following URL: %s', $save_url );
				}

				$response = wp_remote_get( $save_url );

				$archive_link = '';

				if ( is_wp_error( $response ) ) {
					return $response;
				} elseif ( ! empty( $response['headers']['x-archive-wayback-runtime-error'] ) ) {
					return new \WP_Error( 'wayback_machine_error', $response['headers']['x-archive-wayback-runtime-error'], $response );
				} elseif ( ! empty( $response['headers']['content-location'] ) ) {
					return $response['headers']['content-location'];
				} elseif ( ! empty( wp_remote_retrieve_header( $response, 'link' ) ) ) {
					preg_match( '/rel="memento.*?(((http|https):\/\/){0,1}(web\.archive\.org\/web\/[0-9]{14}\/.*?))>/', wp_remote_retrieve_header( $response, 'link' ), $matches, 0, 0 );
					if ( count( $matches ) >= 2 ) {
						return $matches[1];
					}
				} else {
					return print_r( wp_remote_retrieve_headers( $response ), true );
				}
			}
		}
	}
}