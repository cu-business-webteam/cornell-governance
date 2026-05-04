<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Writable {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Radio_Group;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Writable\Review_Cycle' ) ) {
		Final class Review_Cycle extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Review_Cycle {
			/**
			 * @var Review_Cycle $instance holds the single instance of this class
			 * @access private
			 */
			protected static Review_Cycle $instance;

			function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Review_Cycle
			 * @since   0.1
			 */
			public static function instance(): Review_Cycle {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}