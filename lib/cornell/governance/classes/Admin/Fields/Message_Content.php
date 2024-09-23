<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Message_Content' ) ) {
		class Message_Content extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Message_Content $instance holds the single instance of this class
			 * @access private
			 */
			protected static Message_Content $instance;
			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'id'        => 'message-content',
					'title'     => __( 'What message should be sent when it is time for a page to be reviewed?', 'cornell/governance' ),
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-wysiwyg',
					'default'   => $this->get_default_content(),
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Message_Content
			 * @since   0.1
			 */
			public static function instance(): Message_Content {
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
				return __( 'The following pages that you manage on the Cornell websites are due to be reviewed: ', 'cornell/governance' );
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
		}
	}
}