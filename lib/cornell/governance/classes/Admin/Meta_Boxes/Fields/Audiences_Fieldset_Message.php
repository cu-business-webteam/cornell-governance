<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Message;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Audiences_Fieldset_Message' ) ) {
		abstract class Audiences_Fieldset_Message extends Message {
			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-audiences-microcopy',
					'label'    => '',
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-message',
						'cornell-governance-audiences-microcopy'
					),
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );

				$this->text = esc_html( __( 'Identify the intended primary and secondary audiences for this page. One per dropdown.', 'cornell-governance' ) );
			}
		}
	}
}