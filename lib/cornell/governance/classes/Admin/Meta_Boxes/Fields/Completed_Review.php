<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Confirm;

	if ( ! class_exists( 'Completed_Review' ) ) {
		class Completed_Review extends Confirm {
			/**
			 * @var Completed_Review $instance holds the single instance of this class
			 * @access private
			 */
			protected static Completed_Review $instance;

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

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Completed_Review
			 * @since   0.1
			 */
			public static function instance(): Completed_Review {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}