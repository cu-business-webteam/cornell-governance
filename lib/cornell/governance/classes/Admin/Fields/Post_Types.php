<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {

	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Post_Types' ) ) {
		class Post_Types extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Post_Types $instance holds the single instance of this class
			 * @access private
			 */
			protected static Post_Types $instance;
			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'type'      => 'checkbox',
					'id'        => 'post-types',
					'title'     => __( 'On which post types should the governance information be displayed?', 'cornell/governance' ),
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-checkbox',
					'default'   => Plugin::instance()->get_post_types(),
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Post_Types
			 * @since   0.1
			 */
			public static function instance(): Post_Types {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Attempt to build a list of all registered post types
			 *
			 * @return array
			 * @access private
			 * @since  0.1
			 */
			private function get_post_types(): array {
				$all_types = array();
				$types = get_post_types( array( 'show_ui' => true ), 'objects' );
				foreach ( $types as $type ) {
					$all_types[$type->name] = $type->label;
				}

				return $all_types;
			}

			function get_input(): string {
				$types  = $this->get_post_types();
				$current = $this->get_input_value();
				if ( ! is_array( $current ) ) {
					$current = Plugin::instance()->get_post_types();
				}

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

				return sprintf( '<fieldset><legend class="screen-reader-text">%1$s</legend>%2$s</fieldset>', $this->title, implode( '', $options ) );
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
			public function validate_field( $value ) {
				if ( self::$did_sanitize )
					return $value;

				self::$did_sanitize = true;

				$types = array();

				$all_types = array_keys( $this->get_post_types() );
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