<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\Liaison\Templates {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Tertiary_Prompt' ) ) {
		class Tertiary_Prompt extends \Cornell\Governance\Emails\Templates\Tertiary_Prompt {
			/**
			 * @var Tertiary_Prompt $instance holds the single instance of this class
			 * @access private
			 */
			private static Tertiary_Prompt $instance;

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
		}
	}
}