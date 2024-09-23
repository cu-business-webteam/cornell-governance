<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Field_Types {
	if ( ! class_exists( 'Checkbox_Group' ) ) {
		abstract class Checkbox_Group extends Base {
			/**
			 * Gathers the options for this checkbox group
			 *
			 * @return array the array of options
			 */
			abstract public function get_options(): array;

			/**
			 * Builds the HTML for the checkbox group
			 *
			 * @return string
			 */
			public function get_input(): string {
				$options = array();
				$value_text = array();

				foreach ( $this->get_options() as $val => $label ) {
					if ( $this->get_input_value() == $val ) {
						$value_text[] = $label;
					}

					$options[] = sprintf( '
<label for="%2$s_%1$s">
	<input type="checkbox" name="%2$s[%1$s]" id="%2$s_%1$s" value="%1$s" %3$s%5$s/> 
	%4$s
</label>',
						$val,
						$this->id,
						in_array( $val, $this->get_input_value() ) ? ' checked' : '',
						$label,
						$this->is_readonly ? ' readonly' : ''
					);
				}

				if ( $this->is_readonly ) {
					return $this->get_input_readonly( implode( ',', $value_text ) );
				}

				return sprintf( '<fieldset class="%1$s">
	<legend>%3$s</legend>
	<div class="%2$s">
		%4$s
	</div>
</fieldset>',
					implode( ' ', $this->classes ),
					$this->id,
					$this->label,
					implode( "\n\r", $options )
				);
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
				$opts = $this->get_options();
				if ( array_key_exists( $value, $opts ) ) {
					return $value;
				}

				return '';
			}
		}
	}
}