<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Input;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Supervisor' ) ) {
		class Supervisor extends Input {
			/**
			 * @var Supervisor $instance holds the single instance of this class
			 * @access private
			 */
			protected static Supervisor $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-supervisor',
					'label' => __( 'Office, supervisor or secondary contact email address', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-email', 'cornell-governance-supervisor' ),
					'default' => '',
					'type' => 'email',
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
			 * @return  Supervisor
			 * @since   0.1
			 */
			public static function instance(): Supervisor {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}