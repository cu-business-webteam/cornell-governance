<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Radio_Group;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Review_Cycle' ) ) {
		abstract class Review_Cycle extends Radio_Group {
			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-review-cycle',
					'label' => __( 'Review Cycle', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-email', 'cornell-governance-review-cycle' ),
					'default' => '12',
					'meta_box' => 'Info',
				);

				$cap = Plugin::instance()->get_capability();
				if ( ! Helpers::user_can( 0,  $cap ) ) {
					$this->is_readonly = true;
				}

				parent::__construct( $atts );
			}

			/**
			 * Gathers the options for this select element
			 *
			 * @return array the array of options
			 */
			public function get_options(): array {
				return array(
					'3' => __( 'Every 3 months: January, April, July, October', 'cornell/governance' ),
					'6' => __( 'Every 6 months: November, May', 'cornell/governance' ),
					'12' => __( 'Every 12 months: June', 'cornell/governance' ),
				);
			}
		}
	}
}