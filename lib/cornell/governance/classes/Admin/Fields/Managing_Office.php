<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Managing_Office' ) ) {
		class Managing_Office extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Managing_Office $instance holds the single instance of this class
			 * @access private
			 */
			protected static Managing_Office $instance;
			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'id'        => 'managing-office',
					'title'     => __( 'What is the name of the office that manages Governance for your organization?', 'cornell/governance' ),
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-text',
					'default'   => __( 'MarCom', 'cornell/governance' ),
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Managing_Office
			 * @since   0.1
			 */
			public static function instance(): Managing_Office {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Build the input
			 *
			 * @access protected
			 * @return string
			 * @since  0.1
			 */
			protected function get_input(): string {
				$current = $this->get_input_value();

				$id = $this->page . '-' . $this->id;

				return sprintf( '<input type="text" name="%1$s" id="%1$s" value="%2$s"/>', $id, esc_attr( $current ) );
			}

			/**
			 * Validate the value of this input
			 *
			 * @param mixed $value the new value to be validated
			 *
			 * @return mixed the sanitized/validated value for the field
			 * @access public
			 * @since  0.1
			 */
			public function validate_field( $value ) {
				if ( self::$did_sanitize )
					return $value;

				self::$did_sanitize = true;

				return sanitize_text_field( $value );
			}
		}
	}
}