<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Writable {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Confirm;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Completed_Review' ) ) {
		class Completed_Review extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Completed_Review {
			/**
			 * @var Completed_Review $instance holds the single instance of this class
			 * @access private
			 */
			protected static Completed_Review $instance;

			function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Completed_Review
			 * @since   0.1
			 */
			public static function instance(): Completed_Review {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}