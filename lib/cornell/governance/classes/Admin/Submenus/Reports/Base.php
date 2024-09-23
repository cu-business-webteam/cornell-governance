<?php
namespace {
	if ( ! defined( 'ABSPATH' ) )
		die('No access');
}

namespace Cornell\Governance\Admin\Submenus\Reports {

	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Base' ) ) {
		abstract class Base {
			/**
			 * Construct our Reports object
			 */
			public function __construct() {
				$this->enqueue_scripts();
			}

			/**
			 * Enqueue our scripts and styles
			 *
			 * @access protected
			 * @since  0.1
			 * @return void
			 */
			protected function enqueue_scripts() {
				$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
				wp_enqueue_script( 'governance-charts', Helpers::plugins_url( '/dist/js/cornell-governance/charts' . $min . '.js' ), array(), false, true );
				wp_enqueue_style( 'governance-charts', Helpers::plugins_url( '/dist/css/cornell-governance/charts' . $min . '.css' ), array(), false, 'all' );
			}

			/**
			 * Output the chart
			 *
			 * @access public
			 * @since  0.1
			 * @return void
			 */
			public function display() {
				$this->output_data();
			}

			/**
			 * Retrieve the data to be included in the chart
			 *
			 * @access protected
			 * @since  0.1
			 * @return array the chart data
			 */
			abstract protected function get_data(): array;

			/**
			 * Format and output the chart data
			 *
			 * @access protected
			 * @since  0.1
			 * @return void
			 */
			abstract protected function output_data();
		}
	}
}