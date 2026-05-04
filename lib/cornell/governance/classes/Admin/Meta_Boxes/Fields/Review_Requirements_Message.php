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

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Review_Requirements_Message' ) ) {
		abstract class Review_Requirements_Message extends Message {
			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-review-requirements-microcopy',
					'label'    => '',
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-message',
						'cornell-governance-review-requirements-microcopy'
					),
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );

				$this->text = esc_html( __( 'Based on the content and goals, specify how often this page must be reviewed and the exact review tasks relevant to this page. Universal tasks applicable to all pages are already populated.', 'cornell-governance' ) );
			}
		}
	}
}