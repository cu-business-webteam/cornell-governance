<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Message;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Completed_Review_Instructions' ) ) {
		class Completed_Review_Instructions extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Completed_Review_Instructions {
			/**
			 * @var Completed_Review_Instructions $instance holds the single instance of this class
			 * @access private
			 */
			protected static Completed_Review_Instructions $instance;

			function __construct() {
				parent::__construct();

				$this->id = 'cornell-governance-page-info-completed-review-instructions-readonly';
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Completed_Review_Instructions
			 * @since   0.1
			 */
			public static function instance(): Completed_Review_Instructions {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}