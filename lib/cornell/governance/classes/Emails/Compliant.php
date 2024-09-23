<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Compliant' ) ) {
		class Compliant extends Prompt {
			/**
			 * @var array $template_vars the template variables to be sent to the template instance
			 * @access protected
			 */
			protected array $template_vars=array();

			/**
			 * Construct our object
			 */
			protected function __construct() {
				if ( ! isset( $GLOBALS['cg_reviewed_page_title'] ) ) {
					return;
				}

				parent::__construct();

				$this->set_vars( array(
					'subject' => __( '[COMPLETE] Page review successful', 'cornell/governance' ),
				) );
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
					Helpers::log( 'An appropriate email template class could not be located' );
					return '';
				}

				Helpers::log( 'Beginning to build an email from the template class of ' . $template_class, 'info' );

				$template = $template_class::instance( $this->template_vars );

				Helpers::log( 'Set the template vars to look like: ' . print_r( $this->template_vars, true ), 'info' );

				return $template->get_email_content();
			}

			/**
			 * Set our template variables specific to this email prompt
			 *
			 * @param array $vars the array of variables
			 *
			 * @access public
			 * @since  0.4.4
			 * @return void
			 */
			public function set_template_vars( array $vars ) {
				$this->template_vars = $vars;

				if ( array_key_exists( 'post_title', $vars['post'] ) ) {
					$this->set_vars( array(
						'subject' => sprintf( __( '[COMPLETE] Page review successful - %s', 'cornell/governance' ), $vars['post']['post_title'] ),
					) );
				}
			}
		}
	}
}