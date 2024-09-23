<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Tooltip;

	if ( ! class_exists( 'Review_Cycle_Instructions' ) ) {
		class Review_Cycle_Instructions extends Tooltip {
			/**
			 * @var Review_Cycle_Instructions $instance holds the single instance of this class
			 * @access private
			 */
			protected static Review_Cycle_Instructions $instance;

			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-review-cycle-instructions',
					'label'    => __( 'How does this work?', 'cornell/governance' ),
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-tooltip',
						'cornell-governance-review-cycle-instructions'
					),
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Review_Cycle_Instructions
			 * @since   0.1
			 */
			public static function instance(): Review_Cycle_Instructions {
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
				$content = __( 'These are some instructions explaining how the review cycle will work.', 'cornell/governance' );

				return $content;
			}
		}
	}
}