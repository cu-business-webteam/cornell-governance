<?php


namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Import_Export {

	use Cornell\Governance\Admin\Submenus\Import_Export;

	if ( ! class_exists( '\Cornell\Governance\Admin\Import_Export\Generic_Export' ) ) {
		class Generic_Export extends Base {
			/**
			 * @var Generic_Export $instance holds the single instance of this class
			 * @access private
			 */
			private static Generic_Export $instance;

			/**
			 * @var string $file the path to the export file
			 */
			private string $file = '';

			/**
			 * @var string the file name to use for this export
			 */
			private string $filename = '';

			/**
			 * Construct our Import object
			 *
			 * @access private
			 * @return void
			 * @since  1.0.1
			 */
			private function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Generic_Export
			 * @since   1.0.1
			 */
			public static function instance(): Generic_Export {
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
			 * @since  1.0.1
			 */
			private function get_data() {
				$this->data = array_merge( $this->headers, $this->data );
			}

			/**
			 * Set the headers array for this export
			 *
			 * @param array $headers the array of headers to use
			 *
			 * @access public
			 * @since  1.0.1
			 * @return void
			 */
			public function set_headers( array $headers) {
				$this->headers = $headers;
			}

			/**
			 * Fill the data array with information gathered externally
			 *
			 * @param array $data the data to fill the property with
			 *
			 * @access public
			 * @since  1.0.1
			 * @return void
			 */
			public function set_data( array $data ) {
				$this->data = $data;
			}

			/**
			 * Set the file name to use for this export
			 *
			 * @access public
			 * @since  1.0.1
			 * @return void
			 */
			public function set_filename( string $filename ) {
				$this->filename = $filename;
			}

			/**
			 * Generate the default information for the meta information
			 *
			 * @access private
			 * @return array the array of defaults
			 * @since  1.0.1
			 */
			private function get_default_meta(): array {
				return array();
			}

			/**
			 * Generate the spreadsheet file
			 *
			 * @access public
			 * @return void
			 * @since  1.0.1
			 */
			public function get_file() {
				/*$dir = wp_upload_dir();
				if ( ! is_dir( $dir['basedir'] . '/governance-exports/' ) ) {
					mkdir( $dir['basedir'] . '/governance-exports/', 0755, true );
				}*/

				$ext        = 'csv';
				$filename   = ( empty( $this->filename ) ? 'governance-export' : $this->filename ) . '-' . date( "Y-m-d_H:i:s" ) . '.' . $ext;
				/*$this->file = wp_upload_dir()['basedir'] . '/governance-export/' . $filename;*/

				$this->get_data();

				try {
					header( 'Content-Type: application/csv' );
					header( 'Content-Disposition: attachment; filename="' . $filename . '";' );

					$csv = fopen( 'php://output', 'w' );
					foreach ( $this->data as $row ) {
						fputcsv( $csv, $row );
					}

					fclose( $csv );

					ob_flush();

					exit;
				} catch ( \Exception $exception ) {
					Import_Export::instance()->generate_error( 'export', $exception->getMessage() );

					return;
				}
			}

			/**
			 * Generate a JSON file of our data and force the user to download the file
			 *
			 * @access public
			 * @since  1.0.1
			 * @return void
			 */
			public function get_file_json() {
				$ext        = 'json';
				$filename   = ( empty( $this->filename ) ? 'governance-export' : $this->filename ) . '-' . date( "Y-m-d_H:i:s" ) . '.' . $ext;

				$this->get_data();

				try {
					header( 'Content-Type: application/json' );
					header( 'Content-Disposition: attachment; filename="' . $filename . '";' );

					$json = fopen( 'php://output', 'w' );
					fwrite( $json, json_encode( $this->data, JSON_PRETTY_PRINT ) );

					fclose( $json );

					ob_flush();

					exit;
				} catch ( \Exception $exception ) {
					Import_Export::instance()->generate_error( 'export', $exception->getMessage() );

					return;
				}
			}
		}
	}
}
