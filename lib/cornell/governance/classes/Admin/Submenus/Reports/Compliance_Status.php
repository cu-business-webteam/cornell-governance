<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'No access' );
	}
}

namespace Cornell\Governance\Admin\Submenus\Reports {

	use Cornell\Governance\Admin\Submenus\Reports;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Compliance_Status' ) ) {
		class Compliance_Status extends Base {
			/**
			 * @var Compliance_Status $instance holds the single instance of this class
			 * @access private
			 */
			private static Compliance_Status $instance;

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
			 * @return  Compliance_Status
			 * @since   0.1
			 */
			public static function instance(): Compliance_Status {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
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

				$data = Reports::instance()->get_var( 'all' );

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
				$title = __( 'Compliance Status', 'cornell/governance' );

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
						__( 'Overdue', 'cornell/governance' ),
						__( 'Due in the next 7 days', 'cornell/governance' ),
						__( 'Due in the next 30 days', 'cornell/governance' ),
						__( 'Due in the next 60 days', 'cornell/governance' ),
						__( 'Fully compliant', 'cornell/governance' ),
						__( 'Not yet reviewed', 'cornell/governance' ),
					),
					'datasets'   => array(
						array(
							'label'           => __( 'Review Due Date', 'cornell/governance' ),
							'data'            => array(
								count( $data['overdue'] ),
								count( $data['7-days'] ),
								count( $data['30-days'] ),
								count( $data['60-days'] ),
								count( $data['compliant'] ),
								count( $data['unreviewed'] ),
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

				$export = \Cornell\Governance\Admin\Import_Export\Generic_Export::instance();

				$headers = array(
					'page_id'            => __( 'Page ID', 'cornell/governance' ),
					'page_title'         => __( 'Page Title', 'cornell/governance' ),
					'page_url'           => __( 'Page URL', 'cornell/governance' ),
					'primary-audience'   => __( 'Primary Audience', 'cornell/governance' ),
					'secondary-audience' => __( 'Secondary Audience', 'cornell/governance' ),
					'last-reviewed'      => __( 'Last Reviewed', 'cornell/governance' ),
					'review-cycle'       => __( 'Review Cycle', 'cornell/governance' ),
					'steward'            => __( 'Steward', 'cornell/governance' ),
					'steward_email'      => __( 'Steward Email', 'cornell/governance' ),
					'steward_username'   => __( 'Steward Username', 'cornell/governance' ),
					'supervisor'         => __( 'Secondary Contact', 'cornell/governance' ),
					'liaison'            => __( 'Liaison', 'cornell/governance' ),
				);

				$export->set_headers( $headers );
				$all_data = Reports::instance()->get_var( 'all' );
				$report_data = array();
				foreach ( $pages as $page_id => $page_data ) {
					$report_data[ $page_id ] = array(
						'page_id' => $page_id,
						'page_title' => get_the_title( $page_id ),
						'page_url' => get_permalink( $page_id ),
						'primary-audience' => $report_data['primary-audience'][$page_id],
						'secondary-audience' => $report_data['secondary-audience'][$page_id],
						'last-reviewed' => $report_data['last-review'][$page_id],
						'review-cycle' => $page_data,
						'steward' => $report_data['steward'][$page_id],
						'steward_email' => get_user_by( 'id', $report_data['steward'][$page_id] )->user_email,
						'steward_username' => get_user_by( 'id', $report_data['steward'][$page_id] )->user_login,
						'supervisor' => $report_data['supervisor'][$page_id],
						'liaison' => $report_data['liaison'][$page_id],
					);
				}

				$export->set_data( $report_data );
				$export->set_filename( 'compliance-status' );

				if ( $_REQUEST['export-data'] === 'json' ) {
					$export->get_file_json();
				} else {
					$export->get_file();
				}
			}
		}
	}
}