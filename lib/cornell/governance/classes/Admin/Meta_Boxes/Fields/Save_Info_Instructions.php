<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Message;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Save_Info_Instructions' ) ) {
		class Save_Info_Instructions extends Message {
			/**
			 * @var Save_Info_Instructions $instance holds the single instance of this class
			 * @access private
			 */
			protected static Save_Info_Instructions $instance;

			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-save-instructions',
					'label'    => __( 'Save your changes:', 'cornell/governance' ),
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-message',
						'cornell-governance-save-info-instructions'
					),
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );

				$this->text = __( 'If you have made changes to the governance information, you must first save those changes here before you select the page Update button.', 'cornell/governance' );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Save_Info_Instructions
			 * @since   0.1
			 */
			public static function instance(): Save_Info_Instructions {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Generate the input HTML
			 *
			 * @access public
			 * @since 2024.06.28
			 * @return string the input HTML
			 */
			public function get_input(): string {
				if ( ! current_user_can( Plugin::instance()->get_capability() ) ) {
					return '';
				}

				return parent::get_input();
			}
		}
	}
}