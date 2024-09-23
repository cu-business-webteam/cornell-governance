<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Field_Types {
	if ( ! class_exists( 'Base' ) ) {
		abstract class Base {
			/**
			 * @var string $id the HTML ID for this input
			 */
			protected string $id;
			/**
			 * @var array $classes the HTML classes for this input
			 */
			protected array $classes;
			/**
			 * @var string $label the Label text for this input
			 */
			protected string $label;
			/**
			 * @var mixed $default the default value for this input
			 */
			protected $default;
			/**
			 * @var bool $is_readonly whether this input should be read-only or not
			 */
			protected bool $is_readonly = false;
			/**
			 * @var string $meta_box the class name for the meta box in which this field exists
			 */
			protected string $meta_box;
			/**
			 * @var string $namespace the current namespace name
			 * @access private
			 */
			private static string $namespace;

			/**
			 * Construct our Input object
			 *
			 * @param array $atts the input attributes
			 */
			public function __construct( array $atts = array() ) {
				self::$namespace = __NAMESPACE__;
				foreach ( array( 'id', 'label', 'classes', 'default', 'meta_box' ) as $k ) {
					if ( array_key_exists( $k, $atts ) ) {
						$this->{$k} = $atts[ $k ];
					}
				}
			}

			/**
			 * Retrieves the field ID
			 *
			 * @access public
			 * @return string the field ID
			 * @since  0.1
			 */
			public function get_field_id(): string {
				return $this->id;
			}

			/**
			 * Build the HTML for the input
			 *
			 * @return string the HTML for the input
			 */
			abstract function get_input(): string;

			/**
			 * Output the HTML for the input
			 */
			protected function do_input(): void {
				echo $this->get_input();
			}

			/**
			 * Outputs text instead of an input if the readonly property is true
			 *
			 * @param mixed $value the value to output
			 *
			 * @access protected
			 * @return string the HTML output
			 * @since  0.1
			 */
			protected function get_input_readonly( $value ): string {
				return sprintf( '
				<div class="%1$s">
	<strong class="text-label">%2$s</strong>
	<div class="input-value">%3$s</div>
</div>',
					implode( ' ', $this->classes ),
					$this->label,
					$value
				);
			}

			/**
			 * Retrieve the value of the input
			 */
			public function get_input_value() {
				$class = str_replace( array(
						'\\Field_Types',
						'\\Fields'
					), '', self::$namespace ) . '\\' . $this->meta_box;
				$key   = str_replace( $class::instance()->get_field_id() . '-', '', $this->id );

				if ( array_key_exists( $key, $class::instance()->meta ) ) {
					return $class::instance()->meta[ $key ];
				} else if ( isset( $_REQUEST[ $this->id ] ) ) {
					return $_REQUEST[ $this->id ];
				}

				return $this->default;
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
			abstract public function validate( $value );
		}
	}
}