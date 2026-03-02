<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {

	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Help_Documentation' ) ) {
		class Help_Documentation extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Help_Documentation $instance holds the single instance of this class
			 * @access private
			 */
			protected static Help_Documentation $instance;
			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'id'        => 'help-documentation',
					'title'     => __( 'What help documentation would you like to provide to stewards and liaisons?', 'cornell/governance' ),
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings-help',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-wysiwyg',
					'default'   => '',
				) );

				add_filter( 'mce_css', array( $this, 'add_editor_style' ) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Help_Documentation
			 * @since   0.1
			 */
			public static function instance(): Help_Documentation {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Build the default content for this field
			 *
			 * @access private
			 * @since  0.1
			 * @return string the default content
			 */
			private function get_default_content(): string {
				return '';
			}

			/**
			 * Build the input
			 *
			 * @access protected
			 * @return string
			 * @since  0.1
			 */
			protected function get_input(): string {
				$current = $this->get_input_value();

				$id = $this->page . '-' . $this->id;

				ob_start();
				wp_editor( $current, $id, array(
					'media_buttons' => false,
					'drag_drop_upload' => false,
					'textarea_name' => $id,
					'teeny' => true,
				) );
				return ob_get_clean();
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

				return wp_kses_post( $value );
			}

			/**
			 * Registers the stylesheet for this TinyMCE editor
			 *
			 * @param string $sheets a comma-delimited list of stylesheets
			 *
			 * @access public
			 * @since  1.0.1
			 * @return string the updated list of stylesheets
			 */
			public function add_editor_style( string $sheets ): string {
				if ( ! empty( $sheets ) ) {
					$sheets .= ', ';
				}

				$sheets .= Helpers::plugins_url( '/dist/css/cornell-governance/editor-style.min.css' );
				return $sheets;
			}
		}
	}
}