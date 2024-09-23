<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'No access' );
	}
}

namespace Cornell\Governance\Admin\Submenus\Reports {

	use Cornell\Governance\Admin\Submenus\Reports;

	if ( ! class_exists( 'Primary_Audience' ) ) {
		class Primary_Audience extends Base {
			/**
			 * @var Primary_Audience $instance holds the single instance of this class
			 * @access private
			 */
			private static Primary_Audience $instance;

			/**
			 * Construct our Primary_Audience object
			 */
			public function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Primary_Audience
			 * @since   0.1
			 */
			public static function instance(): Primary_Audience {
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
				$terms = $this->get_audiences();

				$pages = array();

				foreach ( Reports::instance()->get_var( 'primary-audience' ) as $post_id => $value ) {
					$pages[$value][$post_id] = $terms[$value]->name;
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
			private function get_audiences(): array {
				$audiences = get_terms( array(
					'taxonomy' => 'audience',
					'hide_empty' => false
				) );

				$terms = array();
				foreach ( $audiences as $audience ) {
					$terms[$audience->slug] = $audience;
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

				$terms = $this->get_audiences();

				$labels = array();
				$data = array();
				foreach ( $terms as $term ) {
					if ( ! array_key_exists( $term->slug, $list ) ) {
						continue;
					}

					$labels[] = $term->name;
					$data[] = count( $list[$term->slug] );
				}

				$output = array(
					'canvasID' => 'primary-audience-chart',
					'type'     => 'bar',
					'chartLabel' => __( 'Content by Primary Audience', 'cornell/governance' ),
					'labels' => $labels,
					'datasets' => array(
						array(
							'label' => __( 'Primary Audience', 'cornell/governance' ),
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

				Reports::instance()->chartConfig['primaryAudience'] = $output;

				print( '<div class="governance-chart">' );

				printf(
					'<h3 id="%2$s-title">%1$s</h3>
							<canvas role="img" id="%2$s" aria-labelledby="%2$s-title" aria-describedby="%2$s-data"></canvas>',
					__( 'Content by Primary Audience', 'cornell/governance' ),
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