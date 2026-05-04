<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Input;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Supervisor' ) ) {
		abstract class Supervisor extends Input {
			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-supervisor',
					'label' => esc_html( __( 'Secondary Contact Email', 'cornell-governance' ) ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-email', 'cornell-governance-supervisor' ),
					'default' => '',
					'type' => 'email',
					'meta_box' => 'Info',
				);

				$cap = Plugin::instance()->get_capability();
				if ( ! Helpers::user_can( 0,  $cap ) ) {
					$this->is_readonly = true;
				}

				parent::__construct( $atts );
			}
		}
	}
}