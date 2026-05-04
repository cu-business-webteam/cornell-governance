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

	if ( ! class_exists( '\Cornell\Governance\Admin\Import_Export\Export' ) ) {
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

				$this->data[] = $this->headers;

				$meta_query = array(
					'relation' => 'OR',
					array(
						'key'     => 'cornell/governance/information',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => 'cornell/governance/notes',
						'compare' => 'EXISTS',
					)
				);

				$posts = get_posts( array(
					'posts_per_page' => - 1,
					'post_type'      => Plugin::instance()->get_post_types(),
					'meta_query'     => $meta_query,
				) );

				$defaults = $this->get_default_meta();

				foreach ( $posts as $post ) {
					$postmeta = get_post_meta( $post->ID, 'cornell/governance/information', true );
					if ( ! is_array( $postmeta ) ) {
						Helpers::log( 'The Governance Post Meta for ' . $post->ID . ' does not return an array. It looks like: ' . print_r( $postmeta, true ), 'warning' );
						continue;
					}
					$meta  = array_merge( $defaults, $postmeta );
					$notes = get_post_meta( $post->ID, 'cornell/governance/notes', true );
					if ( ! is_array( $notes ) ) {
						$notes = array( 'notes' => '' );
					}

					$author = get_user( $post->post_author );

					$this->data[$post->ID] = apply_filters( 'cornell/governance/import-export/data', array(
						$post->ID,
						$post->post_title,
						get_permalink( $post->ID ),
						$meta['goals'],
						$meta['problem'],
						$meta['primary-audience'],
						$meta['secondary-audience'],
						date( 'c', $meta['last-review'] ),
						$meta['review-cycle'],
						implode( PHP_EOL, $meta['tasks'] ),
						$post->post_author,
						$author->user_email,
						$author->user_login,
						$meta['supervisor'],
						$meta['liaison'],
						$notes['notes'],
					) );

					$this->data[$post->ID] = array_combine( array_keys( $this->headers ), $this->data[$post->ID] );
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
				return apply_filters( 'cornell/governance/import-export/data-defaults', array(
					'goals'              => '',
					'problem'            => '',
					'primary-audience'   => '',
					'secondary-audience' => '',
					'last-review'        => time(),
					'review-cycle'       => '',
					'tasks'              => array(),
					'supervisor'         => '',
					'liaison'            => '',
				) );
			}

			/**
			 * Generate the spreadsheet file
			 *
			 * @access public
			 * @return void
			 * @since  0.6.2
			 */
			public function get_file() {
				$ext        = 'csv';
				$filename   = 'governance-export-' . date( "Y-m-d_H:i:s" ) . '.' . $ext;

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
			 * @since  0.6.2
			 * @return void
			 */
			public function get_file_json() {
				$ext        = 'json';
				$filename   = 'governance-export-' . date( "Y-m-d_H:i:s" ) . '.' . $ext;

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
