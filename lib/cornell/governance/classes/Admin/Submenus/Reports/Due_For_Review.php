<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'No access' );
	}
}

namespace Cornell\Governance\Admin\Submenus\Reports {

	use Cornell\Governance\Admin\Submenus\Reports;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Due_For_Review' ) ) {
		class Due_For_Review extends Base {
			/**
			 * @var Due_For_Review $instance holds the single instance of this class
			 * @access private
			 */
			private static Due_For_Review $instance;

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
			 * @return  Due_For_Review
			 * @since   0.1
			 */
			public static function instance(): Due_For_Review {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Retrieve and return a specific portion of the report data
			 *
			 * @access public
			 * @since  0.1
			 * @return array the value of a specific part of the report data
			 */
			public function get_var( $key ): array {
				$data = $this->get_data();
				if ( array_key_exists( $key, $data ) ) {
					return $data[$key];
				}

				return array();
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

				foreach ( $data['last-review'] as $post_id => $datum ) {
					$cycle = $data['review-cycle'][ $post_id ];
					$due   = Helpers::calculate_next_review_date( $datum, $cycle );

					if ( $due <= $now ) {
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
				$title = __( 'Due For Review', 'cornell/governance' );

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
				} else if ( count( $data['7-days'] ) + count( $data['30-days'] ) + count( $data['60-days'] ) <= 0 ) {
					$output = sprintf(
						'<div class="due-for-review-page-list"><p>%s</p></div>',
						__( '<strong>Congratulations!</strong> There are currently no items that are due for review.', 'cornell/governance' )
					);

					printf(
						'<div class="governance-chart">
								<h3 id="due-for-review-title">%1$s</h3>
								%2$s
							</div>',
						$title,
						$output
					);

					return;
				}

				$output = '';
				foreach ( array( '7-days', '30-days', '60-days' ) as $period ) {
					$output .= $this->do_compliance_table( $data, $period );
				}

				printf(
					'<div class="governance-chart">
								<h3 id="non-compliance-title">%1$s</h3>
								%2$s
							</div>',
					$title,
					$output
				);
			}

			/**
			 * Output a table specific to the compliance window
			 *
			 * @param array $data the data being processed and displayed
			 * @param string $period the key for the period being processed and displayed
			 *
			 * @access private
			 * @since  2023.05
			 * @return string the formatted HTML table
			 */
			private function do_compliance_table( array $data, string $period ): string {
				if ( count( $data[$period] ) <= 0 ) {
					return '';
				}

				$output = sprintf( '<table class="due-for-review-page-list">
<caption>%1$s</caption>
<thead>
<tr>%2$s</tr>
</thead>
<tfoot>
<tr>%2$s</tr>
</tfoot>',
				sprintf( __( 'Due in the Next %d Days', 'cornell/governance' ), str_replace( '-days', '', $period ) ),
				$this->get_table_headers()
				);

				foreach ( $data[$period] as $key => $datum ) {
					$post = get_post( $key );

					$output .= sprintf( '<tr id="content-%1$d">', $key );

					foreach ( array(
						'id' => $key,
						'title' => sprintf( '<a href="%1$s">%2$s</a>', get_edit_post_link( $key, 'link' ), $post->post_title ),
						'due' => Helpers::format_date( $datum ),
						'steward' => get_user_by( 'id', $post->post_author )->user_email,
					) as $k => $value ) {
						$output .= sprintf( '<td class="non-compliant-%1$s">%2$s</td>', $k, $value );
					}

					$output .= '</tr>';
				}

				$output .= '</tbody></table>';

				return $output;
			}

			/**
			 * Build and return the table headers for thead and tfoot
			 *
			 * @access private
			 * @since  2023.05
			 * @return string the table header cells
			 */
			private function get_table_headers(): string {
				$output = '';

				foreach ( array (
					'id' => __( 'ID', 'cornell/governance' ),
					'title' => __( 'Title', 'cornell/governance' ),
					'due' => __( 'Due Date', 'cornell/governance' ),
					'steward' => __( 'Steward', 'cornell/governance' ),
				) as $key => $value ) {
					$output .= sprintf( '<th class="due-for-review-%1$s">%2$s</th>', $key, $value );
				}

				return $output;
			}
		}
	}
}