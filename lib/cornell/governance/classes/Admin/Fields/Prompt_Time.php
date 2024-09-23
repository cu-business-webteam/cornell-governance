<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Prompt_Time' ) ) {
		class Prompt_Time extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * Construct this input
			 */
			protected function __construct( array $atts = array() ) {
				$atts = array_merge( $atts, array(
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings-prompts',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-number',
				) );
				parent::__construct( $atts );
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

				return sprintf( '<input type="number" name="%1$s" id="%1$s" value="%2$d"/>', $id, esc_attr( $current ) );
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

				return intval( $value );
			}
		}
	}
}