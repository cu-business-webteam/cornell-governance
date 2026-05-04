<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Submenus {

	use Cornell\Governance\Config;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;
	use Cornell\Governance\Admin\HTML_Table;
	use Cornell\Governance\Wayback\Trigger;

	class Archive_Trigger extends Base {
		/**
		 * @var string $message holds any message that needs to be printed at the top of the screen
		 * @access protected
		 */
		protected string $message = '';
		/**
		 * @var Archive_Trigger $instance holds the single instance of this class
		 * @access private
		 */
		private static Archive_Trigger $instance;

		/**
		 * Creates the Archive_Trigger object
		 *
		 * @access private
		 * @since  0.1
		 */
		function __construct() {
			if ( ! is_admin() ) {
				return;
			}

			if ( isset( $_GET['trigger_snapshots'] ) ) {
				$this->trigger_snapshots($_GET['trigger_snapshots']);
			}

			parent::__construct( array(
				'title'       => esc_html( __( 'Cornell Governance: Wayback Machine Archival Information', 'cornell-governance' ) ),
				'menu_name'   => esc_html( __( 'Archive Snapshots', 'cornell-governance' ) ),
				'slug'        => 'cornell-governance-archive-trigger',
				'description' => esc_html( __( 'View information about Wayback Machine archive snapshots, and trigger any scheduled snapshots', 'cornell-governance' ) ),
			) );
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Archive_Trigger
		 * @since   0.1
		 */
		public static function instance(): Archive_Trigger {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Conditionally register this submenu
		 */
		public function register() {
			if ( false === Plugin::instance()->get_archive_settings('active') ) {
				return;
			}

			Parent::register();
		}

		/**
		 * Trigger any scheduled Archive snapshots
		 *
		 * @param string $today the date of the snapshots that need to be triggered
		 *
		 * @access protected
		 * @return void
		 * @since  0.6.2
		 */
		private function trigger_snapshots( string $today ) {
			$archive_obj = Trigger::instance();
			// By default, the Trigger uses today's date; we may need to reset that for a different date
			$archive_obj->set_today( $today );
			$archive_obj->set_initial_vars();

			if ( $archive_obj->get_posts_count() > 0 ) {
				$done = $archive_obj->manual_trigger();

				if ( count( $done ) > 0 ) {
					$this->output_trigger_results( $done );
				} else {
					$this->output_error_message( esc_html( __( 'No Snapshots', 'cornell-governance' ) ), __( 'There were no snapshots executed on this run', 'cornell-governance' ) );
				}
			}

			add_action( 'admin_notices', array( $this, 'output_results_message' ) );
		}

		/**
		 * Generate and output a list of results from the manual snapshot action
		 *
		 * @param array $results the array of successful snapshots
		 * @param bool $echo whether to echo the message or store it in this class' message property
		 *
		 * @access protected
		 * @return void
		 * @since  0.6.2
		 */
		protected function output_trigger_results( array $results, bool $echo = false ) {
			$output = '<div class="cornell-governance-trigger-results notice notice-success is-dismissible">';
			$output .= sprintf( '<h3>%s</h3>', esc_html( __( 'Manual Snapshot Results', 'cornell-governance' ) ) );
			$output .= '<ul>';

			foreach ( $results as $id => $result ) {
				$title = get_the_title( $id );
				if ( is_wp_error( $result ) ) {
					$output .= sprintf( '<li>%s: %s</li>', esc_html( $title ), esc_html( $result->get_error_message() ) );
				} else {
					$output .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( $result ), esc_html( $title ) );
				}
			}

			$output .= '</ul>';
			$output .= '</div>';

			if ( $echo ) {
				echo $output;
			} else {
				$this->message = $output;
			}
		}

		/**
		 * Generate and output an error message on this page
		 *
		 * @param string $heading the heading text
		 * @param string $message the full text of the message
		 * @param bool $echo whether to echo the message or store it in this class' message property
		 *
		 * @access protected
		 * @return void
		 * @since  0.6.2
		 */
		protected function output_error_message( string $heading, string $message, bool $echo = false ) {
			$output = sprintf(
				'<div class="error notice notice-error is-dismissible"><p><strong>%s</strong>: %s</p></div>',
				esc_html( $heading ),
				esc_html( $message )
			);

			if ( $echo ) {
				echo $output;
			} else {
				$this->message = $output;
			}
		}

		/**
		 * Set the object properties
		 *
		 * @param array $attributes the properties to assign
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function set_properties( array $attributes ) {
			parent::set_properties( $attributes );

			$this->cap = 'delete_users';
		}

		/**
		 * Retrieve and return the custom cap for this menu page
		 *
		 * @access public
		 * @return string the custom cap
		 * @since  0.6.2
		 */
		public function get_cap(): string {
			return $this->cap;
		}

		/**
		 * Outputs the content of the Page List
		 *
		 * @access protected
		 * @return void
		 * @since  0.1
		 */
		protected function display() {
			printf( '<div class="wrap"><h2>%s</h2><div class="cornell-governance-archive-info">', esc_html( $this->title ) );

			printf( '<p>%s</p>', esc_html( __( 'View information about the Wayback Machine integration and trigger manual archival snapshots if desired', 'cornell-governance' ) ) );

			$this->do_plugin_log();

			print( '</div></div>' );
		}

		/**
		 * Output some debug information
		 *
		 * @access private
		 * @return void
		 * @since  2024.06.25
		 */
		private function do_debug_display() {
			printf( '<h3>%s</h3>', esc_html( __( 'Debug Information', 'cornell-governance' ) ) );
			printf( '<p>%s</p>', esc_html( __( 'The debug constants are currently set to:', 'cornell-governance' ) ) );
			print( '<ul>' );

			ob_start();
			defined( 'WP_DEBUG' ) ? var_dump( WP_DEBUG ) : print( 'false' );
			$val = ob_get_clean();
			printf( '<li><code>WP_DEBUG</code>: %s</li>', esc_html( $val ) );

			ob_start();
			! empty( Config::instance()->get_var( 'CORNELL_DEBUG' ) ) ? var_dump( Config::instance()->get_var( 'CORNELL_DEBUG' ) ) : print( 'false' );
			$val = ob_get_clean();
			printf( '<li><code>CORNELL_DEBUG</code>: %s</li>', esc_html( $val ) );

			ob_start();
			! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_TO' ) ) ? var_dump( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_TO' ) ) : print( 'false' );
			$val = ob_get_clean();
			printf( '<li><code>CORNELL_GOVERNANCE_EMAIL_TO</code>: %s</li>', esc_html( $val ) );

			ob_start();
			! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_CC' ) ) ? var_dump( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_CC' ) ) : print( 'false' );
			$val = ob_get_clean();
			printf( '<li><code>CORNELL_GOVERNANCE_EMAIL_CC</code>: %s</li>', esc_html( $val ) );

			ob_start();
			! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_BCC' ) ) ? var_dump( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_BCC' ) ) : print( 'false' );
			$val = ob_get_clean();
			printf( '<li><code>CORNELL_GOVERNANCE_EMAIL_BCC</code>: %s</li>', esc_html( $val ) );

			print( '</ul>' );

			print( '<hr/>' );
		}

		/**
		 * Adds the screen options to the page
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function add_options() {
		}

		/**
		 * Output the submenu page
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function do_submenu_page() {
			$this->display();
		}

		/**
		 * Output the list of snapshots that are in the plugin log
		 *
		 * @access private
		 * @since  0.6.2
		 * @return void
		 */
		private function do_plugin_log() {
			printf( '<h3>%s</h3>', esc_html( __( 'Snapshot Log', 'cornell-governance' ) ) );

			$all_done = get_option( 'cornell/governance/archive/trigger/done', [] );
			if ( ! is_array( $all_done ) ) {
				$all_done = [];
			}

			Helpers::log( print_r( $all_done, true ), 'info' );
			$today = date( 'Y-m-d' );
			$done = get_option( 'cornell/governance/archive/trigger/done/' . $today, array() );
			if ( ! empty( $done ) && is_array( $done ) ) {
				$all_done[$today] = array();
				$all_done[$today]['option_id'] = 0;
				$all_done[$today]['option_name'] = 'cornell/governance/archive/trigger/done/' . $today;
				$all_done[$today]['option_value'] = $done;
			}

			$this->do_historical_table( $all_done );

			printf( '<h3>%s</h3>', esc_html( __( 'Scheduled Archival Snapshots', 'cornell-governance' ) ) );

			global $wpdb;
			$option_name = 'cornell/governance/archive/trigger/posts/%';

			$query = $wpdb->prepare( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s", $option_name );
			$result = $wpdb->get_results( $query );

			foreach ( $result as $row ) {
				$this->do_upcoming_table( $row );
			}

			if ( defined( 'CORNELL_DEBUG' ) && CORNELL_DEBUG ) {
				printf( '<h3>%s</h3>', esc_html( __( 'Debug Information:', 'cornell-governance' ) ) );
				print( '<pre style="max-width: 100%; overflow: auto;"><code>' );
				var_dump( $all_done );
				print( '</code></pre>' );
			}
		}

		/**
		 * Output the historical log table
		 *
		 * @param array|boolean $all_done the array of completed items
		 *
		 * @access private
		 * @since  1.0.2
		 * @return void
		 */
		private function do_historical_table( $all_done ) {
			if ( empty( $all_done ) ) {
				echo sprintf( '<p>%s</p>', esc_html( __( 'There are no historical snapshots in the log', 'cornell-governance' ) ) );
				return;
			}

			$headers = array(
				'date' => esc_html( __( 'Date of Snapshot', 'cornell-governance' ) ),
				'content_url' => esc_html( __( 'URL of WP Content', 'cornell-governance' ) ),
				'request_url' => esc_html( __( 'URL of Archive Request', 'cornell-governance' ) ),
				'location' => esc_html( __( 'Location or Response Headers', 'cornell-governance' ) ),
				'capture_time' => esc_html( __( 'Capture Time', 'cornell-governance' ) ),
				'headers' => esc_html( __( 'Full Response Headers', 'cornell-governance' ) ),
			);

			echo HTML_Table::instance()->open( esc_html( __( 'Log of Archival Snapshots Created Historically', 'cornell-governance' ) ) );
			echo HTML_Table::instance()->get_row( $headers, 'header' );
			echo HTML_Table::instance()->get_row( $headers, 'footer' );
			echo HTML_Table::instance()->open_body();

			foreach ( $all_done as $date => $done ) {
				if ( ! is_array( $done ) ) {
					continue;
				}

				if ( is_string( $done['option_value'] ) ) {
					$done['option_value'] = maybe_unserialize( $done['option_value'] );
				}

				if ( ! is_array( $done['option_value'] ) ) {
					continue;
				}

				$items_for_day = $done['option_value'];
				uasort( $items_for_day, array( $this, 'sort_done_day' ) );

				foreach ( $items_for_day as $item ) {
					if ( is_wp_error( $item ) ) {
						$item = $item->get_error_message();
					}

					if ( is_array( $item ) ) {
						$capture_time = Helpers::get_date_time( $item['capture-time'] );

						echo HTML_Table::instance()->get_row( array(
							'date' => esc_html( $date ),
							'content_url' => esc_url( $item['content-url'] ),
							'request_url' => esc_url( $item['request-url'] ),
							'location' => esc_html( $item['location'] ),
							'capture_time' => esc_html( Helpers::format_date_time( $capture_time ) ),
							'headers' => esc_html( $item['headers'] ),
						) );
					} else {
						echo HTML_Table::instance()->get_row( array(
							'date' => esc_html( $date ),
							'content_url' => esc_html( 'N/A' ),
							'request_url' => esc_html( 'N/A' ),
							'location' => esc_html( $item ),
							'capture_time' => esc_html( 'N/A' ),
							'headers' => esc_html( 'N/A' ),
						) );
					}

				}
			}

			echo HTML_Table::instance()->close_body();
			echo HTML_Table::instance()->close();
		}

		/**
		 * Sort the array of captured snapshots for a specific day
		 */
		private function sort_done_day( $a, $b ) {
			if ( $a['capture-time'] == $b['capture-time'] ) {
				return 0;
			}

			return ( $a['capture-time'] < $b['capture-time'] ) ? -1 : 1;
		}

		/**
		 * Output a table of scheduled snapshots
		 *
		 * @param \stdClass $row the database row being handled
		 *
		 * @access private
		 * @since  1.0.2
		 * @return void
		 */
		private function do_upcoming_table( \stdClass $row ) {
			$headers = array(
				'id' => esc_html( __( 'Page ID', 'cornell-governance' ) ),
				'title' => esc_html( __( 'Page Title', 'cornell-governance' ) ),
				'steward' => esc_html( __( 'Page Steward', 'cornell-governance' ) ),
			);

			$to_do = maybe_unserialize( $row->option_value );
			$today_string = str_replace( 'cornell/governance/archive/trigger/posts/', '', $row->option_name );
			$today = date( 'M j, Y', strtotime( $today_string ) );

			if ( empty( $to_do ) ) {
				/* translators: the date that was recently processed */
				echo sprintf( '<p>%s</p>', sprintf( esc_html( __( 'All archival snapshots scheduled for %s have been completed', 'cornell-governance' ) ), $today ) );
			} else {
				/* translators: the date for which snapshots are scheduled */
				echo HTML_Table::instance()->open( sprintf( esc_html( __( 'Archival snapshots scheduled for %s', 'cornell-governance' ) ), $today ) );
				echo HTML_Table::instance()->get_row( $headers, 'header' );
				echo HTML_Table::instance()->get_row( $headers, 'footer' );
				echo HTML_Table::instance()->open_body();

				foreach ( $to_do as $id => $item ) {
					$steward = get_user_by( 'id', $item->post_author );
					echo HTML_Table::instance()->get_row( array( 'id' => $item->ID, 'title' => sprintf( '<a href="%s">%s</a>', get_permalink( $item->ID ), $item->post_title ), 'steward' => $steward->user_login ) );
				}

				echo HTML_Table::instance()->close_body();
				echo HTML_Table::instance()->close();

				$this->do_manual_trigger_button( $today_string );
			}
		}

		/**
		 * Output a form that allows a user to manually trigger scheduled snapshots
		 *
		 * @param string $today the date of the snapshots needing to be triggered
		 *
		 * @access private
		 * @return void
		 *@since  1.0.2
		 */
		private function do_manual_trigger_button( string $today ) {
			echo '<form method="GET">';

			print( '<input type="hidden" name="page" value="cornell-governance-archive-trigger" />' );
			wp_nonce_field( 'cornell-governance-archive-trigger-posts', 'cornell-governance-archive-trigger-nonce' );
			printf( '<input type="hidden" name="trigger_snapshots" value="%s" />', esc_attr( $today ) );
			/* translators: the date of the snapshots being processed */
			printf( '<input type="submit" class="button-primary" value="%s" />', sprintf( esc_html( __( 'Trigger %s Snapshots', 'cornell-governance' ) ), $today ) );

			echo '</form>';
		}

		/**
		 * Output the message as an admin notice
		 *
		 * @access public
		 * @since  1.0.5
		 * @return void
		 */
		public function output_results_message() {
			echo $this->message;
		}
	}
}