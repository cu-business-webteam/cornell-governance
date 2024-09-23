<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Button;

	if ( ! class_exists( 'Save_Notes' ) ) {
		class Save_Notes extends Button {
			/**
			 * @var Save_Notes $instance holds the single instance of this class
			 * @access private
			 */
			protected static Save_Notes $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-notes-save',
					'label' => __( 'Update Page Notes', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-button', 'cornell-governance-save-notes' ),
					'default' => '',
					'meta_box' => 'Notes',
					'primary' => true,
				);

				parent::__construct( $atts );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Save_Notes
			 * @since   0.1
			 */
			public static function instance(): Save_Notes {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}