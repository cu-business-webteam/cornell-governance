<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Change_Form_Active' ) ) {
		class Change_Form_Active extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Change_Form_Active $instance holds the single instance of this class
			 * @access private
			 */
			protected static Change_Form_Active $instance;
			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'type'      => 'boolean',
					'id'        => 'change-form-active',
					'title'     => __( 'Include a link to a form allowing users to request changes to the governance settings for a page?', 'cornell/governance' ),
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings-change-form',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-checkbox cornell-governance-admin-boolean',
					'default'   => false,
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Change_Form_Active
			 * @since   0.1
			 */
			public static function instance(): Change_Form_Active {
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
				$id = $this->page . '-' . $this->id;

				$current = $this->get_input_value();

				return sprintf( '<input type="%3$s" name="%1$s" id="%1$s" value="true"%2$s/>', $id, ! empty( $current ) ? ' checked="checked"' : '', 'checkbox' );
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