<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\WYSIWYG;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Notes_Notes' ) ) {
		class Notes_Notes extends WYSIWYG {
			/**
			 * @var Notes_Notes $instance holds the single instance of this class
			 * @access private
			 */
			protected static Notes_Notes $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-notes-notes',
					'label' => __( 'Relevant documentation', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-textarea', 'cornell-governance-notes-notes' ),
					'default' => '',
					'meta_box' => 'Notes',
					'wysiwyg_settings' => array(
						'teeny' => true,
					),
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
			 * @return  Notes_Notes
			 * @since   0.1
			 */
			public static function instance(): Notes_Notes {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}