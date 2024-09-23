<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Textarea;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Goals' ) ) {
		class Goals extends Textarea {
			/**
			 * @var Goals $instance holds the single instance of this class
			 * @access private
			 */
			protected static Goals $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-goals',
					'label' => __( 'Page Goal', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-textarea', 'cornell-governance-goals' ),
					'default' => '',
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
			 * @return  Goals
			 * @since   0.1
			 */
			public static function instance(): Goals {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}