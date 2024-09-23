<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Field_Types {
	if ( ! class_exists( 'Button' ) ) {
		abstract class Button extends Base {
			/**
			 * @var string $type the input type
			 */
			protected string $type = 'button';
			/**
			 * @var bool $is_primary whether this is a primary or alternative action button
			 */
			protected bool $is_primary = false;

			/**
			 * Construct our Input object
			 *
			 * @param array $atts the input attributes
			 */
			public function __construct( array $atts = array() ) {
				parent::__construct( $atts );

				if ( array_key_exists( 'primary', $atts ) ) {
					$this->is_primary = boolval( $atts['primary'] );
				}
			}

			/**
			 * Builds the HTML for the input
			 *
			 * @return string
			 */
			public function get_input(): string {
				return sprintf( '<p class="%1$s">
	<button class="components-button %5$s" type="%4$s" name="%2$s" id="%2$s">%3$s</button>
</p>',
					implode( ' ', $this->classes ),
					$this->id,
					$this->label,
					$this->type,
					$this->is_primary ? 'is-primary' : 'is-secondary'
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
				return $value;
			}
		}
	}
}