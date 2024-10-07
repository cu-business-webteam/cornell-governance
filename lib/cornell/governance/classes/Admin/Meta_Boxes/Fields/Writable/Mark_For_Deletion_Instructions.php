<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Writable {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Message;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Mark_For_Deletion_Instructions' ) ) {
		class Mark_For_Deletion_Instructions extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Mark_For_Deletion_Instructions {
			/**
			 * @var Mark_For_Deletion_Instructions $instance holds the single instance of this class
			 * @access private
			 */
			protected static Mark_For_Deletion_Instructions $instance;

			function __construct() {
				parent::__construct();

				$this->id = 'cornell-governance-page-info-mark-for-deletion-instructions';
				$this->is_readonly = false;
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Mark_For_Deletion_Instructions
			 * @since   0.1
			 */
			public static function instance(): Mark_For_Deletion_Instructions {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}