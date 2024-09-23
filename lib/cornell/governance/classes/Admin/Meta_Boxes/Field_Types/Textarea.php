<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Field_Types {
	if ( ! class_exists( 'Textarea' ) ) {
		abstract class Textarea extends Base {
			/**
			 * @var string $type the input type
			 */
			protected string $type = 'textarea';
			/**
			 * @var array $atts the extra attributes for the input
			 */
			protected array $atts = array();

			/**
			 * Construct the Textarea object
			 *
			 * @param array $atts the input attributes
			 */
			public function __construct( array $atts = array() ) {
				parent::__construct( $atts );
			}

			/**
			 * Builds the HTML for the input
			 *
			 * @return string
			 */
			public function get_input(): string {
				$value = apply_filters( 'cornell/governance/textarea/value', $this->get_input_value(), $this->get_field_id() );

				if ( $this->is_readonly ) {
					return $this->get_input_readonly( $value );
				}

				if ( is_array( $value ) ) {
					$value = implode( ' ', $value );
				}

				$attributes = '';
				foreach ( $this->atts as $name => $val ) {
					$attributes .= ' ' . $name . '="' . esc_attr( $val ) . '"';
				}

				return sprintf( '<p class="%1$s">
	<label for="%2$s">%3$s</label>
	<textarea name="%2$s" id="%2$s" %6$s %7$s>%5$s</textarea>
</p>',
					implode( ' ', $this->classes ),
					$this->id,
					$this->label,
					null,
					$value,
					$attributes,
					$this->is_readonly ? ' readonly' : ''
				);
			}

			/**
			 * Validate the value of the input and prepare it for the DB
			 *
			 * @param mixed $value the current value of the field
			 *
			 * @access public
			 * @return mixed the sanitized value
			 * @since  0.1
			 */
			public function validate( $value ) {
				return sanitize_textarea_field( $value );
			}
		}
	}
}