<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Button;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Edit_Notes_Button' ) ) {
		class Edit_Notes_Button extends Button {
			/**
			 * @var Edit_Notes_Button $instance holds the single instance of this class
			 * @access private
			 */
			protected static Edit_Notes_Button $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-notes-edit-notes',
					'label' => __( 'Edit Documentation', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-reveal-toggle-notes-editor', 'cornell-governance-field', 'cornell-governance-button', 'cornell-governance-save-notes' ),
					'default' => '',
					'meta_box' => 'Info',
					'primary' => false,
				);

				parent::__construct( $atts );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Edit_Notes_Button
			 * @since   0.1
			 */
			public static function instance(): Edit_Notes_Button {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}