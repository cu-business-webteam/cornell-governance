<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Tooltip;

	if ( ! class_exists( 'Steward_Tooltip' ) ) {
		class Steward_Tooltip extends Tooltip {
			/**
			 * @var Steward_Tooltip $instance holds the single instance of this class
			 * @access private
			 */
			protected static Steward_Tooltip $instance;

			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-steward-tooltip',
					'label'    => __( 'More about page stewards', 'cornell/governance' ),
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
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Steward_Tooltip
			 * @since   0.1
			 */
			public static function instance(): Steward_Tooltip {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Retrieve the content of the tooltip dialog
			 *
			 * @access protected
			 * @since  2023.05
			 * @return string the HTML content of the tooltip dialog
			 */
			protected function get_content(): string {
				$content = __( 'The page steward is automatically set by the WordPress "Author" field, and is only displayed here for information purposes. To change the page steward, please update the Author for this page.', 'cornell/governance' );

				return $content;
			}
		}
	}
}