<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\Templates {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Tertiary_Prompt' ) ) {
		class Tertiary_Prompt extends Base {
			/**
			 * @var Tertiary_Prompt $instance holds the single instance of this class
			 * @access private
			 */
			private static Tertiary_Prompt $instance;

			/**
			 * Creates the Template object
			 *
			 * @param array $template_data *
			 *
			 * @access protected
			 * @since  0.1
			 */
			protected function __construct( array $template_data ) {
				$this->template_data = $template_data;
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @param array $template_data the data to be processed
			 *
			 * @access  public
			 * @return  Tertiary_Prompt
			 * @since   0.1
			 */
			public static function instance( array $template_data = array() ): Tertiary_Prompt {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className( $template_data );
				} else {
					self::$instance->template_data = $template_data;
				}

				return self::$instance;
			}


			/**
			 * @inheritDoc
			 */
			protected function get_template_vars(): array {
				$template_vars = $this->get_prompt_template_vars();
				$template_vars['review_time'] = get_option( 'cornell-governance-tertiary-prompt-time', 7 );

				return apply_filters( 'cornell/governance/emails/report-data', $template_vars, get_class( $this ) );
			}
		}
	}
}