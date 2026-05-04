<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( '\Cornell\Governance\Admin\Fields\Email_Active' ) ) {
		class Email_Active extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Email_Active $instance holds the single instance of this class
			 * @access private
			 */
			protected static Email_Active $instance;
			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'type'      => 'boolean',
					'id'        => 'email-prompts-active',
					'title'     => esc_html( __( 'Enable email prompts from this plugin?', 'cornell-governance' ) ),
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings-prompts',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-checkbox cornell-governance-admin-boolean',
					'default'   => true,
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Email_Active
			 * @since   0.1
			 */
			public static function instance(): Email_Active {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Validate the value of this input
			 *
			 * @param mixed $value the new value to be validated
			 *
			 * @return bool the sanitized/validated value for the field
			 * @access public
			 * @since  0.1
			 */
			public function validate_field( $value ): bool {
				return ! empty( $value );
			}
		}
	}
}