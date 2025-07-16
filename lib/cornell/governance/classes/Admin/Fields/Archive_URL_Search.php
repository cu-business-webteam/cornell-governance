<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Archive_URL_Search' ) ) {
		class Archive_URL_Search extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Archive_URL_Search $instance holds the single instance of this class
			 * @access private
			 */
			protected static Archive_URL_Search $instance;
			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'type'      => 'url',
					'id'        => 'archive-url-search',
					'title'     => __( 'If you would like to replace this site\'s URL with a production URL, enter this site\'s URL here.', 'cornell/governance' ),
					'page'      => 'cornell-governance',
					'section'   => 'cornell-governance-settings-archive',
					'class'     => 'cornell-governance-admin-field cornell-governance-admin-text cornell-governance-admin-url',
					'default'   => get_bloginfo( 'url' ),
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Archive_URL_Search
			 * @since   0.1
			 */
			public static function instance(): Archive_URL_Search {
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
				$current = $this->get_input_value();

				$id = $this->page . '-' . $this->id;

				return sprintf( '<input type="%3$s" name="%1$s" id="%1$s" value="%2$s"/>', $id, esc_url( $current ), $this->type );
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

				return esc_url( $value );
			}
		}
	}
}