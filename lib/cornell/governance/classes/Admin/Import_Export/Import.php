<?php


namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Import_Export {

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

				if ( stristr( $this->file, '.csv' ) ) {
					try {
						$csv = fopen( $this->file, 'r' );
						if ( false === $csv ) {
							throw new \Exception( 'Unable to open CSV Import file: ' . $this->file );
						}

						while ( ( $data = fgetcsv( $csv, 1000, "," ) ) !== false ) {
							$tmp = array();
							foreach ( $data as $index => $cell ) {
								$tmp[ array_keys( $this->headers )[ $index ] ] = $cell;
							}
							$this->data[] = $tmp;
						}

						fclose( $csv );
					} catch ( \Exception $e ) {
						Import_Export::instance()->generate_error( 'import', $e->getMessage() );

						return;
					}
				} else if ( stristr( $this->file, '.json' ) ) {
					try {
						$json = fopen( $this->file, 'r' );
						if ( false === $json ) {
							throw new \Exception( 'Unable to open JSON Import file: ' . $this->file );
						}

						$contents = fread( $json, filesize( $this->file ) );
						fclose( $json );

						$this->data = json_decode( $contents, true );
					} catch ( \Exception $e ) {
						Import_Export::instance()->generate_error( 'import', $e->getMessage() );

						return;
					}
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

				print( '<table>' );

				foreach ( $this->data as $i => $row ) {
					$cell_type = 'td';
					if ( $i <= 0 ) {
						$cell_type = 'th';
					}

					print( '<tr>' );
					foreach ( $row as $key => $cell ) {
						if ( 'steward' === $key && empty( trim( $cell ) ) ) {
							if ( ! empty( $row['steward_email'] ) ) {
								$user = get_user_by( 'email', $row['steward_email'] );
								if ( is_a( $user, '\WP_User' ) ) {
									$cell = $user->ID;
								}
							} else if ( ! empty( $row['steward_username'] ) ) {
								$user = get_user_by( 'login', $row['steward_username'] );
								if ( is_a( $user, '\WP_User' ) ) {
									$cell = $user->ID;
								}
							}
						}

						printf( '<%s data-key="%s">', $cell_type, $key );
						print( nl2br( $cell ) );
						/*var_dump( $cell );*/
						printf( '</%s>', $cell_type );
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

				$headers = array_shift( $this->data );
				if ( empty( $this->data ) ) {
					return new WP_Error( 'import', __( 'The import file does not appear to include any data', 'cornell/governance' ) );
				}

				foreach ( $this->data as $row ) {
					$post = get_post( $row['page_id'] );
					if ( is_a( $post, 'WP_Post' ) ) {
						$post_id         = $post->ID;
						$governance_meta = get_post_meta( $post_id, 'cornell/governance/information', true );
						if ( ! is_array( $governance_meta ) ) {
							echo Import_Export::instance()->generate_info_message( 'import', 'There did not appear to be existing governance info for ' . $post_id . ' before we ran the import file.' );
							$governance_meta = array();
						}

						$governance_meta['goals']              = $row['goals'];
						$governance_meta['primary-audience']   = $row['primary-audience'];
						$governance_meta['secondary-audience'] = $row['secondary-audience'];
						$governance_meta['problem']            = $row['problem'];
						$governance_meta['review-cycle']       = $row['review-cycle'];
						$governance_meta['supervisor']         = $row['supervisor'];
						$governance_meta['liaison']            = $row['liaison'];
						$governance_meta['tasks']              = array_filter( preg_split( '/\r\n|\r|\n/', $row['tasks'] ) );
						$governance_meta['completed-tasks']    = array();
						if ( ! empty( $row['last-reviewed'] ) ) {
							$governance_meta['last-review'] = strtotime( $row['last-reviewed'] );
						} else {
							$governance_meta['last-review'] = null;
						}
						if ( ! array_key_exists( 'timestamp', $governance_meta ) ) {
							$governance_meta['timestamp'] = date( 'Y-m-d H:i:s' );
						}
						if ( ! array_key_exists( 'initial-setup', $governance_meta ) ) {
							$governance_meta['initial-setup'] = array(
								'time' => time(),
								'user' => get_current_user_id(),
							);
						}

						update_post_meta( $post_id, 'cornell/governance/information', $governance_meta );

						$notes = get_post_meta( 'cornell/governance/notes', $post_id, true );
						if ( is_array( $notes ) && array_key_exists( 'notes', $notes ) ) {
							if ( $notes['notes'] != $row['notes'] ) {
								$notes['notes']     = $row['notes'];
								$notes['timestamp'] = date( "Y-m-d h:i:s" );

								update_post_meta( $post_id, 'cornell/governance/notes', $notes );
							}
						} else {
							$notes = array(
								'notes'     => $row['notes'],
								'timestamp' => date( "Y-m-d h:i:s" ),
							);

							update_post_meta( $post_id, 'cornell/governance/notes', $notes );
						}

						if ( ! empty( $row['steward'] ) && ( intval( $row['steward'] ) !== intval( $post->post_author ) ) ) {
							$post->post_author = intval( $row['steward'] );
							wp_update_post( $post );
						} else if ( ! empty( $row['steward_email'] ) ) {
							$user = get_user_by( 'email', $row['steward_email'] );
							if (  is_a( $user, '\WP_User' ) ) {
								$post->post_author = $user->ID;
							}
							wp_update_post( $post );
						} else if ( ! empty( $row['steward_username'] ) ) {
							$user = get_user_by( 'login', $row['steward_username'] );
							if ( is_a( $user, '\WP_User' ) ) {
								$post->post_author = $user->ID;
							}
							wp_update_post( $post );
						}
					}
				}

				array_unshift( $this->data, $headers );

				return true;
			}
		}
	}
}
