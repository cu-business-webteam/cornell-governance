<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\Liaison {

	if ( ! class_exists( 'Due' ) ) {
		class Due extends \Cornell\Governance\Emails\Due {
			/**
			 * @var Due $instance holds the single instance of this class
			 * @access private
			 */
			protected static Due $instance;

			/**
			 * Construct our object
			 */
			protected function __construct() {
				parent::__construct();

				$this->set_vars( array(
					'subject' => __( '[Liaison Report] [DUE TODAY] These pages require immediate review', 'cornell/governance' ),
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Due
			 * @since   0.1
			 */
			public static function instance(): Due {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

		}
	}
}