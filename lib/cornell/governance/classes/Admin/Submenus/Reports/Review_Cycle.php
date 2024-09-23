<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'No access' );
	}
}

namespace Cornell\Governance\Admin\Submenus\Reports {

	use Cornell\Governance\Admin\Submenus\Reports;

	if ( ! class_exists( 'Review_Cycle' ) ) {
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
			 * @return array the data to be included in the chart
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
					'chartLabel' => __( 'Review Cycle', 'cornell/governance' ),
					'labels' => array(
						__( 'Every 3 Months', 'cornell/governance' ),
						__( 'Every 6 Months', 'cornell/governance' ),
						__( 'Every 12 Months', 'cornell/governance' ),
					),
					'datasets' => array(
						array(
							'label' => __( 'Review Cycle', 'cornell/governance' ),
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
					__( 'Review Cycle Breakdown', 'cornell/governance' ),
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
			}
		}
	}
}