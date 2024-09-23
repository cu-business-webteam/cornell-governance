<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Change_Form_Props' ) ) {
		class Change_Form_Props extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Change_Form_Props $instance holds the single instance of this class
			 * @access private
			 */
			protected static Change_Form_Props $instance;
			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'type'      => 'checkbox',
					'id'        => 'change-form-props',
					'title'     => __( 'Which properties of the post should be appended to the change form URL?', 'cornell/governance' ),
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings-change-form',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-checkbox',
					'default'   => array(),
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Change_Form_Props
			 * @since   0.1
			 */
			public static function instance(): Change_Form_Props {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Build a list of the available post properties
			 *
			 * @return array
			 * @access private
			 * @since  0.1
			 */
			private function get_post_props(): array {
				return array(
					'ID' => __( 'Post ID', 'cornell/governance' ),
					'post_title' => __( 'Post Title', 'cornell/governance' ),
					'permalink' => __( 'Post URL', 'cornell/governance' ),
					'current_user_id' => __( 'The ID of the user that clicked the link', 'cornell/governance' ),
					'current_user_email' => __( 'The Email address of the user that clicked the link', 'cornell/governance' ),
					'current_user_display_name' => __( 'The Display Name of the user that clicked the link', 'cornell/governance' ),
				);
			}

			/**
			 * Build the input
			 *
			 * @access protected
			 * @return string
			 * @since  0.1
			 */
			protected function get_input(): string {
				$types  = $this->get_post_props();
				$current = $this->get_input_value();

				$id = $this->page . '-' . $this->id;

				$options = array();
				foreach ( $types as $name => $label ) {
					$checked = '';

					if ( is_array( $current ) && in_array( $name, $current ) ) {
						$checked = 'checked="checked"';
					}

					$options[$name] = sprintf(
						'<p class="checkbox-item"><input type="checkbox" name="%1$s[%2$s]" id="%1$s_%2$s" %3$s/> <label for="%1$s_%2$s">%4$s</label></p>',
						$id,
						$name,
						$checked,
						$label
					);
				}

				return sprintf( '<fieldset><legend class="screen-reader-text">%1$s</legend>%2$s</fieldset>', $this->title, implode( '', $options ) );			}

			/**
			 * Validate the value of this input
			 *
			 * @param mixed $value the new value to be validated
			 *
			 * @return mixed the sanitized/validated value for the field
			 * @access public
			 * @since  0.1
			 */
			public function validate_field( $value ) {
				if ( self::$did_sanitize )
					return $value;

				self::$did_sanitize = true;

				$types = array();

				$all_types = array_keys( $this->get_post_props() );
				foreach ( array_keys( $value ) as $item ) {
					if ( in_array( $item, $all_types, true ) ) {
						$types[] = $item;
					}
				}

				return $types;
			}
		}
	}
}