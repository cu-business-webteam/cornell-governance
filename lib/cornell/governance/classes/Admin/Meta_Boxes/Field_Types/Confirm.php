<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Field_Types {
	if ( ! class_exists( 'Confirm' ) ) {
		abstract class Confirm extends Base {
			/**
			 * @var string $type the input type
			 */
			protected string $type = 'checkbox';

			/**
			 * Builds the HTML for the input
			 *
			 * @return string
			 */
			public function get_input(): string {
				return sprintf( '<p class="%1$s">
	<input type="%4$s" name="%2$s" id="%2$s" value="1"/>
	<label for="%2$s">%3$s</label>
</p>',
					implode( ' ', $this->classes ),
					$this->id,
					$this->label,
					$this->type
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
				return '1' === $value;
			}
		}
	}
}