<?php
namespace {
	if ( ! defined( 'ABSPATH' ) )
		die('No access');
}

namespace Cornell\Governance\Admin\Submenus\Reports {

	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Submenus\Reports\Base' ) ) {
		abstract class Base {
			/**
			 * @var string $page_slug the slug of the page on which this report is being output
			 */
			private string $page_slug = '';

			/**
			 * @var string $report_name a slug for the specific report being generated
			 */
			protected string $report_name = '';

			/**
			 * Construct our Reports object
			 */
			public function __construct() {
				$this->enqueue_scripts();
				$this->report_name = sanitize_title( get_class($this) );
			}

			/**
			 * Set the page slug for the page on which this report is output
			 *
			 * @param string $page_slug the slug
			 *
			 * @access public
			 * @since 1.0.1
			 * @return void
			 */
			public function set_page_slug( $page_slug ) {
				$this->page_slug = $page_slug;
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
				wp_enqueue_script( 'governance-charts', Helpers::plugins_url( '/dist/js/cornell-governance/charts' . $min . '.js' ), array(), Plugin::$version, true );
				wp_enqueue_style( 'governance-charts', Helpers::plugins_url( '/dist/css/cornell-governance/charts' . $min . '.css' ), array(), Plugin::$version, 'all' );
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

			/**
			 * Format the data and prepare it for download as a CSV
			 *
			 * @access protected
			 * @since  0.1
			 * @return void
			 */
			abstract protected function export_data();

			/**
			 * Output some action buttons to allow users to export the underlying chart data
			 *
			 * @access protected
			 * @since  1.0.1
			 * @return void
			 */
			protected function action_buttons() {
				if ( empty( $this->page_slug ) ) {
					return;
				}

				print( '<div class="governance-action-buttons">' );
				print( '<form method="get">' );
				printf( '<input type="hidden" name="page" value="%s" />', $this->page_slug );
				printf( '<input type="hidden" name="export-what" value="%s" />', $this->report_name );
				wp_nonce_field( 'governance-export-data' );
				print( '<div class="button-row">' );
				printf( '<button class="button-secondary" name="export-data" value="csv">%s</button>', __( 'Export CSV', 'cornell-governance' ) );
				printf( '<button class="button-secondary" name="export-data" value="json">%s</button>', __( 'Export JSON', 'cornell-governance' ) );
				print( '</div>' );
				print( '</form>' );
				print( '</div>' );
			}
		}
	}
}