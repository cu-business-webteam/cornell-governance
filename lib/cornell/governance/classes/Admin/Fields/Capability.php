<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Capability' ) ) {
		class Capability extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Capability $instance holds the single instance of this class
			 * @access private
			 */
			protected static Capability $instance;
			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'type'      => 'select',
					'id'        => 'capability',
					'title'     => __( 'Which capability should be used to determine who can change governance information on a page?', 'cornell/governance' ),
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-select',
					'default'   => 'manage_options',
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Capability
			 * @since   0.1
			 */
			public static function instance(): Capability {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Attempt to build a list of all available user capabilities
			 *
			 * @return array
			 * @access private
			 * @since  0.1
			 */
			private function get_all_caps(): array {
				global $wp_roles;
				$all_caps = array();
				foreach ( $wp_roles->role_objects as $role ) {
					$all_caps = array_merge( $all_caps, array_keys( $role->capabilities ) );
				}

				$all_caps = array_unique( $all_caps );
				asort( $all_caps, SORT_STRING );

				return $all_caps;
			}

			function get_input(): string {
				$caps    = $this->get_all_caps();
				$current = $this->get_input_value();

				$options = array();
				foreach ( $caps as $cap ) {
					$options[] = sprintf( '<option value="%1$s" %2$s>%1$s</option>', $cap, selected( $cap, $current, false ) );
				}

				$id = $this->page . '-' . $this->id;

				return sprintf( '<select name="%1$s" id="%1$s">%2$s</select>', $id, implode( '', $options ) );
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

				$all_caps = $this->get_all_caps();
				if ( in_array( $value, $all_caps, true ) ) {
					return $value;
				}

				return null;
			}
		}
	}
}