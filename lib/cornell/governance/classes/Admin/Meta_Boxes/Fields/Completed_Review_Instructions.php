<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Message;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Completed_Review_Instructions' ) ) {
		class Completed_Review_Instructions extends Message {
			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-completed-review-instructions',
					'label'    => esc_html( __( 'Completed Review:', 'cornell-governance' ) ),
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-message',
						'cornell-governance-completed-review-instructions'
					),
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );

				$this->text = esc_html( __( 'Once you have completed all of the review tasks, you need to confirm that the page is in compliance for this review cycle.', 'cornell-governance' ) );
			}
		}
	}
}