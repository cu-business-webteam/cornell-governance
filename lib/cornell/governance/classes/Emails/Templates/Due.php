<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\Templates {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Due' ) ) {
		class Due extends Base {
			/**
			 * @var Due $instance holds the single instance of this class
			 * @access private
			 */
			private static Due $instance;

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
			 * @return  Due
			 * @since   0.1
			 */
			public static function instance( array $template_data = array() ): Due {
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
				return apply_filters( 'cornell/governance/emails/report-data', $this->get_prompt_template_vars(), get_class( $this ) );
			}
		}
	}
}