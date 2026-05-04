<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Tooltip;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly\Steward_Tooltip' ) ) {
		Final class Steward_Tooltip extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Steward_Tooltip {
			/**
			 * @var Steward_Tooltip $instance holds the single instance of this class
			 * @access private
			 */
			protected static Steward_Tooltip $instance;

			function __construct() {
				parent::__construct();

				$this->id = 'cornell-governance-page-info-steward-tooltip-readonly';

				$this->is_readonly = true;
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Steward_Tooltip
			 * @since   0.1
			 */
			public static function instance(): Steward_Tooltip {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}