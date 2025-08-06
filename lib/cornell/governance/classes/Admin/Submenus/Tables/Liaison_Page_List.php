<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}

	// Loading table class
	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}
}

namespace Cornell\Governance\Admin\Submenus\Tables {
	use \Cornell\Governance\Admin\Submenus\Liaison_Dashboard;

	if ( ! class_exists( 'Liaison_Page_List' ) ) {
		class Liaison_Page_List extends Page_List_Table {
			function __construct( $args = array() ) {
				$args = array(
					'singular' => 'liaison-page-list',
					'plural' => 'liaison-page-lists',
					'ajax' => false,
				);

				parent::__construct( $args );
			}

			function get_data(): array {
				add_filter( 'cornell/governance/page-list-table/query-args', array( Liaison_Dashboard::instance(), 'add_meta_query' ) );
				$data = parent::get_data();
				remove_filter( 'cornell/governance/page-list-table/query-args', array( Liaison_Dashboard::instance(), 'add_meta_query' ) );

				return $data;
			}
		}
	}
}