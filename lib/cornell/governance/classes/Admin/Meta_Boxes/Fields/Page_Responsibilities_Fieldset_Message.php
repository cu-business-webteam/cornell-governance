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

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Page_Responsibilities_Fieldset_Message' ) ) {
		abstract class Page_Responsibilities_Fieldset_Message extends Message {
			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-page-responsibilities-microcopy',
					'label'    => '',
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-message',
						'cornell-governance-page-responsibilities-microcopy'
					),
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );

				$this->text = esc_html( __( 'This section identifies the person responsible for updating the page content, as well as their backup and the liaison attached to this page content.', 'cornell-governance' ) );
			}
		}
	}
}