<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Textarea;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Problem' ) ) {
		abstract class Problem extends Textarea {
			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-problem',
					'label' => __( 'Purpose/Problems Solved', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-textarea', 'cornell-governance-problem' ),
					'default' => '',
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