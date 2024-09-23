<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Select;

	if ( ! class_exists( 'Primary_Audience' ) ) {
		class Primary_Audience extends Audience {
			/**
			 * @var Primary_Audience $instance holds the single instance of this class
			 * @access private
			 */
			protected static Primary_Audience $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-primary-audience',
					'label' => __( 'Primary Audience (only 1)', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-primary-audience' ),
					'default' => '',
					'meta_box' => 'Info',
					'attributes' => array( 'placeholder' => __( '-- Select an Audience --', 'cornell/governance' ) ),
				);
				parent::__construct( $atts );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Primary_Audience
			 * @since   0.1
			 */
			public static function instance(): Primary_Audience {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}