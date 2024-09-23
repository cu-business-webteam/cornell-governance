<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;

	if ( ! class_exists( 'Tertiary_Prompt' ) ) {
		class Tertiary_Prompt extends Prompt {
			/**
			 * Construct our object
			 */
			protected function __construct() {
				parent::__construct();

				$prompt_time = get_option( 'cornell-governance-tertiary-prompt-time', 7 );

				$this->set_vars( array(
					'subject' => sprintf( __( '[URGENT] These pages require your review in the next %d days', 'cornell/governance' ), $prompt_time )
				) );
			}
		}
	}
}