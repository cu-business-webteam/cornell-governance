<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Writable {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Button;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Writable\Save_Info' ) ) {
		class Save_Info extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Save_Info {
			/**
			 * @var Save_Info $instance holds the single instance of this class
			 * @access private
			 */
			protected static Save_Info $instance;

			function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Save_Info
			 * @since   0.1
			 */
			public static function instance(): Save_Info {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}