<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Field_Types {

	use Cornell\Governance\Helpers;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Field_Types\Radio_Group' ) ) {
		abstract class Radio_Group extends Base {
			/**
			 * @var string $instructions the fully-qualified class name for the message to be output
			 */
			protected string $instructions = '';

			/**
			 * Gathers the options for this radio button group
			 *
			 * @return array the array of options
			 */
			abstract public function get_options(): array;

			/**
			 * Set the instructions property so that it can be output at the top of the fieldset
			 *
			 * @param string $instructions the fully-qualified class name to be called to generate the message
			 *
			 * @access public
			 * @since  0.5.0
			 * @return void
			 */
			public function set_instructions( string $instructions ): void {
				$this->instructions = $instructions;
			}

			/**
			 * Builds the HTML for the radio button group
			 *
			 * @return string
			 */
			public function get_input(): string {
				$options    = array();
				$value_text = array();

				foreach ( $this->get_options() as $val => $label ) {
					if ( $this->get_input_value() == $val ) {
						$value_text[] = $label;
					}

					$options[] = sprintf( '
<label for="%2$s_%1$s">
	<input type="radio" name="%2$s" id="%2$s_%1$s" value="%1$s" %3$s%5$s/> 
	%4$s
</label>',
						$val,
						$this->id,
						checked( $this->get_input_value(), $val, false ),
						$label,
						$this->is_readonly ? ' readonly' : ''
					);
				}

				if ( $this->is_readonly ) {
					return $this->get_input_readonly( implode( ',', $value_text ) );
				}

				if ( class_exists( $this->instructions ) ) {
					$ob = $this->instructions::instance();
					if ( is_a( $ob, '\Cornell\Governance\Admin\Meta_Boxes\Field_Types\Base' ) ) {
						Helpers::log( 'Calling the ' . $this->instructions . ' class to generate micro-copy' );
						$instructions = $this->instructions::instance()->get_input();
					} else {
						Helpers::log( 'The class called ' . print_r( $this->instructions, true ) . ' does not appear to be the right type of field' );
						$instructions = '';
					}
				} else {
					Helpers::log( 'Could not locate a class called ' . print_r( $this->instructions, true ) );
					$instructions = '';
				}

				return sprintf( '<fieldset class="%1$s">
	<legend>%3$s</legend>
	%5$s
	<div class="%2$s">
		%4$s
	</div>
</fieldset>',
					implode( ' ', $this->classes ),
					$this->id,
					$this->label,
					implode( "\n\r", $options ),
					$instructions,
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
				$opts = $this->get_options();
				if ( array_key_exists( $value, $opts ) ) {
					return $value;
				}

				return '';
			}
		}
	}
}