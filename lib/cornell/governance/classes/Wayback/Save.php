<?php

namespace {
	if ( ! defined( 'ABSPATH' ) )
		die( 'You do not have permission to access this file directly.' );
}

namespace Cornell\Governance\Wayback {

	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Wayback\Save' ) ) {
		/**
		 * The class that controls firing and saving results of an Archive snapshot
		 */
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
			 * @param int|\WP_Post $post the post for which the snapshot is being requested
			 *
			 * @access public
			 * @since  0.6.2
			 * @return array|\WP_Error an array of request and response data on success; error on failure
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
			 * @return array|\WP_Error an array of request and response data on success; error on failure
			 * @since  0.6.2
			 */
			public function trigger_snapshot_by_url( string $url ) {
				$rt = array();

				// Ping archive machine.
				$save_url = trailingslashit( $this->api_base ) . $url;

				$env = Helpers::get_environment();
				if ( 'production' !== $env ) {
					$rt['location'] = esc_html( __( 'This is not a production environment.', 'cornell-governance' ) );
					$rt['content-url'] = esc_url( $url );
					$rt['request-url'] = esc_url( $save_url );
					$rt['capture-time'] = time();
					$rt['headers'] = esc_html( sprintf( 'If this were a production environment, we would have queried the following URL: %s', esc_url( $save_url ) ) );

					return $rt;
				}

				$response = wp_remote_get( $save_url );

				$archive_link = '';

				$headers = wp_remote_retrieve_headers( $response );

				$rt['location'] = '';
				$rt['content-url'] = esc_url( $url );
				$rt['request-url'] = esc_url( $save_url );
				$rt['capture-time'] = time();
				$rt['headers'] = json_encode( (array) $headers );

				if ( is_wp_error( $response ) ) {
					return $response;
				} elseif ( ! empty( $headers['x-archive-wayback-runtime-error'] ) ) {
					return new \WP_Error( 'wayback_machine_error', $headers['x-archive-wayback-runtime-error'], $response );
				} elseif ( ! empty( $headers['content-location'] ) ) {
					$rt['location'] = $headers['content-location'];

					return $rt;
				} elseif ( ! empty( $headers['link'] ) ) {
					preg_match( '/rel="memento.*?(((http|https):\/\/){0,1}(web\.archive\.org\/web\/[0-9]{14}\/.*?))>/', $headers['link'], $matches, 0, 0 );
					if ( count( $matches ) >= 2 ) {
						$rt['location'] = $matches[1];
						return $rt;
					}
				} elseif ( ( ! empty( $headers['x-ts'] ) && 429 === (int) $headers['x-ts'] ) ) {
					$rt['location'] = esc_html( __( 'The resource has already been archived more than the dailty limit today, for some reason', 'cornell-governance' ) );

					return $rt;
				} else {
					$rt['location'] = esc_html( __( 'Could not parse location header', 'cornell-governance' ) );

					return $rt;
				}

				return array();
			}

			/**
			 * Schedule a snapshot of an updated piece of content
			 *
			 * @param int $post_id the ID of the post being saved
			 * @param \WP_Post $post the post object being saved
			 * @param bool $update whether this is an update or a new post
			 *
			 * @access public
			 * @since  1.0.2
			 * @return void
			 */
			public function schedule_post( int $post_id, \WP_Post $post, bool $update = false ) {
				if ( ! Plugin::instance()->get_archive_settings( 'active' ) ) {
					return;
				}

				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}

				$types = Plugin::instance()->get_post_types();
				if ( ( ! in_array( $post->post_type, $types ) && 'revision' !== $post->post_type ) ) {
					return;
				}

				$parent = wp_is_post_revision( $post_id );
				if ( $parent ) {
					return;
				}

				if ( ! in_array( $post->post_status, Helpers::get_page_status_list() ) ) {
					return;
				}

				$tomorrow = date( 'Y-m-d', strtotime( 'tomorrow' ) );
				$posts    = apply_filters( 'cornell/governance/archive/trigger/posts', get_option( 'cornell/governance/archive/trigger/posts/' . $tomorrow, array() ) );
				if ( array_key_exists( $post_id, $posts ) ) {
					return;
				} else {
					$posts[ $post_id ] = $post;
					update_option( 'cornell/governance/archive/trigger/posts/' . $tomorrow, $posts );
				}
			}
		}
	}
}