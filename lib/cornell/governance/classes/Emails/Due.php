<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Due' ) ) {
		class Due extends Prompt {
			/**
			 * Construct our object
			 */
			protected function __construct() {
				parent::__construct();

				$this->set_vars( array(
					'subject' => __( '[DUE TODAY] These pages require your immediate review', 'cornell/governance' ),
					'headers' => array(
						'Priority: Urgent',
						'X-Priority: 1 (Highest)',
						'X-MSMail-Priority: High',
						'Importance: High',
					),
				) );
			}
		}
	}
}