<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Button;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Save_Info' ) ) {
		abstract class Save_Info extends Button {

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-save',
					'label' => Helpers::user_can( 0,  Plugin::instance()->get_capability() ) ? esc_html( __( 'Save Governance Settings', 'cornell-governance' ) ) : esc_html( __( 'Confirm Page Review', 'cornell-governance' ) ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-button', 'cornell-governance-save-info' ),
					'default' => '',
					'meta_box' => 'Info',
					'primary' => true,
				);

				if ( $atts['label'] == __( 'Confirm Page Review', 'cornell-governance' ) ) {
					$atts['classes'][] = 'cornell-governance-confirm-page-review';
				} else {
					$atts['classes'][] = 'cornell-governance-save-governance-settings';
				}

				parent::__construct( $atts );
			}
		}
	}
}