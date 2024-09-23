<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\General {
	if ( ! class_exists( 'Initial_Prompt' ) ) {
		class Initial_Prompt extends \Cornell\Governance\Emails\Initial_Prompt {
			/**
			 * @var Initial_Prompt $instance holds the single instance of this class
			 * @access private
			 */
			protected static Initial_Prompt $instance;

			/**
			 * Construct our Initial_Prompt object
			 */
			protected function __construct() {
				parent::__construct();

				$prompt_time = get_option( 'cornell-governance-initial-prompt-time', 60 );

				$this->set_vars( array(
					'subject' => sprintf( __( '[Website Report] Pages needing review in the next %d days', 'cornell/governance' ), $prompt_time )
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