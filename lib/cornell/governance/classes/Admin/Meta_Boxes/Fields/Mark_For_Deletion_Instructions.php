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

	if ( ! class_exists( 'Mark_For_Deletion_Instructions' ) ) {
		abstract class Mark_For_Deletion_Instructions extends Message {
			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-mark-for-deletion-instructions',
					'label'    => __( 'Marked for Deletion:', 'cornell/governance' ),
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-message',
						'cornell-governance-mark-for-deletion-instructions'
					),
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );

				$this->text = __( 'If you indicate below that this content should be deleted, an email will be automatically dispatched to both the steward and the Liaison once you select the "Submit Deletion Request" button below.', 'cornell/governance' );
			}
		}
	}
}