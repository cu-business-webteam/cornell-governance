<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\General {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;

	if ( ! class_exists( 'Secondary_Prompt' ) ) {
		class Secondary_Prompt extends \Cornell\Governance\Emails\Secondary_Prompt {
			/**
			 * @var Secondary_Prompt $instance holds the single instance of this class
			 * @access private
			 */
			protected static Secondary_Prompt $instance;

			/**
			 * Construct our object
			 */
			protected function __construct() {
				parent::__construct();

				$prompt_time = get_option( 'cornell-governance-secondary-prompt-time', 30 );

				$this->set_vars( array(
					'subject' => sprintf( __( '[Website Report] Pages needing review in the next %d days', 'cornell/governance' ), $prompt_time )
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