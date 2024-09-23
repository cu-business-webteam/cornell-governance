<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Field_Types {

	use Cornell\Governance\Admin\Meta_Boxes\Info;

	if ( ! class_exists( 'Repeater' ) ) {
		abstract class Repeater extends Input {
			/**
			 * @var string $type the input type
			 */
			protected string $type = 'text';
			/**
			 * @var string $add_text the text that should be used for the "Add New" button
			 */
			protected string $add_text;
			/**
			 * @var string $remove_text the text that should be used for the "Remove" button
			 */
			protected string $remove_text;
			/**
			 * @var string $description additional help text that should be displayed in the field
			 */
			protected string $description;
			/**
			 * @var string $short_name the text that should prefix the item number for each individual field label
			 */
			protected string $short_name;
			/**
			 * @var boolean $sortable whether this list should be sortable or not
			 */
			protected bool $sortable = false;

			/**
			 * Builds the HTML for the input
			 *
			 * @return string
			 */
			public function get_input(): string {
				$value = $this->get_input_value();
				if ( empty( $value ) ) {
					$value = array( '' );
				}

				if ( $this->is_readonly ) {
					$value_text = sprintf( '<ol><li>%s</li></ol>', implode( '</li><li>', $value ) );

					return $this->get_input_readonly( $value_text );
				}

				$fields = array();

				$i = 1;

				foreach ( $this->get_defaults() as $item ) {
					$fields[ $i ] = sprintf( '<li class="repeater-field-static">
	<p class="static-label">
		%2$s %3$d
	</p>
	<div class="repeater-static-container">
		<p>
			%1$s
		</p>
	</div>
</li>',
						$item,
						empty( $this->short_name ) ? $this->label : $this->short_name,
						$i
					);

					$i ++;
				}

				foreach ( $value as $item ) {
					$fields[ $i ] = sprintf( '<li class="repeater-field" data-number="%5$d">
	<label for="%1$s_%5$d">
		%2$s %5$d
	</label>
	<div class="repeater-input-container">
		<input type="%3$s" name="%4$s[]" id="%1$s_%5$d" value="%6$s" %7$s/>
	</div>
</li>',
						$this->id,
						empty( $this->short_name ) ? $this->label : $this->short_name,
						$this->type,
						$this->id,
						$i,
						$item,
						$this->is_readonly ? ' readonly' : ''
					);

					$i ++;
				}

				$list_classes = array(
					'repeater-field-set',
				);

				if ( $this->sortable ) {
					$list_classes[] = 'sortable';
				}

				return sprintf( '<fieldset class="%1$s">
	<legend %9$s>
		%2$s
	</legend>
	%10$s
	<ol class="%8$s" %4$s data-add-text="%5$s" data-remove-text="%6$s" data-root-id="%7$s">
		%3$s
	</ol>
</fieldset>',
					implode( ' ', $this->classes ),
					$this->label,
					implode( ' ', $fields ),
					$this->is_readonly ? ' data-readonly="true"' : ' data-readonly="false"',
					$this->add_text,
					$this->remove_text,
					$this->id,
					implode( ' ', $list_classes ),
					empty( $this->short_name ) ? '' : ' data-shortname="' . esc_attr( $this->short_name ) . '"',
					empty( $this->description ) ? '' : sprintf( '<p class="field-note">%s</p>', $this->description )
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
				if ( ! is_array( $value ) ) {
					return array();
				}

				$new_value = array();

				foreach ( $value as $key => $item ) {
					switch ( $this->type ) {
						case 'email' :
							$new_value[ $key ] = is_email( $item );
							break;
						case 'url' :
							$new_value[ $key ] = sanitize_url( $item );
							break;
						case 'number' :
							$new_value[ $key ] = $item * 1;
							break;
						default :
							$new_value[ $key ] = sanitize_text_field( $item );
							break;
					}
				}

				return $new_value;
			}

			/**
			 * If there are any default items to add to the front of the list of items,
			 *      retrieve and return them
			 *
			 * @access protected
			 * @return array
			 * @since  2023.05
			 */
			protected function get_defaults(): array {
				return array();
			}
		}
	}
}