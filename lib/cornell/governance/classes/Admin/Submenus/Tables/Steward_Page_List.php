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
	if ( ! class_exists( 'Steward_Page_List' ) ) {
		class Steward_Page_List extends Page_List_Table {
			function __construct( $args = array() ) {
				parent::__construct( $args );
			}

			function add_author_arg( $args ) {
				$user           = get_current_user_id();
				$args['author'] = $user;

				return $args;
			}

			function get_data(): array {
				add_filter( 'cornell/governance/page-list-table/query-args', array( $this, 'add_author_arg' ) );
				$data = parent::get_data();
				remove_filter( 'cornell/governance/page-list-table/query-args', array( $this, 'add_author_arg' ) );

				return $data;
			}
		}
	}
}