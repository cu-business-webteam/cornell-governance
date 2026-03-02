<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'No access' );
	}
}

namespace Cornell\Governance\Admin\Submenus\Reports {

	use Cornell\Governance\Admin\Fields\Post_Types;
	use Cornell\Governance\Admin\Submenus\Liaison_Dashboard;
	use Cornell\Governance\Admin\Submenus\Reports;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;
	use WP_Query;

	if ( ! class_exists( 'Liaison_Status' ) ) {
		class Liaison_Status extends Base {
			/**
			 * @var Liaison_Status $instance holds the single instance of this class
			 * @access private
			 */
			private static Liaison_Status $instance;

			/**
			 * Construct our Review_Cycle object
			 */
			public function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Liaison_Status
			 * @since   0.1
			 */
			public static function instance(): Liaison_Status {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Query the database for the data to be displayed in this chart
			 *
			 * @access protected
			 * @return array
			 * @since  0.1
			 */
			protected function gather_data(): array {
				global $wpdb;

				$args = array(
					'post_type'      => Post_Types::instance()->get_input_value(),
					'posts_per_page' => -1,
					'post_status'    => Helpers::get_page_status_list(),
				);

				$args = Liaison_Dashboard::instance()->add_meta_query( $args );

				$args['fields'] = 'ids';

				$ids = new WP_Query( $args );

				$q = $wpdb->prepare( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key=%s", Plugin::INFO_META_KEY );
				if ( false !== $ids && ! is_wp_error( $ids ) ) {
					if ( empty( $ids ) ) {
						/* The current user has no content */
						return array();
					}

					$q .= " AND post_id IN (" . implode( ',', $ids->posts ) . ")";
				}

				$results = $wpdb->get_results( $q );
				if ( is_wp_error( $results ) ) {
					return array();
				}

				$allData = array();

				foreach ( $results as $result ) {
					$result->meta_value = maybe_unserialize( $result->meta_value );

					if ( ! is_array( $result->meta_value ) ) {
						$result->meta_value = array();
					}

					foreach ( $result->meta_value as $key => $value ) {
						if ( ! array_key_exists( $key, $allData ) ) {
							$allData[ $key ] = array();
						}

						$allData[ $key ][ $result->post_id ] = $value;
					}
				}

				return $allData;
			}

			/**
			 * Retrieve the data to be displayed in the chart
			 *
			 * @access protected
			 * @return array the data to be included in the chart
			 * @since  0.1
			 */
			protected function get_data(): array {
				$pages = array(
					'unreviewed' => array(),
					'overdue'   => array(),
					'7-days'    => array(),
					'30-days'   => array(),
					'60-days'   => array(),
					'compliant' => array(),
				);

				$data = $this->gather_data();

				if ( empty( $data ) ) {
					return array();
				}

				$now = time();

				foreach ( $data['review-cycle'] as $post_id => $datum ) {
					$last_review = array_key_exists( $post_id, $data['last-review'] ) ? $data['last-review'][$post_id] : 0;
					$cycle = $datum;
					$due   = Helpers::calculate_next_review_date( $last_review, $datum );

					Helpers::log( sprintf( 'The value of reviewed for %d is %s', $post_id, print_r( $last_review, true ) ), 'info' );

					if ( empty( $last_review ) ) {
						$pages['unreviewed'][ $post_id ] = $due;
					} else if ( $due <= $now ) {
						$pages['overdue'][ $post_id ] = $due;
					} else if ( strtotime( '+ 60 days' ) < $due ) {
						$pages['compliant'][ $post_id ] = $due;
					} else if ( strtotime( '+ 30 days' ) < $due ) {
						$pages['60-days'][ $post_id ] = $due;
					} else if ( strtotime( '+ 7 days' ) < $due ) {
						$pages['30-days'][ $post_id ] = $due;
					} else {
						$pages['7-days'][ $post_id ] = $due;
					}
				}

				return $pages;
			}

			/**
			 * Output the chart data
			 *
			 * @access protected
			 * @return void
			 * @since  0.1
			 */
			protected function output_data() {
				$title = __( 'Liaison Compliance Status', 'cornell/governance' );

				$data = $this->get_data();

				if ( empty( $data ) ) {
					printf(
						'<div class="governance-chart">
								<h3>%1$s</h3>
								<p>%2$s</p>
							</div>',
						$title,
						__( 'There are currently no pages available for this report', 'cornell/governance' )
					);

					return;
				}

				$output = array(
					'canvasID'   => 'compliance-status-chart',
					'type'       => 'doughnut',
					'chartLabel' => __( 'Review Due Date', 'cornell/governance' ),
					'labels'     => array(
						__( 'Not Reviewed Yet', 'cornell/governance' ),
						__( 'Overdue', 'cornell/governance' ),
						__( 'Due in the next 7 days', 'cornell/governance' ),
						__( 'Due in the next 30 days', 'cornell/governance' ),
						__( 'Due in the next 60 days', 'cornell/governance' ),
						__( 'Fully compliant', 'cornell/governance' ),
					),
					'datasets'   => array(
						array(
							'label'           => __( 'Review Due Date', 'cornell/governance' ),
							'data'            => array(
								count( $data['unreviewed'] ),
								count( $data['overdue'] ),
								count( $data['7-days'] ),
								count( $data['30-days'] ),
								count( $data['60-days'] ),
								count( $data['compliant'] ),
							),
							'backgroundColor' => array(
								'rgb(255,0,0)',
								'rgb(255, 165, 0)',
								'rgb(135, 206, 235)',
								'rgb(0, 0, 139)',
								'rgb(112, 130, 56)',
							),
						),
					),
					'options'    => array(
						'plugins' => array(
							'legend' => array(
								'position' => 'bottom',
							),
						),
					),
				);

				Reports::instance()->chartConfig['complianceStatus'] = $output;

				print ( '<div class="governance-chart">' );

				printf(
					'<h3 id="%2$s-title">%1$s</h3>
							<canvas role="img" id="%2$s" aria-labelledby="%2$s-title" aria-describedby="%2$s-data"></canvas>',
					$title,
					$output['canvasID']
				);

				$lists = array();

				foreach ( $output['labels'] as $key => $label ) {
					$lists[] = sprintf(
						'<dt>%1$s</dt><dd>%2$d</dd>',
						$label,
						$output['datasets'][0]['data'][ $key ]
					);
				}

				printf(
					'<details id="%2$s-data"><summary>%3$s</summary><dl>%1$s</dl></details>',
					implode( '', $lists ),
					$output['canvasID'],
					__( 'Reveal source data for this chart', 'cornell/governance' )
				);

				print( '</div>' );

				add_action( 'admin_footer', array( Reports::instance(), 'localize_script' ) );
			}

			/**
			 * Format the data and prepare it for download as a CSV
			 *
			 * @access protected
			 * @since  0.1
			 * @return void
			 */
			protected function export_data() {
				$pages = $this->get_data();
				if ( empty( $pages ) ) {
					return;
				}


			}
		}
	}
}