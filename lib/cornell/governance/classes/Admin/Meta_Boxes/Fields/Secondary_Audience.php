<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Select;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Secondary_Audience' ) ) {
		abstract class Secondary_Audience extends Audience {
			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-secondary-audience',
					'label' => __( 'Secondary Audience (optional)', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-secondary-audience' ),
					'default' => '',
					'meta_box' => 'Info',
					'attributes' => array( 'placeholder' => __( '-- Select an Audience --', 'cornell/governance' ) ),
				);
				parent::__construct( $atts );
			}
		}
	}
}