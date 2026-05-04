<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( '\Cornell\Governance\Admin\Fields\Base' ) ) {
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
			 * Retrieve and returns the full-text label for the field
			 *
			 * @access public
			 * @since  0.6.2
			 * @return string the label
			 */
			public function get_field_title(): string {
				return $this->title;
			}

			/**
			 * Build the HTML for the input
			 *
			 * @return string the HTML for the input
			 * @access protected
			 * @since  0.1
			 */
			protected function get_input(): string {
				$current = $this->get_input_value();
				switch ( $this->type ) {
					case 'boolean':
						return $this->get_input_boolean();
						break;
					case 'url':
						$current = esc_url( $current );
						break;
					default:
						$current = esc_attr( $current );
						break;
				}

				$id = esc_attr( $this->page . '-' . $this->id );

				return sprintf( '<input type="%3$s" name="%1$s" id="%1$s" value="%2$s"/>', $id, empty( $current ) ? '' : $current, $this->type );
			}

			/**
			 * Build and return a boolean field
			 *
			 * @access protected
			 * @since  0.6.3
			 * @return string the HTML for the boolean field
			 */
			protected function get_input_boolean(): string {
				$id = esc_attr( $this->page . '-' . $this->id );

				$current = $this->get_input_value();

				return sprintf( '<input class="cornell-governance-boolean-field" type="%3$s" name="%1$s" id="%1$s" value="true"%2$s/>', $id, ! empty( $current ) ? ' checked="checked"' : '', 'checkbox' );
			}

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
			 * @access public
			 * @return mixed the default value
			 * @since  0.1
			 */
			public function get_default() {
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