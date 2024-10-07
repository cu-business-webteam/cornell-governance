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

	if ( ! class_exists( 'Completed_Review_Instructions' ) ) {
		class Completed_Review_Instructions extends Message {
			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-completed-review-instructions',
					'label'    => __( 'Completed Review:', 'cornell/governance' ),
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-message',
						'cornell-governance-completed-review-instructions'
					),
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );

				$this->text = __( 'Once you have thoroughly reviewed this content, please select the checkbox below and then select the "Confirm Page Review" button to notify everyone involved that the review has been completed.', 'cornell/governance' );
			}
		}
	}
}