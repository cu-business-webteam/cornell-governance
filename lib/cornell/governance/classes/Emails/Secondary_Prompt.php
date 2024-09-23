<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;

	if ( ! class_exists( 'Secondary_Prompt' ) ) {
		class Secondary_Prompt extends Prompt {
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
		}
	}
}