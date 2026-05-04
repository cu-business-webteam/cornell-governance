<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Writable {

	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Writable\Notes_Notes' ) ) {
		class Notes_Notes extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Notes_Notes {
			/**
			 * @var Notes_Notes $instance holds the single instance of this class
			 * @access private
			 */
			protected static Notes_Notes $instance;

			function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Notes_Notes
			 * @since   0.1
			 */
			public static function instance(): Notes_Notes {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}