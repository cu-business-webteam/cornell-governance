<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Field_Types {
	if ( ! class_exists( 'Input' ) ) {
		abstract class Input extends Base {
			/**
			 * @var string $type the input type
			 */
			protected string $type = 'text';
			/**
			 * @var array $atts the extra attributes for the input
			 */
			protected array $atts = array();

			/**
			 * Construct our input object
			 *
			 * @param array $atts the attributes to assign to this object
			 *
			 * @access protected
			 */
			protected function __construct( array $atts=array() ) {
				parent::__construct( $atts );

				if ( array_key_exists( 'type', $atts ) ) {
					$this->type = $atts['type'];
				}
			}

			/**
			 * Builds the HTML for the input
			 *
			 * @return string
			 */
			public function get_input(): string {
				$value = $this->get_input_value();

				if ( is_array( $value ) ) {
					$value = implode( ' ', $value );
				}

				if ( $this->is_readonly ) {
					return $this->get_input_readonly( $value );
				}

				$attributes = '';
				foreach ( $this->atts as $name => $val ) {
					$attributes .= ' ' . $name . '="' . esc_attr( $val ) . '"';
				}

				if ( 'hidden' !== $this->type ) {
					return sprintf( '<p class="%1$s">
	<label for="%2$s">%3$s</label>
	<input type="%4$s" name="%2$s" id="%2$s" value="%5$s"%6$s%7$s/>
</p>',
						implode( ' ', $this->classes ),
						$this->id,
						$this->label,
						$this->type,
						$value,
						$attributes,
						$this->is_readonly ? ' readonly' : ''
					);
				} else {
					return sprintf( '<input type="%4$s" name="%2$s" id="%2$s" value="%5$s"%6$s%7$s/>',
						null,
						$this->id,
						null,
						$this->type,
						$value,
						$attributes,
						$this->is_readonly ? ' readonly' : ''
					);
				}
			}

			/**
			 * Validate the value of the input and prepare it for the DB
			 *
			 * @param mixed $value the current value of the field
			 *
			 * @access public
			 * @since  0.1
			 * @return mixed the sanitized value
			 */
			public function validate( $value ) {
				$new_value = null;

				switch ( $this->type ) {
					case 'email' :
						$new_value = is_email( $value );
						break;
					case 'url' :
						$new_value = sanitize_url( $value );
						break;
					case 'number' :
						$new_value = $value * 1;
						break;
					default :
						$new_value = sanitize_text_field( $value );
						break;
				}

				return $new_value;
			}
		}
	}
}