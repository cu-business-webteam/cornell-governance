<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\General {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Deletion' ) ) {
		class Deletion extends \Cornell\Governance\Emails\Deletion {
			/**
			 * @var Deletion $instance holds the single instance of this class
			 * @access private
			 */
			protected static Deletion $instance;

			/**
			 * Construct our object
			 */
			protected function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Deletion
			 * @since   0.1
			 */
			public static function instance(): Deletion {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}