<?php
/*
Plugin Name: Cornell Business: In-Page Governance
Description: Allows tracking and adding notes about the content, purpose, audiences, etc of individual pages
Version: 0.6.4
Author: Curtiss Grymala
License: GPL2
Text Domain: cornell/governance
Domain Path: /lang/
Internal Plugin: Yes
*/

namespace {
	if ( ! defined( 'CORNELL_DEBUG' ) ) {
		define( 'CORNELL_DEBUG', false );
	}

	if ( ! defined( 'CORNELL_GOVERNANCE_EMAIL_TO' ) ) {
		define( 'CORNELL_GOVERNANCE_EMAIL_TO', null );
	}

	require_once __DIR__ . '/vendor/autoload.php';

	if ( file_exists( __DIR__ . '/.env' ) ) {
		$dotenv = Dotenv\Dotenv::createImmutable( __DIR__ );
		$dotenv->ifPresent('CORNELL_GOVERNANCE_REPO_IS_CUSTOM_GITLAB')->isBoolean();
		$dotenv->load();
	} else if ( file_exists( __DIR__ . '/.env.default' ) ) {
		$dotenv = Dotenv\Dotenv::createImmutable( __DIR__,'.env.default');
		$dotenv->ifPresent('CORNELL_GOVERNANCE_REPO_IS_CUSTOM_GITLAB')->isBoolean();
		$dotenv->load();
	}

	if ( file_exists( __DIR__ . '/cornell-governance-config.php' ) ) {
		require_once __DIR__ . '/cornell-governance-config.php';
	}
}

namespace Cornell\Governance {
	add_action( 'after_setup_theme', '\Cornell\Governance\init_plugin' );

	function init_plugin() {
		if ( ! isset( $cornell_governance ) || ! is_a( $cornell_governance, '\Cornell\Governance\Plugin' ) ) {
			$GLOBALS['cornell_governance'] = Plugin::instance();
		}

		load_plugin_textdomain();
	}

	function load_plugin_textdomain() {
		\load_plugin_textdomain( 'cornell/governance', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
}