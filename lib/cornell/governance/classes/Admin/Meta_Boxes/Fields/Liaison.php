<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Select;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Liaison' ) ) {
		class Liaison extends Select {
			/**
			 * @var Liaison $instance holds the single instance of this class
			 * @access private
			 */
			protected static Liaison $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-liaison',
					/* translators: The placeholder is a plugin setting with the name of the managing office, e.g., "Marketing Liaison" */
					'label' => sprintf( __( '%s Liaison', 'cornell/governance' ), Plugin::instance()->get_managing_office() ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-select', 'cornell-governance-liaison' ),
					'default' => '',
					'meta_box' => 'Info',
					'attributes' => array( 'placeholder' => __( '-- Select a User --', 'cornell/governance' ) ),
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
			 * @return  Liaison
			 * @since   0.1
			 */
			public static function instance(): Liaison {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Gather the terms within the Audience taxonomy
			 *
			 * @access protected
			 * @since  0.1
			 * @return array the list of Audiences
			 */
			protected function get_users(): array {
				return get_users( array(
					'capability' => Plugin::instance()->get_capability(),
				) );
			}

			/**
			 * Gathers the options for this select element
			 *
			 * @return array the array of options
			 */
			public function get_options(): array {
				$options = array( '' => __( 'Please select a user', 'cornell-governance' ) );
				foreach ( $this->get_users() as $user ) {
					$options[$user->user_email] = $user->display_name;
				}

				return $options;
			}
		}
	}
}