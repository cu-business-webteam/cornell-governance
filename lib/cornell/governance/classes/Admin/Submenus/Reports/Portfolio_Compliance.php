<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'No access' );
	}
}

namespace Cornell\Governance\Admin\Submenus\Reports {

	use Cornell\Governance\Admin\Admin;
	use Cornell\Governance\Admin\Fields\Post_Types;
	use Cornell\Governance\Admin\Submenus\Liaison_Dashboard;
	use Cornell\Governance\Admin\Submenus\Reports;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Admin\HTML_Table;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Submenus\Reports\Portfolio_Compliance' ) ) {
		class Portfolio_Compliance extends Base {
			/**
			 * @var Portfolio_Compliance $instance holds the single instance of this class
			 * @access private
			 */
			private static Portfolio_Compliance $instance;
			/**
			 * @var array $all_data all of the data retrieved
			 * @access private
			 */
			private array $all_data = array();

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
			 * @return  Portfolio_Compliance
			 * @since   0.1
			 */
			public static function instance(): Portfolio_Compliance {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Query the database for the data to be displayed in this chart
			 *
			 * @access protected
			 * @return array
			 * @since  0.1
			 */
			protected function gather_data(): array {
				if ( ! empty( $this->all_data ) ) {
					return $this->all_data;
				}

				global $wpdb;

				$args = array(
					'post_type'      => Post_Types::instance()->get_input_value(),
					'posts_per_page' => - 1,
					'post_status'    => Helpers::get_page_status_list(),
				);

				$args = Liaison_Dashboard::instance()->add_meta_query( $args );

				$args['fields'] = 'ids';

				$ids = new \WP_Query( $args );

				$q = $wpdb->prepare( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key=%s", Plugin::INFO_META_KEY );
				if ( false !== $ids && ! is_wp_error( $ids ) ) {
					if ( empty( $ids ) ) {
						/* The current user has no content */
						return array();
					}

					$q .= " AND post_id IN (" . implode( ',', $ids->posts ) . ")";
				}

				$results = $wpdb->get_results( $q );
				if ( is_wp_error( $results ) ) {
					return array();
				}

				$allData = array();

				foreach ( $results as $result ) {
					$result->meta_value = maybe_unserialize( $result->meta_value );

					if ( ! is_array( $result->meta_value ) ) {
						$result->meta_value = array();
					}

					foreach ( $result->meta_value as $key => $value ) {
						if ( ! array_key_exists( $key, $allData ) ) {
							$allData[ $key ] = array();
						}

						$allData[ $key ][ $result->post_id ] = $value;
					}
				}

				$this->all_data = $allData;

				return $allData;
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

				$data = $this->gather_data();

				if ( empty( $data ) ) {
					return array();
				}

				$now = time();

				Helpers::log( sprintf( 'Our full array of data looks like: %s', print_r( $data, true ) ), 'warning' );
				Helpers::log( sprintf( 'Our last review data array looks like: %s', print_r( $data['last-review'], true ) ), 'warning' );

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
				$title = esc_html( __( 'Portfolio Compliance Overview', 'cornell-governance' ) );

				$data = $this->get_data();

				/*if ( empty( $data ) ) {
					printf(
						'<div class="governance-chart">
								<h3>%1$s</h3>
								<p>%2$s</p>
							</div>',
						$title,
						__( 'There are currently no pages available for this report', 'cornell/governance' )
					);

					return;
				}*/

				echo '<div class="governance-liaison-overview">';

				$this->do_compliance_overview( $data );

				$this->do_non_compliant_overview();

				$this->do_steward_performance();

				$this->do_liaison_workflow();

				echo '</div>';
			}

			/**
			 * Prepare and output the Performance Overview section of the dashboard
			 *
			 * @param array $data the compliance data
			 *
			 * @access private
			 * @return void
			 * @since  1.0.2
			 */
			private function do_compliance_overview( array $data ) {
				$total = 0;
				foreach ( $data as $key => $value ) {
					$total += count( $value );
				}

				if ( 0 === $total ) {
					return;
				}

				$compliant_total = count( $data['compliant'] );
				$compliant_pct   = $compliant_total / $total * 100;

				$overdue_total = count( $data['overdue'] );
				$overdue_pct   = $overdue_total / $total * 100;

				$due_total = count( $data['7-days'] ) + count( $data['30-days'] ) + count( $data['60-days'] );
				$due_pct   = $due_total / $total * 100;

				$output = '<div class="governance-portfolio-overview">';
				$output .= sprintf( '<h3 class="governance-portfolio-overview-title">%s</h3>', __( 'Portfolio Compliance Overview', 'cornell-governance' ) );
				$output .= sprintf( '<div class="column governance-compliance-compliant"><span class="percentage">%s</span><p class="percentage-label">%s</p></div>', $compliant_pct . '%', __( 'Compliant Pages', 'cornell-governance' ) );
				$output .= sprintf( '<div class="column governance-compliance-due"><span class="percentage">%s</span><p class="percentage-label">%s</p></div>', $due_pct . '%', __( 'Due in Next 60 days', 'cornell-governance' ) );
				$output .= sprintf( '<div class="column governance-compliance-overdue"><span class="percentage">%s</span><p class="percentage-label">%s</p></div>', $overdue_pct . '%', __( 'Overdue Pages', 'cornell-governance' ) );
				$output .= $this->do_compliance_progress_bar( $compliant_pct );
				$output .= sprintf( '<p class="column governance-compliance-total-compliant">%s</p>', sprintf( esc_html( __( 'Total Pages in Compliance: %d', 'cornell-governance' ) ), $compliant_total ) );
				$output .= '</div>';

				echo $output;
			}

			/**
			 * Prepare and return the progress bar for the Compliance Overview section
			 *
			 * @param string $compliant_pct the percentage to display
			 *
			 * @access private
			 * @since  1.1
			 * @return string the progress bar HTML
			 */
			private function do_compliance_progress_bar( string $compliant_pct ): string {
				$format = '<div class="governance-statusbar" aria-hidden="true" style="--status-percentage: %1$s">';
				$format .= '<span class="statusbar-start">%2$s</span>';
				$format .= '<div class="governance-compliance-overall-status"> <span class="completion-percentage-label">%1$s</span> </div>';
				$format .= '<span class="statusbar-end">%3$s</span>';
				$format .= '</div>';
				return sprintf( $format, $compliant_pct . '%', esc_html( __( '0%', 'cornell-governance' ) ), __( '100%', 'cornell-governance' ) );
			}

			/**
			 * Prepare and output non-compliant overview section
			 *
			 * @access private
			 * @return void
			 * @since  1.0.2
			 */
			private function do_non_compliant_overview() {
				$data = $this->gather_data();
				$page_list = $this->get_data();

				$pages = array();

				foreach ( $data['review-cycle'] as $key => $value ) {
					if ( array_key_exists( $key, $page_list['unreviewed'] ) ) {
						$pages[ 0 ][ $key ] = get_post( $key );
						continue;
					}

					$next_review                   = Helpers::calculate_next_review_date( ( array_key_exists( $key, $data['last-review'] ) ? $data['last-review'][ $key ] : 0 ), $value );
					$pages[ $next_review ][ $key ] = get_post( $key );
				}

				krsort( $pages, SORT_NUMERIC );

				$output = '<div class="governance-portfolio-overdue-overview">';
				$output .= sprintf( '<h3 class="governance-portfolio-overdue-overview-heading">%s</h3>', __( 'Non-Compliant Pages', 'cornell-governance' ) );

				$headers = array(
					'date' => esc_html( __( 'Due Date', 'cornell-governance' ) ),
					'count' => esc_html( __( 'Pages', 'cornell-governance' ) ),
				);

				$output .= HTML_Table::instance()->open( __( 'Count of Pages that are Overdue', 'cornell-governance' ) );
				$output .= HTML_Table::instance()->get_row( $headers, 'header' );
				$output .= HTML_Table::instance()->get_row( $headers, 'footer' );
				$output .= HTML_Table::instance()->open_body();

				foreach ( $pages as $key => $value ) {
					if ( $key >= time() ) {
						continue;
					}

					if ( $key === 0 ) {
						$date = esc_html( __( 'Not yet reviewed', 'cornell-governance' ) );
					} else {
						$date = date( get_option( 'date_format' ), $key );
						//$date = $key;
					}

					$output .= HTML_Table::instance()->get_row( array( 'date' => $date, 'count' => count( $value ) ) );
					//$output .= sprintf( '<p class="governance-overdue-overview-date">%s: %d</p>', sprintf( __( 'Due %s', 'cornell/governance' ), $date ), count( $value ) );
				}

				$output .= HTML_Table::instance()->close_body();
				$output .= HTML_Table::instance()->close();

				$output .= '</div>';

				echo $output;
			}

			/**
			 * Prepare and output the Steward Performance section of the dashboard
			 *
			 * @access private
			 * @return void
			 * @since  1.0.2
			 */
			private function do_steward_performance() {
				$data = $this->gather_data();

				$pages = array();

				foreach ( $data['review-cycle'] as $key => $value ) {
					$post                                                 = get_post( $key );
					$pages[ $post->post_author ][ $key ]                  = $post;
					$pages[ $post->post_author ][ $key ]->governance_meta = array();

					foreach ( $data as $metakey => $values ) {
						foreach ( $values as $id => $metavalue ) {
							if ( $id === $key ) {
								$pages[ $post->post_author ][ $key ]->governance_meta[ $metakey ] = $metavalue;
							}
						}
					}

					$last_review                                                               = array_key_exists( $key, $data['last-review'] ) ? $data['last-review'][ $key ] : 0;
					$pages[ $post->post_author ][ $key ]->governance_meta['next-review']       = Helpers::calculate_next_review_date( $last_review, $value );
					if ( empty( $last_review ) ) {
						$pages[ $post->post_author ][ $key ]->governance_meta['compliance-status'] = 'unreviewed';
					} else {
						$pages[ $post->post_author ][ $key ]->governance_meta['compliance-status'] = Helpers::get_compliance_status( $pages[ $post->post_author ][ $key ]->governance_meta );
					}

				}

				$output = '<div class="governance-portfolio-steward-performance">';
				$output .= sprintf( '<h3 class="governance-portfolio-steward-performance-heading">%s</h3>', __( 'Steward Performance', 'cornell-governance' ) );

				$table = HTML_Table::instance();

				$output .= $table->open( esc_html( __( 'Overview of Steward Compliance and Responsibility', 'cornell-governance' ) ), array( 'governance-steward-compliance-table' ) );

				$headers = array(
					'steward'   => esc_html( __( 'Steward', 'cornell-governance' ) ),
					'pages'     => esc_html( __( 'Assigned Pages', 'cornell-governance' ) ),
					'compliant' => esc_html( __( 'Compliant Pages', 'cornell-governance' ) ),
					'due'       => esc_html( __( 'Review Due Pages', 'cornell-governance' ) ),
					'overdue'   => esc_html( __( 'Overdue Pages', 'cornell-governance' ) ),
					'unreviewed' => esc_html( __( 'Unreviewed Pages', 'cornell-governance' ) ),
				);
				$output .= $table->get_row( $headers, 'header' );
				$output .= $table->get_row( $headers, 'footer' );

				foreach ( $pages as $author => $list ) {
					$author_name = $this->get_author_name( $author );

					$assigned = count( $list );
					$status   = array(
						'compliant' => array(),
						'due'       => array(),
						'overdue'   => array(),
						'unreviewed' => array(),
					);
					foreach ( $list as $page ) {
						if ( is_string( $page->governance_meta['compliance-status'] ) && 'unreviewed' === $page->governance_meta['compliance-status'] ) {
							$status['unreviewed'][ $page->ID ] = $page->ID;
						} else if ( $page->governance_meta['compliance-status']['overdue'] ) {
							$status['overdue'][ $page->ID ] = $page->ID;
						} else if ( $page->governance_meta['compliance-status']['due'] ) {
							$status['due'][ $page->ID ] = $page->ID;
						} else {
							$status['compliant'][ $page->ID ] = $page->ID;
						}
					}

					$output .= $table->get_row( array(
						'steward'   => $author_name,
						'pages'     => $assigned,
						'compliant' => count( $status['compliant'] ),
						'due'       => count( $status['due'] ),
						'overdue'   => count( $status['overdue'] ),
						'unreviewed' => count( $status['unreviewed'] ),
					) );
				}

				$output .= $table->close_body();
				$output .= $table->close();

				$output .= '</div>';

				echo $output;
			}

			/**
			 * Attempt to retrieve a name for the specified user ID
			 *
			 * @param int $author the user ID of the user
			 *
			 * @access private
			 * @return string the author name
			 * @since  1.0.2
			 */
			private function get_author_name( int $author ): string {
				$author_name = get_the_author_meta( 'display_name', $author );
				if ( empty( $author_name ) ) {
					$author_name = get_the_author_meta( 'user_nicename', $author );
				}
				if ( empty( $author_name ) ) {
					$author_name = get_the_author_meta( 'first_name', $author ) . ' ' . get_the_author_meta( 'last_name', $author );
				}
				if ( empty( $author_name ) ) {
					$author_name = get_the_author_meta( 'user_email', $author );
				}
				if ( empty( $author_name ) ) {
					$author_name = get_the_author_meta( 'user_login', $author );
				}
				if ( empty( $author_name ) ) {
					$author_name = sprintf( esc_html( __( 'User with ID %d not found', 'cornell-governance' ) ), $author );
				}

				return $author_name;
			}

			/**
			 * Prepare and output the Liaison Workflow section
			 *
			 * @access private
			 * @return void
			 * @since  1.0.2
			 */
			private function do_liaison_workflow() {
				$output = '';

				if ( ! empty( Admin::instance()->get_liaison_workflow() ) ) {
					$output .= '<div class="column governance-liaison-workflow">';
					$output .= sprintf( '<h3 class="governance-liaison-workflow-heading">%s</h3>', __( 'Liaison Responsibilities', 'cornell-governance' ) );
					$output .= '<div class="governance-liaison-workflow-content">';
					$output .= Admin::instance()->get_liaison_workflow();
					$output .= '</div>';
					$output .= '</div>';
				}

				echo $output;
			}

			/**
			 * Format the data and prepare it for download as a CSV
			 *
			 * @access protected
			 * @return void
			 * @since  0.1
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
				$all_data    = Reports::instance()->get_var( 'all' );
				$report_data = array();
				foreach ( $pages as $page_id => $page_data ) {
					$report_data[ $page_id ] = array(
						'page_id'            => $page_id,
						'page_title'         => get_the_title( $page_id ),
						'page_url'           => get_permalink( $page_id ),
						'primary-audience'   => $report_data['primary-audience'][ $page_id ],
						'secondary-audience' => $report_data['secondary-audience'][ $page_id ],
						'last-reviewed'      => $report_data['last-review'][ $page_id ],
						'review-cycle'       => $page_data,
						'steward'            => $report_data['steward'][ $page_id ],
						'steward_email'      => get_user_by( 'id', $report_data['steward'][ $page_id ] )->user_email,
						'steward_username'   => get_user_by( 'id', $report_data['steward'][ $page_id ] )->user_login,
						'supervisor'         => $report_data['supervisor'][ $page_id ],
						'liaison'            => $report_data['liaison'][ $page_id ],
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