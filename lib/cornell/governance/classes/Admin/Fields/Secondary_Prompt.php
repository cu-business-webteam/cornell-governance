<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {
	if ( ! class_exists( 'Secondary_Prompt' ) ) {
		class Secondary_Prompt extends Prompt_Time {
			/**
			 * @var Secondary_Prompt $instance holds the single instance of this class
			 * @access private
			 */
			protected static Secondary_Prompt $instance;
			/**
			 * Construct this input
			 */
			protected function __construct( array $atts = array() ) {
				parent::__construct( array(
					'id'        => 'secondary-prompt-time',
					'title'     => __( 'How many days before a review is due should the second prompt message be sent?', 'cornell/governance' ),
					'default'   => 30,
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Secondary_Prompt
			 * @since   0.1
			 */
			public static function instance(): Secondary_Prompt {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}