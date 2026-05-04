<?php

namespace {
	if ( ! defined( 'ABSPATH' ) )
		die( 'You do not have permission to access this file directly.' );
}

namespace Cornell\Governance\Wayback {

	use Cornell\Governance\Admin\Submenus\Archive_Trigger;

	if ( ! class_exists( '\Cornell\Governance\Wayback\Trigger') ) {
		/**
		 * The class that handles triggering an Archive snapshot
		 */
		class Trigger {
			/**
			 * @var Trigger $instance holds the single instance of this class
			 * @access private
			 */
			private static Trigger $instance;
			/**
			 * @var array $posts the list of posts that need new Wayback snapshots
			 * @access private
			 */
			private array $posts = array();
			/**
			 * @var int $counter how many posts have been processed in this iteration
			 * @access private
			 */
			private static int $count = 0;
			/**
			 * @var int $limit how many posts should be processed in a single iteration
			 * @access private
			 */
			private int $limit = 10;
			/**
			 * @var string $today a formatted date representing today
			 * @access private
			 */
			private string $today = '';
			/**
			 * @var array $done an array of snapshots that have been successfully captured
			 * @access private
			 */
			private array $done = array();

			/**
			 * Creates the Trigger object
			 *
			 * @access private
			 * @since  0.1
			 */
			private function __construct() {
				if ( ! is_admin() && ! isset( $_REQUEST['cornell/governance/trigger-snapshots'] ) && ! isset( $_REQUEST['cornell/governance/process-snapshots'] ) ) {
					return;
				}

				$this->set_initial_vars();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Trigger
			 * @since   0.1
			 */
			public static function instance(): Trigger {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Setup all of the initial variables for this object
			 *
			 * @access public
			 * @since  0.6.2
			 * @return void
			 */
			public function set_initial_vars() {
				if ( empty( $this->today ) ) {
					$this->today = date( 'Y-m-d' );
				}

				$this->posts = apply_filters( 'cornell/governance/archive/trigger/posts', get_option( 'cornell/governance/archive/trigger/posts/' . $this->today, array() ) );
				$this->limit = apply_filters( 'cornell/governance/archive/trigger/limit', 10 );
				$this->done = get_option( 'cornell/governance/archive/trigger/done/' . $this->today, array() );
			}

			/**
			 * Override the value of the Today string
			 *
			 * @param string $today the new value of the Today string
			 *
			 * @access public
			 * @since  0.6.2
			 * @return void
			 */
			public function set_today( string $today ) {
				if ( ! is_admin() || ! current_user_can( Archive_Trigger::instance()->get_cap() ) ) {
					return;
				}

				$this->today = $today;
			}

			/**
			 * Retrieve and return a count of how many items are in the posts array
			 *
			 * @access public
			 * @since  0.6.2
			 * @return int the number of items in the posts array
			 */
			public function get_posts_count(): int {
				return count( $this->posts );
			}

			/**
			 * Retrieve and return a count of how many items are in the done array
			 *
			 * @access public
			 * @since  0.6.2
			 * @return int the number of items in the done array
			 */
			public function get_done_count(): int {
				return count( $this->done );
			}

			/**
			 * Runs the cron job to trigger the snapshots that need to be saved
			 *
			 * @access public
			 * @since  0.6.2
			 * @return void
			 */
			public function do_cron() {
				$started = get_option( 'cornell/governance/trigger-snapshots/triggered', false );
				if ( $started === false ) {
					if ( isset( $_REQUEST['cornell/governance/trigger-snapshots'] ) ) {
						$started = date( 'Y-m-d' );
						update_option( 'cornell/governance/trigger-snapshots/triggered', $started );
						wp_die( __( 'Archive snapshots have been triggered. They will be processed soon', 'cornell-governance' ) );
					} else {
						wp_die( __( 'Archive snapshots have not yet been triggered; it is likely all have been processed', 'cornell-governance' ) );
					}
				}

				foreach ( $this->posts as $post ) {
					if ( self::$count >= $this->limit ) {
						wp_die( esc_html( __( 'This batch of snapshots has been completed. Waiting until the next request to process more.', 'cornell-governance' ) ), __( 'Round Complete', 'cornell-governance' ) );
					}

					$this->done[ $post->ID ] = Save::instance()->trigger_snapshot( $post );

					if ( ! is_wp_error( $this->done[ $post->ID ] ) ) {
						unset( $this->posts[ $post->ID ] );
						update_option( 'cornell/governance/archive/trigger/posts/' . $this->today, $this->posts );
					}

					update_option( 'cornell/governance/archive/trigger/done/' . $this->today, $this->done );
					self::$count++;
				}

				if ( empty( $this->posts ) ) {
					$this->cleanup_database();
				}
			}

			/**
			 * Run a manual snapshot trigger
			 *
			 * @access public
			 * @since  0.6.2
			 * @return array the list of completed snapshots
			 */
			public function manual_trigger(): array {
				update_option( 'cornell/governance/trigger-snapshots/triggered', $this->today );
				$this->do_cron();

				return $this->done;
			}

			/**
			 * Clean up old lists of snapshots that were triggered
			 *
			 * @access protected
			 * @since  0.6.2
			 * @return void
			 */
			protected function cleanup_database() {
				$this->cleanup_triggers();
				$this->cleanup_snapshots();
				delete_option( 'cornell/governance/trigger-snapshots/triggered' );
			}

			/**
			 * Remove any snapshot triggers that were never handled
			 *
			 * @access private
			 * @since  0.6.2
			 * @return void
			 */
			private function cleanup_triggers() {
				global $wpdb;
				$option_name = 'cornell/governance/archive/trigger/posts/%';

				$query = $wpdb->prepare( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s", $option_name );
				$result = $wpdb->get_results( $query );

				if ( is_wp_error( $result ) ) {
					return;
				}

				foreach ( $result as $row ) {
					$date = str_replace( 'cornell/governance/archive/trigger/posts/', '', $row->option_name );
					$today = \DateTimeImmutable::createFromFormat( 'Y-m-d', $this->today );
					$option_date = \DateTimeImmutable::createFromFormat( 'Y-m-d', $date );
					if ( $option_date < $today ) {
						delete_option( $row->option_name );
					}
				}
			}

			/**
			 * Cleanup and merge any snapshots that were triggered in the past
			 *
			 * @access private
			 * @since  0.6.2
			 * @return void
			 */
			private function cleanup_snapshots() {
				global $wpdb;

				$option_name = 'cornell/governance/archive/trigger/done/%';
				$query = $wpdb->prepare( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s", $option_name );
				$result = $wpdb->get_results( $query );

				if ( is_wp_error( $result ) ) {
					return;
				}

				$all_done = get_option( 'cornell/governance/archive/trigger/done', [] );
				if ( ! is_array( $all_done ) ) {
					$all_done = [];
				}

				$log_limit = apply_filters( 'cornell/governance/archive/log-limit', get_option( 'cornell-governance-archive-log-limit', 30 ) );
				$oldest = date( "Y-m-d", strtotime( $this->today . ' - ' . $log_limit . ' days' ) );
				$check = \DateTimeImmutable::createFromFormat( 'Y-m-d', $oldest );

				foreach ( $all_done as $date => $row ) {
					$row_date = \DateTimeImmutable::createFromFormat( 'Y-m-d', $date );
					if ( $row_date < $check ) {
						delete_option( 'cornell/governance/archive/trigger/done/' . $date );
						unset( $all_done[ $date ] );
					}
				}

				foreach ( $result as $row ) {
					$date = str_replace( 'cornell/governance/archive/trigger/done/', '', $row->option_name );
					$today = \DateTimeImmutable::createFromFormat( 'Y-m-d', $this->today );
					$option_date = \DateTimeImmutable::createFromFormat( 'Y-m-d', $date );
					/*if ( $option_date < $today ) {
						delete_option( $row->option_name );
					}*/

					$date = str_replace( 'cornell/governance/archive/trigger/done/', '', $row->option_name );
					$all_done[ $date ] = array();
					$all_done[ $date ]['option_id'] = $row->option_id;
					$all_done[ $date ]['option_name'] = $row->option_name;
					$all_done[ $date ]['option_value'] = $row->option_value;
				}

				update_option( 'cornell/governance/archive/trigger/done', $all_done );
			}

			/**
			 * Manually trigger a snapshot of a single page or post
			 *
			 * @param int|\WP_Post $post the post being archived
			 *
			 * @access public
			 * @since  0.6.2
			 * @return void
			 */
			public function do_manual_page_snapshot( $post ) {
				if ( is_numeric( $post ) ) {
					$post = get_post( $post );
				}

				if ( is_wp_error( $post ) ) {
					return;
				}

				$this->done[ $post->ID ] = Save::instance()->trigger_snapshot( $post );

				if ( ! is_wp_error( $this->done[ $post->ID ] ) ) {
					unset( $this->posts[ $post->ID ] );
					update_option( 'cornell/governance/archive/trigger/posts/' . $this->today, $this->posts );
				}

				update_option( 'cornell/governance/archive/trigger/done/' . $this->today, $this->done );
			}
		}
	}
}