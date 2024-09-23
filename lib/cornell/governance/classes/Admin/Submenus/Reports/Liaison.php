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

	if ( ! class_exists( 'Liaison' ) ) {
		class Liaison extends Base {
			/**
			 * @var Liaison $instance holds the single instance of this class
			 * @access private
			 */
			private static Liaison $instance;

			/**
			 * Construct our Liaison object
			 */
			public function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Liaison
			 * @since   0.1
			 */
			public static function instance(): Liaison {
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
				$terms = $this->get_liaisons();

				$pages = array();

				foreach ( Reports::instance()->get_var( 'liaison' ) as $post_id => $value ) {
					$pages[$value][$post_id] = $terms[$value]->display_name;
				}

				return $pages;
			}

			/**
			 * Retrieve and return an associative array of audience terms
			 *
			 * @access private
			 * @since  0.1
			 * @return array the array of terms
			 */
			private function get_liaisons(): array {
				$users = get_users( array(
					'capability' => Plugin::instance()->get_capability(),
				) );

				$terms = array();
				foreach ( $users as $user ) {
					$terms[$user->user_email] = $user;
				}

				return $terms;
			}

			/**
			 * Output the chart data
			 *
			 * @access protected
			 * @return void
			 * @since  0.1
			 */
			protected function output_data() {
				$list = $this->get_data();

				$terms = $this->get_liaisons();

				$labels = array();
				$data = array();
				foreach ( $terms as $term ) {
					if ( ! array_key_exists( $term->user_email, $list ) ) {
						continue;
					}

					$labels[] = $term->display_name;
					$data[] = count( $list[$term->user_email] );
				}

				$output = array(
					'canvasID' => 'liaison-chart',
					'type'     => 'doughnut',
					'chartLabel' => sprintf( __( 'Content by %s Liaison', 'cornell/governance' ), Plugin::instance()->get_managing_office() ),
					'labels' => $labels,
					'datasets' => array(
						array(
							'label' => __( 'Liaison', 'cornell/governance' ),
							'data' => $data,
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
				foreach ( $data as $datum ) {
					$color = Helpers::randomColor();
					$output['datasets'][0]['backgroundColor'][] = vsprintf('rgb(%d,%d,%d)', $color );
				}

				Reports::instance()->chartConfig['liaison'] = $output;

				print( '<div class="governance-chart">' );

				printf(
					'<h3 id="%2$s-title">%1$s</h3>
							<canvas role="img" id="%2$s" aria-labelledby="%2$s-title" aria-describedby="%2$s-data"></canvas>',
					sprintf( __( 'Content by %s Liaison', 'cornell/governance' ), Plugin::instance()->get_managing_office() ),
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