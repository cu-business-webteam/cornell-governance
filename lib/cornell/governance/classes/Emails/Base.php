<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails {
	if ( ! class_exists( 'Base' ) ) {
		abstract class Base {
			/**
			 * Our headers array
			 */
			protected array $headers = array();
			/**
			 * Our to address
			 */
			protected string $to = '';
			/**
			 * Our email subject
			 */
			protected string $subject = '';
			/**
			 * Our email body
			 */
			protected string $body = '';
			/**
			 * Holds a list of the pages to be listed in this email message (if this email includes a list of pages)
			 */
			protected array $pages = array();

			/**
			 * Construct our object
			 */
			protected function __construct() {
				$this->set_headers( array( 'From: Website Governance <no-reply@cornell.edu>' ) );
			}

			/**
			 * Gather the headers for the email message
			 */
			abstract protected function get_headers();

			/**
			 * Set the vars we need for the email headers
			 *
			 * @param array $atts the array of headers to set
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function set_headers( array $atts ) {
				$this->headers = array_merge( $this->headers, $atts );
			}

			/**
			 * Build the recipient email address(es)
			 */
			abstract protected function get_email_to();

			/**
			 * Set the to header for the email message
			 *
			 * @param string|array $to the value of the to header
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function set_email_to( $to ) {
				if ( is_email( CORNELL_GOVERNANCE_EMAIL_TO ) && CORNELL_DEBUG && ! isset( $_GET['cornell/governance/debug'] ) ) {
					$this->to = is_email( CORNELL_GOVERNANCE_EMAIL_TO );

					return;
				}

				$this->to = $to;
			}

			/**
			 * Build the email subject
			 */
			abstract protected function get_email_subject();

			/**
			 * Set the value of the subject header
			 *
			 * @param string $subject the subject to use on the email message
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function set_email_subject( string $subject ) {
				$this->subject = $subject;
			}

			/**
			 * Sets the array of pages to be included in this message
			 *
			 * @param array $pages the array of pages
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function set_pages( array $pages ) {
				$this->pages = $pages;
			}

			/**
			 * Build the body of our HTML email if a template was not located
			 */
			abstract protected function get_default_html_body();

			/**
			 * Build the body of our Plain Text email
			 *
			 * @access protected
			 * @return string the plain-text email message body
			 * @since  0.1
			 */
			protected function get_text_body(): string {
				return wp_strip_all_tags( $this->get_html_body() );
			}

			/**
			 * Send the email message
			 *
			 * @return bool whether the system successfully processed the message
			 */
			protected function send(): bool {
				if ( isset( $_GET['cornell/governance/debug'] ) ) {
					echo '<p>' . __( 'Preparing to output email debug information rather than actually sending messages', 'cornell/governance' ) . '</p>';

					return $this->fake_send();
				}

				add_filter( 'wp_mail_from', array( $this, 'get_email_from' ), 99 );
				add_filter( 'wp_mail_from_name', array( $this, 'get_email_from_name' ), 99 );

				$this->get_debug_cc();

				$sent = wp_mail( $this->get_email_to(), '[' . get_option( 'blogname' ) . '] ' . $this->get_email_subject(), $this->get_html_body(), $this->get_headers() );

				remove_filter( 'wp_mail_from', array( $this, 'get_email_from' ), 99 );
				remove_filter( 'wp_mail_from_name', array( $this, 'get_email_from_name' ), 99 );

				return $sent;
			}

			/**
			 * Add any necessary CC or BCC addresses before sending a message
			 *
			 * @access protected
			 * @return void
			 * @since  0.4.6
			 */
			protected function get_debug_cc() {
				$headers = $this->headers;
				$bcc     = array();
				$cc      = array();

				foreach ( $headers as $header ) {
					if ( stristr( 'bcc:', $header ) ) {
						$bcc = explode( ':', $header );
						$bcc = explode( ',', $bcc[1] );
					} else if ( stristr( 'cc:', $header ) ) {
						$cc = explode( ':', $header );
						$cc = explode( ',', $cc[1] );
					}
				}

				if ( defined( 'CORNELL_GOVERNANCE_EMAIL_CC' ) ) {
					$cclist = explode( ',', CORNELL_GOVERNANCE_EMAIL_CC );
					foreach ( $cclist as $address ) {
						if ( is_email( trim( $address ) ) ) {
							$cc[] = is_email( trim( $address ) );
						}
					}

					$cc = array_unique( $cc );
				}

				if ( defined( 'CORNELL_GOVERNANCE_EMAIL_BCC' ) ) {
					$cclist = explode( ',', CORNELL_GOVERNANCE_EMAIL_BCC );
					foreach ( $cclist as $address ) {
						if ( is_email( trim( $address ) ) ) {
							$bcc[] = is_email( trim( $address ) );
						}
					}

					$bcc = array_unique( $bcc );
				}

				$this->set_headers( array(
					'Cc: ' . implode( ', ', $cc ),
					'Bcc: ' . implode( ', ', $bcc )
				) );
			}

			/**
			 * Output HTML representing what should have been sent
			 *
			 * @access protected
			 * @return bool
			 * @since  0.4.0
			 */
			protected function fake_send(): bool {
				$from_email = apply_filters( 'wp_mail_from', $this->get_email_from( '' ) );
				$from_name  = apply_filters( 'wp_mail_from_name', $this->get_email_from_name( '' ) );

				echo '<div class="cornell-governance-email" style="margin: 2rem; padding: 1rem; border: 1px solid #000;">';

				echo '<div class="cornell-governance-email-head">';
				echo '<p class="email-to"><strong>To: </strong>' . str_replace( array( '<', '>' ), array(
						'&lt;',
						'&gt;'
					), $this->get_email_to() ) . '</p>';
				echo '<p class="email-from"><strong>From: </strong>' . $from_name . ' &lt;' . $from_email . '&gt;</p>';
				echo '<p class="email-subject"><strong>Subject: </strong>' . $this->get_email_subject() . '</p>';
				echo '<pre><code>';
				var_dump( $this->pages );
				echo '</code></pre>';
				echo '</div>';

				echo '<div class="cornell-governance-email-body">';
				echo $this->get_html_body();
				echo '</div>';

				echo '</div>';

				return true;
			}

			/**
			 * Public accessor for the send method
			 *
			 * @access public
			 * @return bool whether the email was sent successfully
			 * @since  2025.06.27
			 */
			public function send_mail(): bool {
				return $this->send();
			}

			/**
			 * Get and return the email address from which these messages should be sent
			 *
			 * @param string $email the existing from email address
			 *
			 * @access public
			 * @return string the updated email address
			 * @since  2024.06.26
			 */
			public function get_email_from( string $email ): string {
				return 'no-reply@cornell.edu';
			}

			/**
			 * Get and return the email name from which these messages should be sent
			 *
			 * @param string $email the existing from email name
			 *
			 * @access public
			 * @return string the updated email name
			 * @since  2024.06.26
			 */
			public function get_email_from_name( string $email ): string {
				return __( 'Website Governance', 'cornell/governance' );
			}

			/**
			 * Set our class properties
			 *
			 * @param array $vars the variables to set
			 *
			 * @access public
			 * @return void
			 * @since  2024.06.25
			 */
			public function set_vars( array $vars ) {
				$keys = array(
					'headers',
					'subject',
					'to',
					'from',
					'body',
				);

				foreach ( $keys as $key ) {
					if ( array_key_exists( $key, $vars ) ) {
						$this->{$key} = $vars[ $key ];
					}
				}
			}


			/**
			 * Add the plain-text body to the email message
			 *
			 * @param \PHPMailer $phpmailer the PHPMailer object being prepared
			 *
			 * @access public
			 * @return void
			 * @since  2024.06.25
			 */
			public function add_plain_text_body( \PHPMailer $phpmailer ) {
				// don't run if sending plain text email already
				// don't run if altbody is set
				if ( 'text/plain' === $phpmailer->ContentType || ! empty( $phpmailer->AltBody ) ) {
					return;
				}

				$phpmailer->AltBody = $this->get_text_body();
			}

			/**
			 * Retrieve the templated HTML body of the email
			 *
			 * @access protected
			 * @return string the HTML body
			 * @since  2024.06.26
			 */
			protected function get_templated_html_body(): string {
				$template_class = $this->locate_template();
				if ( empty( $template_class ) ) {
					return '';
				}

				$this->pages['recipient'] = $this->get_email_to();

				return $template_class::instance( $this->pages )->get_email_content();
			}

			/**
			 * Retrieve and return the HTML body of this email message
			 *      Start by looking for a templated version of the body,
			 *      then fall back to a default if the template is not found
			 *
			 * @access protected
			 * @return string the HTML body
			 * @since  2024.06.26
			 */
			protected function get_html_body(): string {
				$body = $this->get_templated_html_body();
				if ( ! empty( $body ) ) {
					return $body;
				}

				return $this->get_default_html_body();
			}

			/**
			 * Attempt to locate the HTML template file for this email
			 *
			 * @access protected
			 * @return string the template class name
			 * @since  2024.06.26
			 */
			protected function locate_template(): string {
				// Fully qualified class name
				$classname = get_called_class();
				// Split into parts
				$classparts = explode( '\\', $classname );
				// Get the class name without the namespace
				$class = end( $classparts );
				// Get the last part of the namespace
				$last = prev( $classparts );

				// Check for a template class at the same level as this email class
				$template_class = str_replace( $last, $last . '\Templates', $classname );
				if ( class_exists( $template_class ) ) {
					return $template_class;
				}

				// If that didn't exist, check for a template class at the base email level
				$template_class = str_replace( $last, 'Templates', $classname );
				if ( class_exists( $template_class ) ) {
					return $template_class;
				}

				// If that didn't exist, return a blank string
				return '';
			}
		}
	}
}