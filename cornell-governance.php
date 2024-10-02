<?php
/*
Plugin Name: Cornell Business: In-Page Governance
Description: Allows tracking and adding notes about the content, purpose, audiences, etc of individual pages
Version: 0.4.7
Author: Curtiss Grymala
License: GPL2
Text Domain: cornell/governance
Domain Path: /lang/
Internal Plugin: Yes
*/

namespace {
	use Symfony\Component\Dotenv\Dotenv;

	if ( ! defined( 'CORNELL_DEBUG' ) ) {
		define( 'CORNELL_DEBUG', false );
	}

	if ( ! defined( 'CORNELL_GOVERNANCE_EMAIL_TO' ) ) {
		define( 'CORNELL_GOVERNANCE_EMAIL_TO', null );
	}

	if ( file_exists( __DIR__ . '/.env' ) ) {
		require_once __DIR__ . '/vendor/autoload.php';
		$dotenv = new Dotenv(  );
		$dotenv->load( __DIR__ . '/.env' );
	}
}

namespace Cornell\Governance {
	if ( ! isset( $cornell_governance ) || ! is_a( $cornell_governance, '\Cornell\Governance\Plugin' ) ) {
		$GLOBALS['cornell_governance'] = Plugin::instance();
	}

	add_action( 'plugins_loaded', 'Cornell\Governance\load_plugin_textdomain' );

	function load_plugin_textdomain() {
		\load_plugin_textdomain( 'cornell/governance', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
}