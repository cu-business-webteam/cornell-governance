<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Confirm;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Completed_Review' ) ) {
		abstract class Completed_Review extends Confirm {
			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-completed-review',
					'label' => __( 'I am confirming that I have completed the page review tasks for this review cycle.', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-confirm', 'cornell-governance-completed-review' ),
					'default' => '',
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );
			}
		}
	}
}