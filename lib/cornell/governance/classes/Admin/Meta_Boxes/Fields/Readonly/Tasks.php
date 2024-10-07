<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly {

	use Cornell\Governance\Admin\Fields\Default_Tasks;
	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Repeater;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Tasks' ) ) {
		Final class Tasks extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Tasks {
			/**
			 * @var Tasks $instance holds the single instance of this class
			 * @access private
			 */
			protected static Tasks $instance;

			function __construct() {
				parent::__construct();

				$this->id = 'cornell-governance-page-info-tasks-readonly';

				$this->is_readonly = true;
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Tasks
			 * @since   0.1
			 */
			public static function instance(): Tasks {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}