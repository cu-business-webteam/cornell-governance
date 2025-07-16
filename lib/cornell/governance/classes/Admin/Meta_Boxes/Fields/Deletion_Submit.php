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

	if ( ! class_exists( 'Deletion_Submit' ) ) {
		abstract class Deletion_Submit extends Button {

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-deletion-submit',
					'label' => __( 'Submit Deletion Request', 'cornell-governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-button', 'cornell-governance-deletion-submit' ),
					'default' => '',
					'meta_box' => 'Info',
					'primary' => true,
				);

				parent::__construct( $atts );
			}
		}
	}
}