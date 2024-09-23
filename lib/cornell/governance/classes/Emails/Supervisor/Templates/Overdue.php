<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\Supervisor\Templates {

	if ( ! class_exists( 'Overdue' ) ) {
		class Overdue extends \Cornell\Governance\Emails\Templates\Overdue {
			/**
			 * @var Overdue $instance holds the single instance of this class
			 * @access private
			 */
			private static Overdue $instance;

			/**
			 * Returns the instance of this class.
			 *
			 * @param array $template_data the data to be processed
			 *
			 * @access  public
			 * @return  Overdue
			 * @since   0.1
			 */
			public static function instance( array $template_data = array() ): Overdue {
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