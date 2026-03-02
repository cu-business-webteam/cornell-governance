<?php


namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Import_Export {

	use Cornell\Governance\Admin\Submenus\Import_Export;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Sample_Format' ) ) {
		class Sample_Format extends Base {
			/**
			 * @var Sample_Format $instance holds the single instance of this class
			 * @access private
			 */
			private static Sample_Format $instance;

			/**
			 * Construct our Import object
			 *
			 * @access private
			 * @return void
			 * @since  0.6.2
			 */
			private function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Sample_Format
			 * @since   0.1
			 */
			public static function instance(): Sample_Format {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className();
				}

				return self::$instance;
			}

			/**
			 * Retrieve the metadata to be exported to the spreadsheet
			 *
			 * @access private
			 * @return void
			 * @since  0.6.2
			 */
			private function get_data() {
				$this->data = array();

				$this->data[] = $this->headers;

				$this->data[] = apply_filters( 'cornell/governance/import-export/sample-descriptions', array(
					__( 'Numerical WP Post ID (read-only)', 'cornell/governance' ),
					__( 'Full-Text Post Title (read-only)', 'cornell/governance' ),
					__( 'Full URL to the page (read-only)', 'cornell/governance' ),
					__( 'Full-Text Page Goal information for this page', 'cornell/governance' ),
					__( 'Full-Text Purpose/Problem information for this page', 'cornell/governance' ),
					__( 'Slug for Primary Audience term', 'cornell/governance' ),
					__( 'Slug for Secondary Audience term', 'cornell/governance' ),
					__( 'The date and time when this page was last reviewed (leave blank if never reviewed)', 'cornell/governance' ),
					__( 'Enter 3, 6 or 12 to indicate how many months should go by before the page needs to be reviewed again', 'cornell/governance' ),
					__( 'Full text of the tasks that should be assigned specifically to this page.' . PHP_EOL . 'Each hard-return within this field generates a new task', 'cornell/governance' ),
					__( 'The numerical user ID of the WordPress user that should be the assigned author of this page. This is the primary source for the author.', 'cornell/governance' ),
					__( 'The email address associated with the user that should be assigned as the author of this page. This is the secondary source for the author (if the ID column is blank).', 'cornell/governance' ),
					__( 'The username of the user that should be assigned as the author of this page. This is the tertiary source for the author (if both ID and email are blank).', 'cornell/governance' ),
					__( 'The email address for the Secondary Contact', 'cornell/governance' ),
					__( 'The email address for the Liaison (must be someone with appropriate permissions to be a liaison)', 'cornell/governance' ),
					__( 'Any text (markdown-formatting is permitted) that should be added as the Page Documentation', 'cornell/governance' ),
				) );

				$this->data[] = apply_filters( 'cornell/governance/import-export/sample-data', array(
					0,
					__( 'Sample Page Title', 'cornell/governance' ),
					'https://example.com/page-slug/',
					__( 'Sample Goal', 'cornell/governance' ),
					__( 'Sample Problem', 'cornell/governance' ),
					'fake-audience-slug-1',
					'fake-audience-slug-2',
					date( 'c', time() ),
					3,
					implode( PHP_EOL, array(
						'Task 1',
						'Task 2',
					) ),
					1234,
					'author@example.com',
					'author',
					'supervisor@example.com',
					'liaison@example.com',
					sprintf( __( 'Sample Note _with_ [basic Markdown formatting](%s)', 'cornell/governance' ), 'https://www.markdownguide.org/basic-syntax/' ),
				) );

				return;
			}

			/**
			 * Output the sample data in an HTML table for illustrative purposes
			 *
			 * @access public
			 * @return void
			 * @since  0.6.2
			 */
			public function output_table() {
				if ( empty( $this->data ) ) {
					$this->get_data();
				}

				printf( '<table><caption>%s</caption><thead><tr>', __( 'Sample CSV Data', 'cornell/governance' ) );
				$headers = array_shift( $this->data );
				foreach ( $headers as $header ) {
					printf( '<th scope="col">%s</th>', $header );
				}
				print( '</tr></thead><tbody>' );
				foreach ( $this->data as $row ) {
					print( '<tr>' );
					foreach ( $row as $column ) {
						printf( '<td>%s</td>', $column );
					}
					print( '</tr>' );
				}
				print( '</tbody></table>' );

				array_unshift( $this->data, $headers );
			}

			/**
			 * Output the sample data in CSV format to be copied and pasted
			 *
			 * @access public
			 * @since  0.6.2
			 * @return void
			 */
			public function output_data() {
				if ( empty( $this->data ) ) {
					$this->get_data();
				}

				print( '<textarea id="cornell-governance-sample-csv-data" wrap="off" readonly>' );

				$csv = fopen('php://output', 'w');
				foreach ( $this->data as $row ) {
					fputcsv( $csv, $row );
				}
				fclose( $csv );

				print( '</textarea>' );
			}
		}
	}
}
