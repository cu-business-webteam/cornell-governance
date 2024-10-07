<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Confirm;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Mark_For_Deletion' ) ) {
		class Mark_For_Deletion extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Mark_For_Deletion {
			/**
			 * @var Mark_For_Deletion $instance holds the single instance of this class
			 * @access private
			 */
			protected static Mark_For_Deletion $instance;

			function __construct() {
				parent::__construct();

				$this->id = 'cornell-governance-page-info-mark-for-deletion-readonly';
				$this->is_readonly = true;
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Mark_For_Deletion
			 * @since   0.1
			 */
			public static function instance(): Mark_For_Deletion {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}