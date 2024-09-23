<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'No access' );
	}
}

namespace Cornell\Governance\Admin\Submenus\Reports {

	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Admin\Submenus\Reports;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Non_Compliant' ) ) {
		class Non_Compliant extends Base {
			/**
			 * @var Non_Compliant $instance holds the single instance of this class
			 * @access private
			 */
			private static Non_Compliant $instance;

			/**
			 * Construct our Non_Compliant object
			 */
			public function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Non_Compliant
			 * @since   0.1
			 */
			public static function instance(): Non_Compliant {
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
				$data = Reports::instance()->get_var( 'all' );
				$now  = time();

				$pages = array(
					'overdue'   => array(),
					'compliant' => array(),
				);

				if ( ! array_key_exists( 'last-review', $data ) || ! is_array( $data['last-review'] ) ) {
					return array();
				}

				foreach ( $data['last-review'] as $post_id => $datum ) {
					$cycle = $data['review-cycle'][ $post_id ];
					$due   = Helpers::calculate_next_review_date( $datum, $cycle );

					if ( $due <= $now ) {
						$pages['overdue'][ $post_id ] = $due;
					} else {
						$pages['compliant'][ $post_id ] = $due;
					}
				}

				uasort( $pages['overdue'], function ( $a, $b ) {
					if ( $a === $b ) {
						return 0;
					}

					return ( $a < $b ) ? - 1 : 1;
				} );

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
				$data  = $this->get_data();
				$title = __( 'Critically Non-Compliant Pages', 'cornell/governance' );

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
				} else if ( count( $data['overdue'] ) <= 0 ) {
					$output = sprintf(
						'<div class="non-compliant-page-list"><p>%s</p></div>',
						__( '<strong>Congratulations!</strong> There are currently no items that are overdue.', 'cornell/governance' )
					);

					printf(
						'<div class="governance-chart">
								<h3 id="non-compliance-title">%1$s</h3>
								%2$s
							</div>',
						$title,
						$output
					);

					return;
				}

				$headers = '';
				foreach (
					array(
						'id'      => __( 'ID', 'cornell/governance' ),
						'title'   => __( 'Title', 'cornell/governance' ),
						'due'     => __( 'Due Date', 'cornell/governance' ),
						'steward' => __( 'Steward', 'cornell/governance' ),
					) as $key => $value
				) {
					$headers .= sprintf( '<th class="non-compliant-%1$s">%2$s</th>', $key, $value );
				}

				$output = sprintf( '<table class="non-compliant-page-list">
	<thead>
		<tr>%1$s</tr>
		</thead>
		<tfoot>
		<tr>%1$s</tr>
</tfoot>',
					$headers
				);

				$output .= '<tbody>';

				foreach ( $data['overdue'] as $key => $datum ) {
					$post = get_post( $key );

					$output .= sprintf( '<tr id="content-%1$d">', $key );

					foreach (
						array(
							'id'      => $key,
							'title'   => sprintf( '<a href="%1$s">%2$s</a>', get_edit_post_link( $key, 'link' ), $post->post_title ),
							'due'     => Helpers::format_date( $datum ),
							'steward' => get_user_by( 'id', $post->post_author )->user_email,
						) as $k => $value
					) {
						$output .= sprintf( '<td class="non-compliant-%1$s">%2$s</td>', $k, $value );
					}

					$output .= '</tr>';
				}

				$output .= '</tbody></table>';

				printf(
					'<div class="governance-chart">
								<h3 id="non-compliance-title">%1$s</h3>
								%2$s
							</div>',
					$title,
					$output
				);
			}
		}
	}
}