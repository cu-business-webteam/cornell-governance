<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Mark_For_Deletion' ) ) {
		class Mark_For_Deletion extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Mark_For_Deletion $instance holds the single instance of this class
			 * @access private
			 */
			protected static Mark_For_Deletion $instance;
			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'type'      => 'boolean',
					'id'        => 'mark-for-deletion-active',
					'title'     => __( 'Include a button allowing stewards to mark pages for deletion?', 'cornell/governance' ),
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-checkbox cornell-governance-admin-boolean',
					'default'   => false,
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Mark_For_Deletion
			 * @since   0.1
			 */
			public static function instance(): Mark_For_Deletion {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Build the input
			 *
			 * @access protected
			 * @return string
			 * @since  0.1
			 */
			protected function get_input(): string {
				return $this->get_input_boolean();
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
				return ! empty( $value );
			}
		}
	}
}