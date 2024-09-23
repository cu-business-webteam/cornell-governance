<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\General {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Overdue' ) ) {
		class Overdue extends \Cornell\Governance\Emails\Overdue {
			/**
			 * @var Overdue $instance holds the single instance of this class
			 * @access private
			 */
			protected static Overdue $instance;

			/**
			 * Construct our object
			 */
			protected function __construct() {
				parent::__construct();

				$this->set_vars( array(
					'subject' => __( '[OVERDUE] These pages require your immediate review', 'cornell/governance' ),
					'headers' => array(
						'Priority: Urgent',
						'X-Priority: 1 (Highest)',
						'X-MSMail-Priority: High',
						'Importance: High',
					),
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Overdue
			 * @since   0.1
			 */
			public static function instance(): Overdue {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}