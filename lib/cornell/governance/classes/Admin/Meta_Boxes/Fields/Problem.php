<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Textarea;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Problem' ) ) {
		class Problem extends Textarea {
			/**
			 * @var Problem $instance holds the single instance of this class
			 * @access private
			 */
			protected static Problem $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-problem',
					'label' => __( 'What problem are we trying to solve for the user?', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-textarea', 'cornell-governance-problem' ),
					'default' => '',
					'meta_box' => 'Info',
				);

				$cap = Plugin::instance()->get_capability();
				if ( ! current_user_can( $cap ) ) {
					$this->is_readonly = true;
				}

				parent::__construct( $atts );
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