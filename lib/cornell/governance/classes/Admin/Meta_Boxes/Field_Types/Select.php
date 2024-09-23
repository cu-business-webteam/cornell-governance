<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Field_Types {
	if ( ! class_exists( 'Base' ) ) {
		abstract class Select extends Base {
			/**
			 * Gathers the options for this select element
			 *
			 * @return array the array of options
			 */
			abstract public function get_options(): array;

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
			protected function __construct( array $atts = array() ) {
				parent::__construct( $atts );

				if ( array_key_exists( 'attributes', $atts ) ) {
					$this->atts = $atts['attributes'];
				}
			}

			/**
			 * Builds the HTML for the select element
			 *
			 * @return string
			 */
			public function get_input(): string {
				$options    = array();
				$value_text = array();

				$attributes = '';
				foreach ( $this->atts as $name => $val ) {
					$attributes .= ' ' . $name . '="' . esc_attr( $val ) . '"';
				}

				foreach ( $this->get_options() as $val => $label ) {
					if ( $this->get_input_value() === $val ) {
						$value_text[] = $label;
					}
					$options[] = sprintf( '<option value="%s"%s>%s</option>', $val, selected( $this->get_input_value(), $val, false ), $label );
				}

				if ( $this->is_readonly ) {
					return $this->get_input_readonly( implode( ', ', $value_text ) );
				}

				if ( count( $options ) < 11 ) {
					return sprintf( '<p class="%1$s">
	<label for="%2$s">%3$s</label>
	<select class="%9$s" name="%2$s" id="%2$s" %7$s %8$s>
		%4$s
	</select>
</p>',
						implode( ' ', $this->classes ),
						$this->id,
						$this->label,
						implode( "\n\r", $options ),
						null,
						null,
						$this->is_readonly ? ' readonly' : '',
						$attributes,
						'components-select-control__input'
					);
				} else {
					return sprintf( '<p class="%1$s">
	<label for="%2$s">%3$s</label>
	<input name="%2$s" id="%2$s" value="%6$s" list="%5$s" %7$s %8$s/>
	<datalist id="%5$s">
		%4$s
	</datalist>
</p>',
						implode( ' ', $this->classes ),
						$this->id,
						$this->label,
						implode( "\n\r", $options ),
						$this->id . '-datalist',
						$this->get_input_value(),
						$this->is_readonly ? ' readonly' : '',
						$attributes
					);
				}
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
				$opts = $this->get_options();

				if ( array_key_exists( $value, $opts ) ) {
					return $value;
				}

				return '';
			}
		}
	}
}