<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails {
	if ( ! class_exists( '\Cornell\Governance\Emails\Initial_Prompt' ) ) {
		class Initial_Prompt extends Prompt {
			/**
			 * Construct our Initial_Prompt object
			 */
			protected function __construct() {
				parent::__construct();

				$prompt_time = get_option( 'cornell-governance-initial-prompt-time', 60 );

				$this->set_vars( array(
					'subject' => sprintf( esc_html( __( '[Website Report] Pages needing review in the next %d days', 'cornell-governance' ) ), $prompt_time )
				) );
			}
		}
	}
}