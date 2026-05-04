<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Tooltip;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Steward_Tooltip' ) ) {
		abstract class Steward_Tooltip extends Tooltip {
			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-steward-tooltip',
					'label'    => esc_html( __( 'More about primary stewards', 'cornell-governance' ) ),
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-tooltip',
						'cornell-governance-steward-tooltip',
						'cornell-governance-inline-tooltip',
						'one-third',
					),
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );
			}

			/**
			 * Retrieve the content of the tooltip dialog
			 *
			 * @access protected
			 * @since  2023.05
			 * @return string the HTML content of the tooltip dialog
			 */
			protected function get_content(): string {
				$content = esc_html( __( 'The primary steward is automatically set by the WordPress "Author" field. To change the page steward, please update the Author for this page.', 'cornell-governance' ) );

				return $content;
			}
		}
	}
}