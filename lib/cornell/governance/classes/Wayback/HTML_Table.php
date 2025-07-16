<?php
/**
 * Provides a helper class to build out an HTML table
 */
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Wayback {
	if ( ! class_exists( 'HTML_Table' ) ) {
		class HTML_Table {
			/**
			 * @var HTML_Table $instance holds the single instance of this class
			 * @access private
			 */
			private static HTML_Table $instance;

			/**
			 * Construct our HTML_Table object
			 *
			 * @access private
			 * @since  0.6.2
			 * @return void
			 */
			private function __construct() {
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  HTML_Table
			 * @since   0.1
			 */
			public static function instance(): HTML_Table {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Opens the HTML table
			 *
			 * @param string $caption the table caption text
			 * @param array $classes the CSS classes to apply to the table
			 *
			 * @access public
			 * @since  0.6.2
			 * @return string the opening tag for the HTML table
			 */
			public function open( string $caption = '', array $classes = array() ): string {
				$classes[] = 'cornell-governance-html-table';
				$output    = sprintf( '<table class="%s">', implode( ' ', $classes ) );
				if ( ! empty( $caption ) ) {
					$output .= sprintf( '<caption>%s</caption>', $caption );
				}

				return $output;
			}

			/**
			 * Open the main body section of the table
			 *
			 * @access public
			 * @since  0.6.2
			 * @return string the opening tbody tag
			 */
			public function open_body(): string {
				return '<tbody>';
			}

			/**
			 * Close the main body section of the table
			 *
			 * @access public
			 * @since  0.6.2
			 * @return string the closing tbody tag
			 */
			public function close_body(): string {
				return '</tbody>';
			}

			/**
			 * Close the table itself
			 *
			 * @access public
			 * @since  0.6.2
			 * @return string the closing table tag
			 */
			public function close(): string {
				return '</table>';
			}

			/**
			 * Builds and returns a single table row
			 *
			 * @param array $data the data being included in the row
			 * @param string $context whether this is a header row, footer row, or body row
			 *
			 * @access public
			 * @since  0.6.2
			 * @return string the row HTML
			 */
			public function get_row( array $data, string $context = 'body' ): string {
				if ( 'header' === $context ) {
					return $this->get_thead( $data );
				} else if ( 'footer' === $context ) {
					return $this->get_tfoot( $data );
				}

				$output = '<tr>';
				foreach ( $data as $cell ) {
					$output .= sprintf( '<td>%s</td>', $cell );
				}
				$output .= '</tr>';

				return $output;
			}

			/**
			 * Builds and returns the thead section of the table
			 *
			 * @param array $headers the header data to be included
			 *
			 * @access private
			 * @since  0.6.2
			 * @return string the thead HTML
			 */
			private function get_thead( array $headers ): string {
				$output = '<thead>';
				$output .= '<tr>';
				foreach ( $headers as $header ) {
					$output .= sprintf( '<th scope="col">%s</th>', $header );
				}
				$output .= '</tr>';
				$output .= '</thead>';

				return $output;
			}

			/**
			 * Builds and returns the tfoot section of the table
			 *
			 * @param array $headers the header data to be included
			 *
			 * @access private
			 * @since  0.6.2
			 * @return string the thead HTML
			 */
			private function get_tfoot( array $headers ): string {
				$output = '<tfoot>';
				$output .= '<tr>';
				foreach ( $headers as $header ) {
					$output .= sprintf( '<th scope="col">%s</th>', $header );
				}
				$output .= '</tr>';
				$output .= '</tfoot>';

				return $output;
			}
		}
	}
}