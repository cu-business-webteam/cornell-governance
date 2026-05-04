<?php


namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Settings_Import_Export {

	use Cornell\Governance\Admin\Submenus\Import_Export;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Settings_Import_Export\Export' ) ) {
		class Export extends Base {
			/**
			 * @var Export $instance holds the single instance of this class
			 * @access private
			 */
			private static Export $instance;

			/**
			 * @var string $file the path to the export file
			 */
			private string $file = '';

			/**
			 * @var array $defaults the default values for each setting
			 * @access private
			 */
			private array $defaults = array();

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
			 * @return  Export
			 * @since   0.1
			 */
			public static function instance(): Export {
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

				$this->get_default_meta();

				$this->data['plugin-version'] = Plugin::$version;

				foreach ( $this->headers as $name => $title ) {
					$this->data[$name] = get_option( 'cornell-governance-' . $name, $this->defaults[$name] );
				}
			}

			/**
			 * Generate the default information for the meta information
			 *
			 * @access private
			 * @return array the array of defaults
			 * @since  0.6.2
			 */
			private function get_default_meta(): array {
				$this->defaults = array();
				$namespace = '\Cornell\Governance\Admin\Fields';

				foreach ( $this->classes as $class ) {
					$name = $namespace . '\\' . $class;
					if ( class_exists( $name ) ) {
						$obj = $name::instance();
						$this->defaults[$obj->get_field_id()] = $obj->get_default();
					}
				}

				return $this->defaults;
			}

			/**
			 * Generate the spreadsheet file
			 *
			 * @access public
			 * @return void
			 * @since  0.6.2
			 */
			public function get_file() {
				$this->get_file_json();
			}

			/**
			 * Generate a JSON file of our data and force the user to download the file
			 *
			 * @access public
			 * @since  0.6.2
			 * @return void
			 */
			public function get_file_json() {
				$ext        = 'json';
				$filename   = 'governance-settings-' . date( "Y-m-d_H:i" ) . '.' . $ext;

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
