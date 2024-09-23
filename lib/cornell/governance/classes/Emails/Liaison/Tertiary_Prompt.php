<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\Liaison {

	if ( ! class_exists( 'Tertiary_Prompt' ) ) {
		class Tertiary_Prompt extends \Cornell\Governance\Emails\Tertiary_Prompt {
			/**
			 * @var Tertiary_Prompt $instance holds the single instance of this class
			 * @access private
			 */
			protected static Tertiary_Prompt $instance;

			/**
			 * Construct our object
			 */
			protected function __construct() {
				parent::__construct();

				$prompt_time = get_option( 'cornell-governance-tertiary-prompt-time', 7 );

				$this->set_vars( array(
					'subject' => sprintf( __( '[Liaison Report] [URGENT] These pages require your review in the next %d days', 'cornell/governance' ), $prompt_time )
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