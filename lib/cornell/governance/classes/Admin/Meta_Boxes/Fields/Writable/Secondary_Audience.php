<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Writable {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Select;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Writable\Secondary_Audience' ) ) {
		Final class Secondary_Audience extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Secondary_Audience {
			/**
			 * @var Secondary_Audience $instance holds the single instance of this class
			 * @access private
			 */
			protected static Secondary_Audience $instance;

			function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Secondary_Audience
			 * @since   0.1
			 */
			public static function instance(): Secondary_Audience {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}