<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\Templates {

	use Cornell\Governance\Helpers;
	use Handlebars\Handlebars;

	if ( ! class_exists( 'Base' ) ) {
		abstract class Base {
			/**
			 * @protected array $template_data the data to be processed for the email template
			 */
			protected array $template_data = array();

			/**
			 * Construct our object
			 *
			 * @param array $template_data the data to be processed for the email template
			 */
			abstract protected function __construct( array $template_data );

			/**
			 * Retrieve and return an associative array of template variables
			 *
			 * @access protected
			 * @return array the template variables
			 * @since  0.1
			 */
			abstract protected function get_template_vars(): array;

			/**
			 * Retrieve the template for this email message
			 *
			 * @access protected
			 * @return string the template content
			 * @since  0.1
			 */
			protected function get_template(): string {
				// If we find a template file, this is where we'll store it
				$file = '';

				$class_name    = get_called_class();
				$class_parts   = explode( '\\', $class_name );

				$template      = array_pop( $class_parts );
				$template_file = $template . '.handlebars';

				// Slice off the Cornell and Governance portions of the namespace
				$class_parts = array_slice( $class_parts, 2 );
				// Attempt to grab the audience name
				$audience    = $class_parts[1];

				// Back out to the root classes directory
				$template_path = dirname( __FILE__, 3 );

				// Look for the appropriate template file in the theme
				$test_local_file_name = get_stylesheet_directory() . '/cornell-governance/templates/' . trailingslashit( $audience ) . $template_file;
				// Look for the appropriate template file in this plugin
				$test_file_name = trailingslashit( $template_path ) . trailingslashit( implode( '/', $class_parts ) ) . $template_file;
				// Look for the appropriate base template file (not specific to any audience) in the theme
				$test_local_base_file_name = get_stylesheet_directory() . '/cornell-governance/templates/' . $template_file;
				// Look for the appropriate base template file (not specific to any audience) in this plugin
				$test_base_file_name = str_replace( trailingslashit( $audience ), '', $test_file_name );

				if ( file_exists( $test_local_file_name ) ) {
					$file = $test_local_file_name;
				} else if ( file_exists( $test_file_name ) ) {
					$file = $test_file_name;
				} else if ( file_exists( $test_local_base_file_name ) ) {
					$file = $test_local_base_file_name;
				} else if ( file_exists( $test_base_file_name ) ) {
					$file = $test_base_file_name;
				}

				if ( empty( $file ) ) {
					return '';
				}

				ob_start();
				include $file;

				return ob_get_clean();
			}

			/**
			 * Retrieve and return the template with the variables replaced
			 *
			 * @access public
			 * @return string the ready-to-send email template
			 * @since  0.1
			 */
			public function get_email_content(): string {
				$template = $this->get_template();
				$vars     = $this->get_template_vars();

				$partialsDir    = __DIR__;
				$partialsLoader = new \Handlebars\Loader\FilesystemLoader(
					$partialsDir,
					[
						"extension" => 'handlebars',
					]
				);

				$handlebars = new \Handlebars\Handlebars();

				return $handlebars->render( $template, $vars );
			}

			/**
			 * Set the current user ID, so that report data will be limited to that user
			 *
			 * @param int $user the current value of the user ID (most likely 0)
			 *
			 * @access protected
			 * @return int the user ID
			 * @since  0.1
			 */
			protected function current_user( int $user ): int {
				return get_current_user_id();
			}

			/**
			 * Retrieve the data for a report to be included in a Prompt email
			 *
			 * @access protected
			 * @return array the array of report data
			 * @since  0.1
			 */
			protected function get_prompt_template_vars(): array {
				$report_data = $this->template_data;

				if ( empty( $report_data ) ) {
					return array();
				}

				if ( array_key_exists( 'recipient', $report_data ) ) {
					$recipient = preg_replace( '/<(.*?)>/', '', $report_data['recipient'] );
					unset( $report_data['recipient'] );
				} else {
					$recipient = '';
				}

				$data = array();
				foreach ( $report_data as $ID => $due ) {
					$meta        = get_post_meta( $ID, 'cornell/governance/information', true );
					$last_review = array_key_exists( 'last-review', $meta ) ? Helpers::format_date( $meta['last-review'] ) : 'N/A';
					$post = get_post( $ID );
					$edit_link = Helpers::get_edit_post_link( $ID, false );

					$data[ $ID ]                    = $post;
					$data[ $ID ]->permalink         = get_permalink( $post );
					$data[ $ID ]->due_date          = Helpers::format_date( $due );
					$data[ $ID ]->last_reviewed     = $last_review;
					$data[ $ID ]->edit_link         = $edit_link;
					$data[ $ID ]->edit_link_encoded = urlencode( $edit_link );
					$data[ $ID ]->author_email      = get_user_by( 'ID', $data[ $ID ]->post_author )->user_email;
					$data[ $ID ]->supervisor_email  = $meta['supervisor'];
				}

				$fmt = new \NumberFormatter( 'en_US', \NumberFormatter::SPELLOUT );

				$prompt_times = array(
					'initial'   => get_option( 'cornell-governance-initial-prompt-time', 60 ),
					'secondary' => get_option( 'cornell-governance-secondary-prompt-time', 30 ),
					'tertiary'  => get_option( 'cornell-governance-tertiary-prompt-time', 7 ),
				);

				$prompt_words = array(
					'initial'      => $fmt->format( $prompt_times['initial'] ),
					'secondary'    => $fmt->format( $prompt_times['secondary'] ),
					'tertiary'     => $fmt->format( $prompt_times['tertiary'] ),
					'initial-uc'   => ucfirst( $fmt->format( $prompt_times['initial'] ) ),
					'secondary-uc' => ucfirst( $fmt->format( $prompt_times['secondary'] ) ),
					'tertiary-uc'  => ucfirst( $fmt->format( $prompt_times['tertiary'] ) ),
				);

				return apply_filters( 'cornell/governance/emails/report-data', array(
					'site_name'       => get_option( 'blogname' ),
					'user'            => $recipient,
					'report'          => $data,
					'managing-office' => get_option( 'cornell-governance-managing-office', __( 'MarCom', 'cornell/governance' ) ),
					'prompt-times'    => $prompt_times,
					'prompt-words'    => $prompt_words,
				), get_class( $this ) );
			}
		}
	}
}