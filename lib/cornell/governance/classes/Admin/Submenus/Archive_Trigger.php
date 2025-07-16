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
	use Cornell\Governance\Wayback\HTML_Table;
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

			parent::__construct( array(
				'title'       => __( 'Cornell Governance: Wayback Machine Information', 'cornell/governance' ),
				'menu_name'   => __( 'Archive Snapshots', 'cornell/governance' ),
				'slug'        => 'cornell-governance-archive-trigger',
				'description' => __( 'View information about Wayback Machine snapshots, and trigger any scheduled snapshots', 'cornell/governance' ),
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
		 * Trigger any scheduled Archive snapshots
		 *
		 * @access protected
		 * @return void
		 * @since  0.6.2
		 */
		private function trigger_snapshots() {
			$archive_obj = Trigger::instance();
			if ( Trigger::instance()->get_posts_count() <= 0 ) {
				$today = date( 'Y-m-d', strtotime( 'tomorrow' ) );
				Trigger::instance()->set_today( $today );
				Trigger::instance()->set_initial_vars();
			}

			if ( ! empty( $posts ) ) {
				$done = Trigger::instance()->manual_trigger();
				if ( count( $done ) > 0 ) {
					$this->output_trigger_results( $done );
				} else {
					$this->output_error_message( __( 'No Snapshots', 'cornell/governance' ), __( 'There were no snapshots executed on this run', 'cornell/governance' ) );
				}
			}
		}

		/**
		 * Generate and output a list of results from the manual snapshot action
		 *
		 * @param array $results the array of successful snapshots
		 *
		 * @access protected
		 * @return void
		 * @since  0.6.2
		 */
		protected function output_trigger_results( array $results ) {
			$output = '<div class="cornell-governance-trigger-results">';
			$output .= sprintf( '<h3>%s</h3>', __( 'Manual Snapshot Results', 'cornell/governance' ) );
			$output .= '<ul>';

			foreach ( $results as $id => $result ) {
				$title = get_the_title( $id );
				if ( is_wp_error( $result ) ) {
					$output .= sprintf( '<li>%s: %s</li>', $title, esc_html( $result->get_error_message() ) );
				} else {
					$output .= sprintf( '<li><a href="%s">%s</a></li>', $result, $title );
				}
			}

			$output .= '</ul>';
			$output .= '</div>';

			echo $output;
		}

		/**
		 * Generate and output an error message on this page
		 *
		 * @param string $heading the heading text
		 * @param string $message the full text of the message
		 *
		 * @access protected
		 * @return void
		 * @since  0.6.2
		 */
		protected function output_error_message( string $heading, string $message ) {
			$output = sprintf(
				'<div class="error notice"><p><strong>%s</strong>: %s</p></div>',
				$heading,
				$message
			);
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

			$this->cap = 'delete_plugins';
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
			printf( '<div class="wrap"><h2>%s</h2><div class="cornell-governance-archive-info">', $this->title );

			printf( '<p>%s</p>', __( 'On this page, you can view information about the Wayback Machine integration and trigger manual snapshots if desired', 'cornell/governance' ) );

			if ( isset( $_GET['trigger_snapshots'] ) ) {
				$this->trigger_snapshots();
			}



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
			printf( '<h3>%s</h3>', __( 'Debug Information', 'cornell/governance' ) );
			printf( '<p>%s</p>', __( 'The debug constants are currently set to:', 'cornell/governance' ) );
			print( '<ul>' );

			ob_start();
			defined( 'WP_DEBUG' ) ? var_dump( WP_DEBUG ) : print( 'false' );
			$val = ob_get_clean();
			printf( '<li><code>WP_DEBUG</code>: %s</li>', $val );

			ob_start();
			! empty( Config::instance()->get_var( 'CORNELL_DEBUG' ) ) ? var_dump( Config::instance()->get_var( 'CORNELL_DEBUG' ) ) : print( 'false' );
			$val = ob_get_clean();
			printf( '<li><code>CORNELL_DEBUG</code>: %s</li>', $val );

			ob_start();
			! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_TO' ) ) ? var_dump( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_TO' ) ) : print( 'false' );
			$val = ob_get_clean();
			printf( '<li><code>CORNELL_GOVERNANCE_EMAIL_TO</code>: %s</li>', $val );

			ob_start();
			! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_CC' ) ) ? var_dump( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_CC' ) ) : print( 'false' );
			$val = ob_get_clean();
			printf( '<li><code>CORNELL_GOVERNANCE_EMAIL_CC</code>: %s</li>', $val );

			ob_start();
			! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_BCC' ) ) ? var_dump( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_BCC' ) ) : print( 'false' );
			$val = ob_get_clean();
			printf( '<li><code>CORNELL_GOVERNANCE_EMAIL_BCC</code>: %s</li>', $val );

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
			printf( '<h3>%s</h3>', __( 'Snapshot Log', 'cornell/governance' ) );

			$all_done = get_option( 'cornell/governance/archive/trigger/done', array() );
			$today = date( 'Y-m-d' );
			$done = get_option( 'cornell/governance/archive/trigger/done/' . $today, array() );
			if ( ! empty( $done ) ) {
				$all_done[$today] = $done;
			}

			if ( empty( $all_done ) ) {
				echo sprintf( '<p>%s</p>', __( 'There are no historical snapshots in the log', 'cornell/governance' ) );
			}

			$headers = array(
				'date' => __( 'Date of Snapshot', 'cornell/governance' ),
				'location' => __( 'Location', 'cornell/governance' ),
			);

			echo HTML_Table::instance()->open( __( 'Log of Snapshots Created Historically', 'cornell/governance' ) );
			echo HTML_Table::instance()->get_row( $headers, 'header' );
			echo HTML_Table::instance()->get_row( $headers, 'footer' );
			echo HTML_Table::instance()->open_body();

			foreach ( $all_done as $date => $done ) {
				if ( ! is_array( $done ) ) {
					continue;
				}

				foreach ( $done as $item ) {
					if ( is_wp_error( $item ) ) {
						$item = $item->get_error_message();
					}

					echo HTML_Table::instance()->get_row( array( 'date' => $date, 'location' => $item ) );
				}
			}

			echo HTML_Table::instance()->close_body();
			echo HTML_Table::instance()->close();
		}
	}
}