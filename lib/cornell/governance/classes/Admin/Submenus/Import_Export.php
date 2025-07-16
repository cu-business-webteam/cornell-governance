<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Submenus {

	use Cornell\Governance\Admin\Import_Export\Export;
	use Cornell\Governance\Admin\Import_Export\Import;
	use Cornell\Governance\Admin\Import_Export\Sample_Format;
	use Cornell\Governance\Admin\Settings_Import_Export\Export as Settings_Export;
	use Cornell\Governance\Admin\Settings_Import_Export\Import as Settings_Import;

	class Import_Export extends Base {
		/**
		 * @var Import_Export $instance holds the single instance of this class
		 * @access private
		 */
		private static Import_Export $instance;
		/**
		 * @var array $headers holds an array of the CSV header information
		 * @access private
		 */
		private array $headers = array();

		/**
		 * Creates the Menu object
		 *
		 * @access private
		 * @since  0.1
		 */
		function __construct() {
			if ( ! is_admin() ) {
				return;
			}

			if ( isset( $_REQUEST['governance-action'] ) && wp_verify_nonce( $_GET['cornell-governance-import-export-nonce'], 'cornell-governance-import-export' ) ) {
				if ( 'export-json' == $_REQUEST['governance-action'] ) {
					$this->handle_export( 'json' );
				} else {
					$this->handle_export();
				}
				exit;
			} else if ( isset( $_GET['governance-settings-action'] ) && wp_verify_nonce( $_GET['cornell-governance-import-export-settings-nonce'], 'cornell-governance-import-export-settings' ) ) {
				$this->handle_settings_export();
			}

			parent::__construct( array(
				'title'       => __( 'Cornell Governance: Import / Export', 'cornell/governance' ),
				'menu_name'   => __( 'Import/Export', 'cornell/governance' ),
				'slug'        => 'cornell-governance-import-export',
				'description' => __( 'Import or export page governance information', 'cornell/governance' ),
			) );
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Import_Export
		 * @since   0.1
		 */
		public static function instance(): Import_Export {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Outputs the content of the Page List
		 *
		 * @access protected
		 * @return void
		 * @since  0.1
		 */
		protected function display() {
			printf( '<div class="wrap"><h2>%s</h2>', $this->title );

			if ( isset( $_REQUEST['governance-action'] ) && 'import' == $_REQUEST['governance-action'] ) {
				$this->handle_upload();
			} else if ( isset( $_REQUEST['governance-action'] ) && 'import-settings' == $_REQUEST['governance-action'] ) {
				$this->handle_settings_upload();
			} else {
				print( '<div class="cornell-governance-metabox">' );

				printf( '<h2>%s</h2>', __( 'Governance Settings', 'cornell/governance' ) );

				print( '<div class="import-export-wrap">' );

				$this->do_export_settings_box();

				$this->do_import_settings_box();

				print( '</div><!-- .import-export-wrap -->' );
				print( '</div><!-- .cornell-governance-metabox -->' );

				print( '<div class="cornell-governance-metabox">' );

				printf( '<h2>%s</h2>', __( 'Governance Data', 'cornell/governance' ) );

				print( '<div class="import-export-wrap">' );

				$this->do_export_box();

				$this->do_import_box();

				$this->do_sample_table_box();

				$this->do_sample_csv_box();

				print( '</div><!-- .import-export-wrap -->' );
				print( '</div><!-- .cornell-governance-metabox -->' );
			}

			print( '</div>' );
		}

		/**
		 * Build and output the Export Settings box for the page
		 *
		 * @access protected
		 * @since  0.6.2
		 * @return void
		 */
		protected function do_export_settings_box() {
			print( '<div class="export-settings">' );

			printf( '<h3>%s</h3>', __( 'Export Governance Settings', 'cornell/governance' ) );

			print( '<form method="get">' );

			printf( '<p>%s</p>', __( 'Generate a JSON file with all of the existing Governance Settings for this site.', 'cornell/governance' ) );

			wp_nonce_field( 'cornell-governance-import-export-settings', 'cornell-governance-import-export-settings-nonce' );
			foreach ( array( 'json' ) as $type ) {
				$class = 'primary';
				printf( '<button type="submit" name="governance-settings-action" value="export-%1$s" class="button button-%3$s">%2$s</button>', $type, sprintf( __( 'Export %s', 'cornell/governance' ), strtoupper( $type ) ), $class );
			}
			print( '</form>' );

			print ( '</div><!-- .export-settings -->' );
		}

		protected function do_import_settings_box() {
			print( '<div class="import-settings">' );

			printf( '<h3>%s</h3>', __( 'Import Governance Settings', 'cornell/governance' ) );

			printf( '<p>%s</p>', __( 'Please upload a JSON file that was generated from a Settings Export on another site', 'cornell/governance' ) );

			wp_import_upload_form( 'admin.php?page=cornell-governance-import-export&governance-action=import-settings' );

			print( '</div><!-- .import-settings -->' );
		}

		/**
		 * Build and output the Export box for the page
		 *
		 * @access protected
		 * @since  0.6.2
		 * @return void
		 */
		protected function do_export_box() {
			print( '<div class="export">' );

			printf( '<h3>%s</h3>', __( 'Export Governance Data', 'cornell/governance' ) );

			print( '<form method="get">' );

			printf( '<p>%s</p>', __( 'Generate a CSV or JSON file with all of the existing Governance Information from this site.', 'cornell/governance' ) );

			wp_nonce_field( 'cornell-governance-import-export', 'cornell-governance-import-export-nonce' );
			print( '<div class="button-row">' );
			foreach ( array( 'csv', 'json' ) as $type ) {
				$class = $type === 'csv' ? 'primary' : 'secondary';
				printf( '<button type="submit" name="governance-action" value="export-%1$s" class="button button-%3$s">%2$s</button>', $type, sprintf( __( 'Export %s', 'cornell/governance' ), strtoupper( $type ) ), $class );
			}
			print( '</div><!-- .button-row -->' );
			print( '</form>' );

			print ( '</div><!-- .export -->' );
		}

		/**
		 * Build and output the Import box for the page
		 *
		 * @access protected
		 * @since  0.6.2
		 * @return void
		 */
		protected function do_import_box() {
			print( '<div class="import">' );

			printf( '<h3>%s</h3>', __( 'Import Governance Data', 'cornell/governance' ) );

			printf( '<p>%s</p>', __( 'Please upload a CSV file in the exact format represented by the Export process', 'cornell/governance' ) );

			wp_import_upload_form( 'admin.php?page=cornell-governance-import-export&governance-action=import' );

			print( '</div><!-- .import -->' );
		}

		/**
		 * Build and output the Sample Table box for the page
		 *
		 * @access protected
		 * @since  0.6.2
		 * @return void
		 */
		protected function do_sample_table_box() {
			print( '<div class="export-sample-table full-width">' );
			printf( '<h3>%s</h3>', __( 'Sample Export Data', 'cornell/governance' ) );
			printf( '<p>%s</p>', __( 'Below is an HTML table with information about the export format', 'cornell/governance' ) );
			Sample_Format::instance()->output_table();
			print( '</div><!-- .export-sample-table -->' );
		}

		/**
		 * Build and output the Sample Table box for the page
		 *
		 * @access protected
		 * @since  0.6.2
		 * @return void
		 */
		protected function do_sample_csv_box() {
			print( '<div class="export-sample-csv full-width">' );
			printf( '<h3>%s</h3>', __( 'Sample Export CSV Data', 'cornell/governance' ) );
			printf( '<label for="cornell-governance-sample-csv-data">%s</label>', __( 'Below is sample data you can copy and paste into a blank CSV file to get you started', 'cornell/governance' ) );
			Sample_Format::instance()->output_data();
			print( '</div><!-- .export-sample-csv -->' );
		}

		/**
		 * Adds the screen options to the page
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function add_options() {
		}

		/**
		 * Output the submenu page
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function do_submenu_page() {
			$this->display();
		}

		private function handle_settings_upload() {
			if ( false === check_admin_referer( 'import-upload' ) ) {
				$this->generate_error( 'import', __( 'Nonce could not be verified.', 'cornell/governance' ) );
			}

			$file = wp_import_handle_upload();
			if ( isset( $file['error'] ) ) {
				echo $this->generate_error( 'import', $file );

				return;
			} else if ( ! file_exists( $file['file'] ) ) {
				echo $this->generate_error( 'import', __( 'The settings export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'cornell/governance' ), esc_html( $file['file'] ) );

				return;
			} else {
				echo $this->generate_success( 'import', __( 'We appear to have successfully uploaded the file: ' . print_r( $file, true ), 'cornell/governance' ) );
			}

			$import = Settings_Import::instance();
			$import->set_vars( $file );
			printf( '<div class="wrap"><h2>%s</h2>', __( 'Settings Import', 'cornell/governance' ) );
			if ( $success = $import->import_data() ) {
				if ( is_wp_error( $success ) ) {
					echo $this->generate_error( 'import', $success );
					return;
				}

				printf( '<p><strong>%s</strong></p>', __( 'The following data appear to have been imported successfully', 'cornell/governance' ) );

				$import->output_data();
			}

			print( '</div>' );
		}

		/**
		 * Handle the upload of an import file
		 *
		 * @access private
		 * @return void
		 * @since  0.6.2
		 */
		private function handle_upload() {
			if ( false === check_admin_referer( 'import-upload' ) ) {
				$this->generate_error( 'import', __( 'Nonce could not be verified.', 'cornell/governance' ) );
			}

			$file = wp_import_handle_upload();
			if ( isset( $file['error'] ) ) {
				echo $this->generate_error( 'import', $file );

				return;
			} else if ( ! file_exists( $file['file'] ) ) {
				echo $this->generate_error( 'import', __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'cornell/governance' ), esc_html( $file['file'] ) );

				return;
			} else {
				echo $this->generate_success( 'import', __( 'We appear to have successfully uploaded the file: ' . print_r( $file, true ), 'cornell/governance' ) );
			}

			$import = Import::instance();
			$import->set_vars( $file );
			printf( '<div class="wrap"><h2>%s</h2>', __( 'Import', 'cornell/governance' ) );
			if ( $success = $import->import_data() ) {
				if ( is_wp_error( $success ) ) {
					echo $this->generate_error( 'import', $success );
					return;
				}

				printf( '<p><strong>%s</strong></p>', __( 'The following data appear to have been imported successfully', 'cornell/governance' ) );

				$import->output_data();
			}

			print( '</div>' );
		}

		/**
		 * Handles exporting the data from the database
		 *
		 * @param string $type whether to export a CSV file or a JSON file
		 *
		 * @access private
		 * @return void
		 * @since  0.6.2
		 */
		private function handle_export( string $type = 'csv' ) {
			/*printf( '<div class="wrap"><h2>%s</h2>', __( 'Export', 'cornell/governance' ) );
			printf( '<p>%s</p>', __( 'The Excel spreadsheet should download automatically', 'cornell/governance' ) );
			print( '</div>' );*/
			if ( 'json' == $type ) {
				Export::instance()->get_file_json();
			} else {
				Export::instance()->get_file();
			}
		}

		private function handle_settings_export() {
			Settings_Export::instance()->get_file();
		}

		/**
		 * Generate an appropriate error message after an error occurs
		 *
		 * @param string $action whether this was an import or export
		 * @param array|string|\WP_Error $error the error information
		 * @param array $classes what type of admin notice to generate
		 *
		 * @access public
		 * @return string the HTML of the error message
		 * @since  0.6.2
		 */
		public function generate_error( string $action, $error, array $classes = array( 'error' ) ): string {
			if ( is_wp_error( $error ) ) {
				$message = $error->get_error_message();
			} else if ( is_array( $error ) && array_key_exists( 'error', $error ) ) {
				$message = $error['error'];
			} else {
				$message = $error;
			}

			$classes[] = 'notice';
			$classes[] = 'notice-' . $classes[0];

			if ( in_array( 'error', $classes ) ) {
				return sprintf( '<div class="%3$s"><h2>%1$s</h2><p>%2$s</p></div>', sprintf( __( 'There was an error with the %s', 'cornell/governance' ), $action ), esc_html( $message ), implode( ' ', $classes ) );
			} else if ( in_array( 'success', $classes ) ) {
				return sprintf( '<div class="%3$s"><h2>%1$s</h2><p>%2$s</p></div>', sprintf( __( 'The %s appears to have been completed successfully', 'cornell/governance' ), $action ), esc_html( $message ), implode( ' ', $classes ) );
			} else {
				return sprintf( '<div class="%3$s"><h2>%1$s</h2><p>%2$s</p></div>', sprintf( __( 'A message was generated during the %s', 'cornell/governance' ), $action ), esc_html( $message ), implode( ' ', $classes ) );
			}
		}

		/**
		 * Generate and output a success message
		 *
		 * @param string $action whether this was an import or export
		 * @param array|string|\WP_Error $message the success information
		 *
		 * @access public
		 * @return string the HTML of the success message
		 * @since  0.6.2
		 */
		public function generate_success( string $action, $message ): string {
			return $this->generate_error( $action, $message, array( 'success', 'is-dismissible' ) );
		}

		/**
		 * Generate and output a debug/info message
		 *
		 * @param string $action whether this was an import or export
		 * @param array|string|\WP_Error $message the info
		 *
		 * @access public
		 * @since  0.6.2
		 * @return string the HTML of the info message
		 */
		public function generate_info_message( string $action, $message ): string {
			return $this->generate_error( $action, $message, array( 'info', 'is-dismissible' ) );
		}
	}
}