<?php

namespace {
    if ( ! defined( 'ABSPATH' ) ) {
        die( 'You do not have permission to access this file directly.' );
    }
}

namespace Cornell\Governance\Admin\Fields {
    if ( ! class_exists( '\Cornell\Governance\Admin\Fields\Frontend_Compliance' ) ) {
        class Frontend_Compliance extends Base {
            /**
             * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
             *      WordPress tends to run this callback twice
             */
            protected static bool $did_sanitize = false;
            /**
             * @var Frontend_Compliance $instance holds the single instance of this class
             * @access private
             */
            protected static Frontend_Compliance $instance;

            /**
             * Construct this input
             */
            protected function __construct() {
                parent::__construct( array(
                    'type'      => 'boolean',
                    'id'        => 'frontend-compliance-active',
                    'title'     => esc_html( __( 'Display a compliance notice on the frontend to logged-in privileged users?', 'cornell-governance' ) ),
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
             * @return  Frontend_Compliance
             * @since   0.1
             */
            public static function instance(): Frontend_Compliance {
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