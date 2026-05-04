<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Writable {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Select;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Writable\Liaison' ) ) {
		Final class Liaison extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Liaison {
			/**
			 * @var Liaison $instance holds the single instance of this class
			 * @access private
			 */
			protected static Liaison $instance;

			function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Liaison
			 * @since   0.1
			 */
			public static function instance(): Liaison {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}