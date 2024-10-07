<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Textarea;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Problem' ) ) {
		Final class Problem extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Problem {
			/**
			 * @var Problem $instance holds the single instance of this class
			 * @access private
			 */
			protected static Problem $instance;

			function __construct() {
				parent::__construct();

				$this->id = 'cornell-governance-page-info-problem-readonly';

				$this->is_readonly = true;
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Problem
			 * @since   0.1
			 */
			public static function instance(): Problem {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}