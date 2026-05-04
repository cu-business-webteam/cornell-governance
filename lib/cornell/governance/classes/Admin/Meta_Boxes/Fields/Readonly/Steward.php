<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Message;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly\Steward' ) ) {
		Final class Steward extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Steward {
			/**
			 * @var Steward $instance holds the single instance of this class
			 * @access private
			 */
			protected static Steward $instance;

			function __construct() {
				parent::__construct();

				$this->id = 'cornell-governance-page-info-steward-readonly';

				$this->is_readonly = true;
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Steward
			 * @since   0.1
			 */
			public static function instance(): Steward {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}