<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Wayback {
	use \Cornell\Governance\Admin\HTML_Table;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Wayback\Retrieve' ) ) {
		class Retrieve {
			/**
			 * @var Retrieve $instance holds the single instance of this class
			 * @access private
			 */
			private static Retrieve $instance;

			/**
			 * @var string $api_base the API base URL for retrieving lists of snapshots
			 * @access private
			 */
			private string $api_base = 'https://web.archive.org/cdx/search/cdx/';

			/**
			 * @var string $transient_name the base for the transient/cache name
			 * @access private
			 */
			private string $transient_name = 'cornell/governance/web-archive/snapshots/';

			/**
			 * @var int $transient_time the amount of time the results should be cached
			 * @access private
			 */
			private int $transient_time = DAY_IN_SECONDS;

			/**
			 * @var int $limit the maximum number of results to display
			 * @access private
			 */
			private int $limit = 20;

			/**
			 * @var array $headers the header array from the returned results
			 * @access private
			 */
			private array $headers = array();

			/**
			 * Creates the Retrieve object
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
			 * @return  Retrieve
			 * @since   0.1
			 */
			public static function instance(): Retrieve {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Retrieve and return the transient timeout
			 *
			 * @access public
			 * @since  0.6.2
			 * @return int the transient timeout
			 */
			public function get_transient_timeout(): int {
				return apply_filters( 'cornell/governance/wayback/timeout', $this->transient_time );
			}

			/**
			 * Retrieve and return the record limit
			 *
			 * @access public
			 * @since  0.6.2
			 * @return int the record limit
			 */
			public function get_limit(): int {
				return apply_filters( 'cornell/governance/wayback/record-limit', $this->limit );
			}

			/**
			 * Retrieve available snapshots for a list of URLs all at once
			 *      To be used by cron requests only
			 *
			 * @param array $pages the array of page URLs to retrieve snapshots for
			 *
			 * @access public
			 * @since  1.0.4
			 * @return void
			 */
			public function get_url_list( array $pages ) {
				if ( ! isset( $_REQUEST['cornell/governance/retrieve-snapshots'] ) ) {
					$e = new \WP_Error( 'no-access', __( 'You attempted to use a function that is not publicly available', 'cornell-governance' ) );
					wp_die( $e, esc_html( __( 'Incorrect Usage', 'cornell-governance' ) ), array( 'response' => 403 ) );
				}

				$count = 0;

				$search = Plugin::instance()->get_archive_settings( 'search' );
				$replace = Plugin::instance()->get_archive_settings( 'replace' );

				foreach ( $pages as $page ) {
					if ( $count > $this->limit ) {
						wp_die( esc_html( __( 'This batch of snapshots has been successfully requested', 'cornell-governance' ) ), __( 'Round Completed', 'cornell-governance'), array( 'response' => 200 ) );
					}

					$url = get_permalink( $page );
					if ( ! empty( $search ) && ! empty( $replace ) ) {
						$url = str_ireplace( $search, $replace, $url );
					}

					if ( false === $this->retrieve_and_store_url( $url, 30 ) ) {
						$count++;
					}
				}

				wp_die( esc_html( __( 'All snapshots appear to have been completed.', 'cornell-governance' ) ), esc_html( __( 'Snapshots Complete', 'cornell-governance' ) ), array( 'response' => 200 ) );
			}

			/**
			 * Retrieve Wayback info about a specific URL and store it in the database for future use
			 *
			 * @param string $url
			 * @param int $timeout the amount of time in seconds that the request should wait before timing out
			 *
			 * @access private
			 * @since  1.0.4
			 * @return bool whether we already had results for this URL or not
			 */
			private function retrieve_and_store_url( string $url, int $timeout=20 ): bool {
				// We already have a list of snapshots for this page
				$results = get_transient( $this->transient_name . urlencode( $url ) );
				if ( false !== $results ) {
					return true;
				}

				// We already tried recently enough and failed, so we will skip this URL
				$results = get_transient( $this->transient_name . 'no-results/' . urlencode( $url ) );
				if ( false !== $results ) {
					return true;
				}

				$api_url = $this->api_base;
				$api_url = add_query_arg( array(
					'url' => urlencode( $url ),
					'output' => 'json',
				), $api_url );

				$request = wp_remote_get( $api_url, array( 'timeout' => $timeout ) );
				if ( is_wp_error( $request ) ) {
					set_transient( $this->transient_name . 'no-results/' . urlencode( $url ), $request, HOUR_IN_SECONDS );
					return false;
				}

				if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
					set_transient( $this->transient_name . 'no-results/' . urlencode( $url ), $request, HOUR_IN_SECONDS );
					return false;
				}

				$result = json_decode( wp_remote_retrieve_body( $request ) );

				set_transient( $this->transient_name . urlencode( $url ), $result, $this->get_transient_timeout() );
				return false;
			}

			/**
			 * Retrieve the available snapshots of a URL
			 *
			 * @param string $query_url
			 *
			 * @access private
			 * @since  0.6.2
			 * @return array|object|\WP_Error the PHP-formatted JSON results of the call
			 */
			private function get_url( string $query_url ) {
				$results = get_transient( $this->transient_name . urlencode( $query_url ) );

				if ( false === $results ) {
					// Since the request can take a long time, we don't want to run it every time
					//      we try to edit a page. Give it a cooling-off time of 1 hour at a time
					$results = get_transient( $this->transient_name . 'no-results/' . urlencode( $query_url ) );
				}

				if ( false === $results ) {
					$url = $this->api_base;
					$url = add_query_arg( array(
						'url' => urlencode( $query_url ),
						'output' => 'json',
					), $url );

					$request = wp_remote_get( $url, array( 'timeout' => 1 ) );
					if ( is_wp_error( $request ) ) {
						set_transient( $this->transient_name . 'no-results/' . urlencode( $query_url ), $request, HOUR_IN_SECONDS );
						return $request;
					}

					if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
						set_transient( $this->transient_name . 'no-results/' . urlencode( $query_url ), $request, HOUR_IN_SECONDS );
						return new \WP_Error( 'bad-request', __( 'The request for the snapshots returned a status code other than 200', 'cornell-governance' ) );
					}

					$result = json_decode( wp_remote_retrieve_body( $request ) );

					set_transient( $this->transient_name . urlencode( $query_url ), $result, $this->get_transient_timeout() );

					return $result;
				}

				return $results;
			}

			/**
			 * Translate the header row into plain language
			 *
			 * @param array $headers the header array
			 *
			 * @access private
			 * @since  0.6.2
			 * @return array the formatted header array
			 */
			private function set_headers( array $headers ): array {
				$translations = array(
					'urlkey' => esc_html( __( 'URL Key', 'cornell-governance' ) ),
					'timestamp' => esc_html( __( 'Timestamp', 'cornell-governance' ) ),
					'original' => esc_html( __( 'Original', 'cornell-governance' ) ),
					'mimetype' => esc_html( __( 'MIME Type', 'cornell-governance' ) ),
					'statuscode' => esc_html( __( 'Status Code', 'cornell-governance' ) ),
					'digest' => esc_html( __( 'Digest Key', 'cornell-governance' ) ),
					'length' => esc_html( __( 'File Size', 'cornell-governance' ) ),
				);

				$return = array();

				foreach ( $headers as $header ) {
					$return[ $header ] = $translations[ $header ] ?? $header;
				}

				$this->headers = $return;

				return $this->headers;
			}

			/**
			 * Format the returned results with appropriate array keys
			 *
			 * @param array $record the single record being formatted
			 *
			 * @access private
			 * @since  0.6.2
			 * @return array the formatted array of information
			 */
			private function format_row( array $record ): array {
				$keys = $this->headers;

				$return = array();

				foreach ( $record as $key => $value ) {
					$return[ array_values( $keys )[ $key ] ] = $value;
				}

				return $return;
			}

			/**
			 * Return a message saying there were no results returned
			 *
			 * @param ?string $url the URL that was queried against the Wayback API
			 *
			 * @access private
			 * @since  0.6.2
			 * @return string the message
			 */
			private function no_results( string $url = null ): string {
				return sprintf( '<p class="cornell-governance-wayback-list-empty" data-query-url="%2$s">%1$s</p>', esc_html( __( 'There do not appear to be any snapshots of this content in the Wayback Machine, currently', 'cornell-governance' ) ), $url );
			}

			/**
			 * Format the returned results as an HTML table
			 *
			 * @param string $url the URL being queried
			 *
			 * @access public
			 * @return string the HTML results
			 *@since  0.6.2
			 */
			public function get_html_table( string $url ): string {
				$results = $this->get_url( $url );

				$output = sprintf( '<h3>%s</h3>', __( 'Wayback Machine Snapshots', 'cornell-governance' ) );

				if ( is_array( $results ) && count( $results ) > 0 ) {
					$output .= HTML_Table::instance()->open( esc_html( __( 'Wayback Machine Results', 'cornell-governance' ) ), array( 'wayback-query-results' ) );
					$headers = array_shift( $results );

					do_action( 'qm/warning', 'The headers array looks like: {headers}', array( 'headers' => print_r( $headers, true ) ) );

					$this->set_headers( $headers );

					$output .= HTML_Table::instance()->get_row( $headers, 'header' );
					$output .= HTML_Table::instance()->get_row( $headers, 'footer' );
					$output .= HTML_Table::instance()->open_body();

					$results = array_reverse( $results );

					foreach ( $results as $result ) {
						$result  = $this->format_row( $result );
						do_action( 'qm/info', 'The row information looks like: {result}', array( 'result' => print_r( $result, true ) ) );

						if ( array_key_exists( 'Timestamp', $result ) && array_key_exists( 'Original', $result ) ) {
							$result['Original'] = sprintf( '<a href="%s">%s</a>', View::instance()->get_url( $result ), $result['Original'] );
						}

						$output .= HTML_Table::instance()->get_row( $result );
					}

					$output .= HTML_Table::instance()->close_body();
					$output .= HTML_Table::instance()->close();
				} else {
					$output .= $this->no_results( $url );
				}

				return $output;
			}

			/**
			 * Build an unordered list of snapshots
			 *
			 * @param string $url the URL being queried
			 *
			 * @access public
			 * @return string the HTML list
			 *@since  0.6.2
			 */
			public function get_unordered_list( string $url ): string {
				$results = $this->get_url( $url );

				$output = sprintf( '<h3>%s</h3>', __( 'Wayback Machine Snapshots', 'cornell-governance' ) );

				if ( is_array( $results ) && count( $results ) > 0 ) {
					$output .= $this->get_html_list( $results );
				} else {
					$output .= $this->no_results( $url );
				}

				return $output;
			}

			/**
			 * Build an ordered list of snapshots
			 *
			 * @param string $url the URL being queried
			 *
			 * @access public
			 * @return string the HTML list
			 *@since  0.6.2
			 */
			public function get_ordered_list( string $url ): string {
				$results = $this->get_url( $url );

				$output = sprintf( '<h3>%s</h3>', __( 'Wayback Machine Snapshots', 'cornell-governance' ) );

				if ( is_array( $results ) && count( $results ) > 0 ) {
					$output .= $this->get_html_list( $results, array(), 'ol' );
				} else {
					$output .= $this->no_results( $url );
				}

				return $output;
			}

			/**
			 * Format the returned results as an HTML list
			 *
			 * @param array $results the full result set
			 * @param array $classes the CSS classes to apply to the list
			 * @param string $context whether this is an ordered list or unordered list
			 *
			 * @access public
			 * @since  0.6.2
			 * @return string the HTML
			 */
			public function get_html_list( array $results, array $classes = array(), string $context = 'ul' ): string {
				$classes[] = 'cornell-governance-wayback-list';

				if ( 'ol' === $context ) {
					$tag       = 'ol';
					$classes[] = 'cornell-governance-ordered-list';
				} else {
					$tag       = 'ul';
					$classes[] = 'cornell-governance-unordered-list';
				}

				$headers = array_shift( $results );
				$this->set_headers( $headers );

				if ( ! count( $results ) ) {
					return '';
				}

				$results = array_reverse( $results );

				$output = sprintf( '<%s class="%s">', $tag, implode( ' ', $classes ) );

				foreach ( $results as $result ) {
					$result  = $this->format_row( $result );

					if ( ! array_key_exists( 'Timestamp', $result ) || ! array_key_exists( 'Original', $result ) ) {
						continue;
					}

					$date_format = get_option( 'date_format' );
					$time_format = get_option( 'time_format' );

					$date_time = date( 'Y-m-d H:i:s', strtotime( $result['Timestamp'] ) );
					$adjusted_date = get_date_from_gmt( $date_time );

					$text = date_i18n( $date_format . ' @ ' . $time_format, strtotime( $adjusted_date ) );

					$link = sprintf( '<a href="%s">%s</a>', View::instance()->get_url( $result ), $text );

					$output .= sprintf( '<li>%s</li>', $link );
				}

				$output .= sprintf( '</%s>', $tag );

				return $output;
			}
		}
	}
}
