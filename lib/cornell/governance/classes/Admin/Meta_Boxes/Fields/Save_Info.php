<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Button;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Save_Info' ) ) {
		class Save_Info extends Button {
			/**
			 * @var Save_Info $instance holds the single instance of this class
			 * @access private
			 */
			protected static Save_Info $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-save',
					'label' => current_user_can( Plugin::instance()->get_capability() ) ? __( 'Save Governance Settings', 'cornell/governance' ) : __( 'Confirm Page Review', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-button', 'cornell-governance-save-info' ),
					'default' => '',
					'meta_box' => 'Info',
					'primary' => true,
				);

				parent::__construct( $atts );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Save_Info
			 * @since   0.1
			 */
			public static function instance(): Save_Info {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}