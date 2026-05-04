<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Button;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly\Deletion_Reason' ) ) {
		class Deletion_Reason extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Deletion_Reason {
			/**
			 * @var Deletion_Reason $instance holds the single instance of this class
			 * @access private
			 */
			protected static Deletion_Reason $instance;

			function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Deletion_Reason
			 * @since   0.1
			 */
			public static function instance(): Deletion_Reason {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}