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

	if ( ! class_exists( 'Stewards' ) ) {
		class Stewards extends Base {
			/**
			 * @var Stewards $instance holds the single instance of this class
			 * @access private
			 */
			private static Stewards $instance;

			/**
			 * Construct our Stewards object
			 */
			public function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Stewards
			 * @since   0.1
			 */
			public static function instance(): Stewards {
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
				$terms = $this->get_stewards();

				$pages = array();

				foreach ( $this->pages as $page ) {
					if ( ! array_key_exists( $page->post_author, $pages ) ) {
						$pages[$page->post_author] = array();
					}

					$pages[$page->post_author][$page->ID] = get_the_author_meta( 'display_name', $page->post_author );
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
			private function get_stewards(): array {
				$this->pages = get_posts( array(
					'post_type' => Plugin::instance()->get_post_types(),
					'post_status' => 'publish',
					'posts_per_page' => -1
				) );

				$terms = array();
				foreach ( $this->pages as $page ) {
					if ( array_key_exists( $page->post_author, $terms ) ) {
						continue;
					}

					$terms[$page->post_author] = get_user_by( 'id', $page->post_author );
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

				$terms = $this->get_stewards();

				$labels = array();
				$data = array();
				foreach ( $terms as $term ) {
					if ( ! array_key_exists( $term->ID, $list ) ) {
						continue;
					}

					$labels[] = $term->data->user_nicename;
					$data[] = count( $list[$term->ID] );
				}

				$output = array(
					'canvasID' => 'stewards-chart',
					'type'     => 'doughnut',
					'chartLabel' => __( 'Content by Steward', 'cornell/governance' ),
					'labels' => $labels,
					'datasets' => array(
						array(
							'label' => __( 'Steward', 'cornell/governance' ),
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

				Reports::instance()->chartConfig['stewards'] = $output;

				print( '<div class="governance-chart">' );

				printf(
					'<h3 id="%2$s-title">%1$s</h3>
							<canvas role="img" id="%2$s" aria-labelledby="%2$s-title" aria-describedby="%2$s-data"></canvas>',
					__( 'Content by Steward', 'cornell/governance' ),
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