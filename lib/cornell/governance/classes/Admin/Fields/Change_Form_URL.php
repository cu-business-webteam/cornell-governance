<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Change_Form_URL' ) ) {
		class Change_Form_URL extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Change_Form_URL $instance holds the single instance of this class
			 * @access private
			 */
			protected static Change_Form_URL $instance;
			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'type'      => 'url',
					'id'        => 'change-form-url',
					'title'     => __( 'What is the URL of the form that users can fill out to submit requests for changes?', 'cornell/governance' ),
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings-change-form',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-text cornell-governance-admin-url',
					'default'   => '',
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Change_Form_URL
			 * @since   0.1
			 */
			public static function instance(): Change_Form_URL {
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

				return sprintf( '<input type="%3$s" name="%1$s" id="%1$s" value="%2$s"/>', $id, esc_url( $current ), $this->type );
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

				return esc_url( $value );
			}
		}
	}
}