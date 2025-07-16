<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Archive_Active' ) ) {
		class Archive_Active extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Archive_Active $instance holds the single instance of this class
			 * @access private
			 */
			protected static Archive_Active $instance;
			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'type'      => 'boolean',
					'id'        => 'archive-active',
					'title'     => __( 'Integrate Wayback Machine archival of modified content?', 'cornell/governance' ),
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings-archive',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-checkbox cornell-governance-admin-boolean',
					'default'   => false,
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Archive_Active
			 * @since   0.1
			 */
			public static function instance(): Archive_Active {
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