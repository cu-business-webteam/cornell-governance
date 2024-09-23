<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Initial_Prompt' ) ) {
		class Initial_Prompt extends Prompt_Time {
			/**
			 * @var Initial_Prompt $instance holds the single instance of this class
			 * @access private
			 */
			protected static Initial_Prompt $instance;
			/**
			 * Construct this input
			 */
			protected function __construct( array $atts = array() ) {
				parent::__construct( array(
					'id'        => 'initial-prompt-time',
					'title'     => __( 'How many days before a review is due should the first prompt message be sent?', 'cornell/governance' ),
					'default'   => 60,
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Initial_Prompt
			 * @since   0.1
			 */
			public static function instance(): Initial_Prompt {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}