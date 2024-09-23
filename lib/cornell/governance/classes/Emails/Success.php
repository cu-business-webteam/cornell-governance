<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails {
	if ( ! class_exists( 'Success' ) ) {
		abstract class Success extends Base {
			protected function __construct();
		}
	}
}