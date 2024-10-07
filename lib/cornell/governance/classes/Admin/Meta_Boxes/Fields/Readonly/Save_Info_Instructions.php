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

	if ( ! class_exists( 'Save_Info_Instructions' ) ) {
		class Save_Info_Instructions extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Save_Info_Instructions {
			/**
			 * @var Save_Info_Instructions $instance holds the single instance of this class
			 * @access private
			 */
			protected static Save_Info_Instructions $instance;

			function __construct() {
				parent::__construct();

				$this->id = 'cornell-governance-page-info-save-instructions-readonly';
				$this->is_readonly = true;
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Save_Info_Instructions
			 * @since   0.1
			 */
			public static function instance(): Save_Info_Instructions {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}