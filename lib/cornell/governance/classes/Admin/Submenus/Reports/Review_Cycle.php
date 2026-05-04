<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'No access' );
	}
}

namespace Cornell\Governance\Admin\Submenus\Reports {

	use Cornell\Governance\Admin\Submenus\Reports;

	if ( ! class_exists( '\Cornell\Governance\Admin\Submenus\Reports\Review_Cycle' ) ) {
		class Review_Cycle extends Base {
			/**
			 * @var Review_Cycle $instance holds the single instance of this class
			 * @access private
			 */
			private static Review_Cycle $instance;

			/**
			 * Construct our Review_Cycle object
			 */
			public function __construct() {
				parent::__construct();

				if ( isset( $_REQUEST['export-data'] ) && isset( $_REQUEST['export-what'] ) && $_REQUEST['export-what'] == $this->report_name ) {
					$this->export_data();
				}
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Review_Cycle
			 * @since   0.1
			 */
			public static function instance(): Review_Cycle {
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
			 * @return array{3-month: array, 6-month: array, 12-month: array} the data to be included in the chart
			 * @since  0.1
			 */
			protected function get_data(): array {
				$pages = array(
					'3-month'  => array(),
					'6-month'  => array(),
					'12-month' => array()
				);

				foreach ( Reports::instance()->get_var( 'review-cycle' ) as $post_id => $value ) {
					switch ( $value ) {
						case 3:
							$pages['3-month'][ $post_id ] = $value;
							break;
						case 6 :
							$pages['6-month'][ $post_id ] = $value;
							break;
						default :
							$pages['12-month'][ $post_id ] = $value;
							break;
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
				$data = $this->get_data();

				$output = array(
					'canvasID' => 'review-cycle-chart',
					'type'     => 'pie',
					'chartLabel' => esc_html( __( 'Review Cycle', 'cornell-governance' ) ),
					'labels' => array(
						esc_html( __( 'Every 3 Months', 'cornell-governance' ) ),
						esc_html( __( 'Every 6 Months', 'cornell-governance' ) ),
						esc_html( __( 'Every 12 Months', 'cornell-governance' ) ),
					),
					'datasets' => array(
						array(
							'label' => esc_html( __( 'Review Cycle', 'cornell-governance' ) ),
							'data' => array(
								count( $data['3-month'] ),
								count( $data['6-month'] ),
								count( $data['12-month'] ),
							),
							'backgroundColor' => array(
								'rgb(255, 99, 132)',
								'rgb(54, 162, 235)',
								'rgb(255, 205, 86)',
							),
						),
					),
					'options' => array(
						'plugins' => array(
							'legend' => array(
								'position' => 'bottom',
							),
						),
					),
				);

				Reports::instance()->chartConfig['reviewCycle'] = $output;

				print( '<div class="governance-chart">' );

				printf(
					'<h3 id="%2$s-title">%1$s</h3>
							<canvas role="img" id="%2$s" aria-labelledby="%2$s-title" aria-describedby="%2$s-data"></canvas>',
					esc_html( __( 'Review Cycle Breakdown', 'cornell-governance' ) ),
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
					__( 'Reveal source data for this chart', 'cornell-governance' )
				);

				$this->action_buttons();

				print( '</div>' );
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
					'page_id'            => esc_html( __( 'Page ID', 'cornell-governance' ) ),
					'page_title'         => esc_html( __( 'Page Title', 'cornell-governance' ) ),
					'page_url'           => esc_html( __( 'Page URL', 'cornell-governance' ) ),
					'primary-audience'   => esc_html( __( 'Primary Audience', 'cornell-governance' ) ),
					'secondary-audience' => esc_html( __( 'Secondary Audience', 'cornell-governance' ) ),
					'last-reviewed'      => esc_html( __( 'Last Reviewed', 'cornell-governance' ) ),
					'review-cycle'       => esc_html( __( 'Review Cycle', 'cornell-governance' ) ),
					'steward'            => esc_html( __( 'Steward', 'cornell-governance' ) ),
					'steward_email'      => esc_html( __( 'Steward Email', 'cornell-governance' ) ),
					'steward_username'   => esc_html( __( 'Steward Username', 'cornell-governance' ) ),
					'supervisor'         => esc_html( __( 'Secondary Contact', 'cornell-governance' ) ),
					'liaison'            => esc_html( __( 'Liaison', 'cornell-governance' ) ),
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
				$export->set_filename( 'review-cycle' );

				if ( $_REQUEST['export-data'] === 'json' ) {
					$export->get_file_json();
				} else {
					$export->get_file();
				}

				exit;
			}
		}
	}
}