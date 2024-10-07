<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Input;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Supervisor' ) ) {
		Final class Supervisor extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Supervisor {
			/**
			 * @var Supervisor $instance holds the single instance of this class
			 * @access private
			 */
			protected static Supervisor $instance;

			function __construct() {
				parent::__construct();

				$this->id = 'cornell-governance-page-info-supervisor-readonly';

				$this->is_readonly = true;
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Supervisor
			 * @since   0.1
			 */
			public static function instance(): Supervisor {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}