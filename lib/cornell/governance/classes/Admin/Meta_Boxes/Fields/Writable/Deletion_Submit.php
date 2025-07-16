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

	if ( ! class_exists( 'Deletion_Submit' ) ) {
		class Deletion_Submit extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Deletion_Submit {
			/**
			 * @var Deletion_Submit $instance holds the single instance of this class
			 * @access private
			 */
			protected static Deletion_Submit $instance;

			function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Deletion_Submit
			 * @since   0.1
			 */
			public static function instance(): Deletion_Submit {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}