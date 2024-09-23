<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Radio_Group;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Review_Cycle' ) ) {
		class Review_Cycle extends Radio_Group {
			/**
			 * @var Review_Cycle $instance holds the single instance of this class
			 * @access private
			 */
			protected static Review_Cycle $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-review-cycle',
					'label' => __( 'Review Cycle', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-email', 'cornell-governance-review-cycle' ),
					'default' => '12',
					'meta_box' => 'Info',
				);

				$cap = Plugin::instance()->get_capability();
				if ( ! current_user_can( $cap ) ) {
					$this->is_readonly = true;
				}

				parent::__construct( $atts );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Review_Cycle
			 * @since   0.1
			 */
			public static function instance(): Review_Cycle {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
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