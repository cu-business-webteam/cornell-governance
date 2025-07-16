<?php


namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Settings_Import_Export {

	use Cornell\Governance\Admin\Submenus\Import_Export;
	use Cornell\Governance\Helpers;
	use WP_Error;

	if ( ! class_exists( 'Import' ) ) {
		class Import extends Base {
			/**
			 * @var Import $instance holds the single instance of this class
			 * @access private
			 */
			private static Import $instance;
			/**
			 * @var string $file the path to the import file
			 */
			private string $file = '';
			/**
			 * @var int $id the ID of the import file
			 */
			private int $id = 0;

			/**
			 * Construct our Import object
			 *
			 * @param array $props the variables to be set
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
			 * @param array $props the variables to be set
			 *
			 * @access  public
			 * @return  Import
			 * @since   0.1
			 */
			public static function instance(): Import {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Sets the necessary variables for the import
			 *
			 * @param array $props the variables to be set
			 *
			 * @access public
			 * @return void
			 * @since  0.6.2
			 */
			public function set_vars( array $props ) {
				if ( array_key_exists( 'id', $props ) ) {
					Helpers::log( 'Setting the ID of the imported file to ' . $props['id'] . '.' );
					$this->id = (int) $props['id'];
				}
				if ( array_key_exists( 'file', $props ) ) {
					Helpers::log( 'Setting the import file to ' . $props['file'] . '.' );
					$this->file = $props['file'];
				}
			}

			/**
			 * Processes the import file
			 *
			 * @access public
			 * @return void
			 * @since  0.6.2
			 */
			public function process_file() {
				$this->data = array();

				try {
					$json = fopen( $this->file, 'r' );
					if ( false === $json ) {
						throw new \Exception( 'Unable to open Settings Import file: ' . $this->file );
					}

					$contents = fread( $json, filesize( $this->file ) );
					fclose( $json );

					$this->data = json_decode( $contents, true );
				} catch ( \Exception $e ) {
					Import_Export::instance()->generate_error( 'import', $e->getMessage() );

					return;
				}
			}

			/**
			 * Output the imported data in an HTML table for debugging purposes
			 *
			 * @access public
			 * @return void
			 * @since  0.6.2
			 */
			public function output_data() {
				try {
					$this->process_file();
				} catch ( \Exception $e ) {
					Import_Export::instance()->generate_error( 'import', $e->getMessage() );

					return;
				}

				/*print( '<pre><code>' );
				var_dump( $this->data );
				print( '</code></pre>' );*/

				if ( empty( $this->headers ) ) {
					$this->set_headers();
				}

				print( '<table>' );

				foreach ( $this->data as $key => $value ) {
					print( '<tr>' );
					printf( '<th scope="row">%s</th>', $this->headers[ $key ] );
					if ( is_array( $value ) ) {
						printf( '<td><ol><li>%s</li></ol></td>', implode( '</li>' . PHP_EOL . '<li>', $value ) );
					} else {
						printf( '<td>%s</td>', $value );
					}
					print( '</tr>' );
				}

				print( '</table>' );
			}

			/**
			 * Import the processed data into the database
			 *
			 * @access public
			 * @return bool|\WP_Error true if successful; WP_Error if failed
			 * @since  0.6.2
			 */
			public function import_data() {
				try {
					$this->process_file();
				} catch ( \Exception $e ) {
					echo Import_Export::instance()->generate_error( 'import', $e->getMessage() );

					return new WP_Error( 'import', $e->getMessage() );
				}

				if ( count( $this->data ) <= 0 ) {
					return new WP_Error( 'import', __( 'The import file appears to be empty', 'cornell/governance' ) );
				}

				foreach ( $this->data as $key => $value ) {
					switch ( $key ) {
						case 'change-form-props':
						case 'post-types' :
							update_option( 'cornell-governance-' . $key, array_fill_keys( $value, true ) );
							break;
						default :
							update_option( 'cornell-governance-' . $key, $value );
							break;
					}

				}

				return true;
			}
		}
	}
}
