<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Wayback {
	if ( ! class_exists( '\Cornell\Governance\Wayback\View') ) {
		/**
		 * The class that allows viewing a list of Archive snapshots for a specific page
		 */
		class View {
			/**
			 * @var View $instance holds the single instance of this class
			 * @access private
			 */
			private static View $instance;

			/**
			 * @var string $api_base the API base URL for retrieving lists of snapshots
			 * @access private
			 */
			private string $api_base = 'https://web.archive.org/web/';

			/**
			 * Creates the View object
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
			 * @return  View
			 * @since   0.1
			 */
			public static function instance(): View {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Returns the URL to view a specific snapshot from the Wayback Machine
			 *
			 * @param array $record the Wayback machine record result
			 *
			 * @access public
			 * @since  0.6.2
			 * @return string the URL to the view
			 */
			public function get_url( array $record ): string {
				$timestamp = array_key_exists( 'Timestamp', $record ) ? $record['Timestamp'] : 0;

				$url = array_key_exists( 'Original', $record ) ? $record['Original'] : '';

				$output = trailingslashit( $this->api_base );

				if ( ! empty( $timestamp ) ) {
					$output = trailingslashit( $output . $timestamp );
				}

				if ( empty( $url ) ) {
					return '';
				}

				return $output . $url;
			}
		}
	}
}