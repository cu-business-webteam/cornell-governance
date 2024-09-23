<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Base' ) ) {
		abstract class Base {
			/**
			 * @var string $type the input type
			 */
			protected string $type;
			/**
			 * @var string $id the HTML ID for this input
			 */
			protected string $id;
			/**
			 * @var string $title the plain-language title/label for the input
			 */
			protected string $title;
			/**
			 * @var string $page the settings page on which to output the field
			 */
			protected string $page;
			/**
			 * @var string $section the settings section in which to output the field
			 */
			protected string $section;
			/**
			 * @var string $class CSS Class to be added to the <tr> element when the field is output
			 */
			protected string $class;
			/**
			 * @var mixed $default the default value for this input
			 */
			protected $default;

			/**
			 * Construct our Input object
			 *
			 * @param array $atts the input attributes
			 */
			public function __construct( array $atts = array() ) {
				foreach ( array( 'id', 'title', 'page', 'section', 'class', 'default', 'type' ) as $k ) {
					if ( array_key_exists( $k, $atts ) ) {
						$this->{$k} = $atts[ $k ];
					}
				}

				if ( ! array_key_exists( 'type', $atts ) ) {
					$this->type = 'text';
				}

				register_setting(
					'cornell-governance',
					'cornell-governance-' . $this->id,
					array(
						'sanitize_callback' => array( $this, 'validate_field' ),
						'type'              => 'boolean' !== $this->type ? 'string' : 'boolean',
						'description'       => $this->title,
						'show_in_rest'      => false,
					)
				);

				$id = $this->page . '-' . $this->id;

				$args = array();
				if ( ! empty( $this->class ) ) {
					$args['class'] = $this->class;
				}

				if ( ! in_array( $this->type, array( 'checkbox', 'radio' ) ) ) {
					$args['label_for'] = $id;
				}

				add_settings_field( $id, $this->title, array(
					$this,
					'do_input'
				), $this->page, $this->section, $args );
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
			 * @access protected
			 * @since  0.1
			 */
			abstract protected function get_input(): string;

			/**
			 * Validate the value of this input
			 *
			 * @param mixed $value the new value to be validated
			 *
			 * @return mixed the sanitized/validated value for the field
			 * @access public
			 * @since  0.1
			 */
			abstract public function validate_field( $value );

			/**
			 * Output the HTML for the input
			 */
			public function do_input(): void {
				echo $this->get_input();
			}

			/**
			 * Retrieve the default value for this input/option
			 *
			 * @access protected
			 * @return mixed the default value
			 * @since  0.1
			 */
			protected function get_default() {
				return $this->default;
			}

			/**
			 * Retrieve the value of the input
			 */
			public function get_input_value() {
				return get_option( $this->page . '-' . $this->id, $this->get_default() );
			}
		}
	}
}