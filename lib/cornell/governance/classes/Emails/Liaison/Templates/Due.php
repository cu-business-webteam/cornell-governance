<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\Liaison\Templates {

	if ( ! class_exists( 'Due' ) ) {
		class Due extends \Cornell\Governance\Emails\Templates\Due {
			/**
			 * @var Due $instance holds the single instance of this class
			 * @access private
			 */
			private static Due $instance;

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
		}
	}
}