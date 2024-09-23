<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Tertiary_Prompt' ) ) {
		class Tertiary_Prompt extends Prompt_Time {
			/**
			 * @var Tertiary_Prompt $instance holds the single instance of this class
			 * @access private
			 */
			protected static Tertiary_Prompt $instance;
			/**
			 * Construct this input
			 */
			protected function __construct( array $atts = array() ) {
				parent::__construct( array(
					'id'        => 'tertiary-prompt-time',
					'title'     => __( 'How many days before a review is due should the third and final prompt message be sent?', 'cornell/governance' ),
					'default'   => 15,
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Tertiary_Prompt
			 * @since   0.1
			 */
			public static function instance(): Tertiary_Prompt {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}