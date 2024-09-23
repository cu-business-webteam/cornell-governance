<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Input;

	if ( ! class_exists( 'Editor' ) ) {
		class Editor extends Input {
			/**
			 * @var Editor $instance holds the single instance of this class
			 * @access private
			 */
			protected static Editor $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-revisions-editor',
					'label' => __( 'Editor', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-hidden', 'cornell-governance-editor' ),
					'default' => get_current_user_id(),
					'type' => 'hidden',
					'meta_box' => 'Revisions',
				);

				parent::__construct( $atts );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Editor
			 * @since   0.1
			 */
			public static function instance(): Editor {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Retrieve the value of the input
			 */
			public function get_input_value() {
				return get_current_user_id();
			}
		}
	}
}