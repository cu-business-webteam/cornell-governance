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

	if ( ! class_exists( 'Focus_Areas_Fieldset_Message' ) ) {
		abstract class Focus_Areas_Fieldset_Message extends Message {
			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-focus-areas-microcopy',
					'label'    => '',
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-message',
						'cornell-governance-focus-areas-microcopy'
					),
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );

				$this->text = __( 'Document important information relevant to managing and maintaining this page.', 'cornell/governance' );
			}
		}
	}
}