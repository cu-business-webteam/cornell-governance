<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Submenus {

	use Cornell\Governance\Helpers;

	class Page_List extends Base {
		/**
		 * @var Page_List $instance holds the single instance of this class
		 * @access private
		 */
		private static Page_List $instance;
		/**
		 * @var Tables\Page_List_Table $table holds the WP_List_Table that is part of this page
		 * @access protected
		 */
		protected Tables\Page_List_Table $table;

		/**
		 * Creates the Menu object
		 *
		 * @access private
		 * @since  0.1
		 */
		function __construct() {
			if ( ! is_admin() ) {
				return;
			}

			parent::__construct( array(
				'title'           => esc_html( __( 'Cornell Governance: Page Status List', 'cornell-governance' ) ),
				'menu_name'       => esc_html( __( 'Page Status', 'cornell-governance' ) ),
				'slug'            => 'cornell-governance-list',
				'per_page_option' => 'cornell/governance/page-list/items_per_page',
				'description'     => esc_html( __( 'View a sortable list of all governable content on this site, along with various governance information about each piece of content', 'cornell-governance' ) ),
			) );
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Page_List
		 * @since   0.1
		 */
		public static function instance(): Page_List {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Outputs the content of the Page List
		 *
		 * @access protected
		 * @return void
		 * @since  0.1
		 */
		protected function display() {
			printf( '<div class="wrap"><h2>%s</h2>', $this->title );
			$this->table->prepare_items();
			echo '<form method="get">';
			$this->do_search_box();
			$this->table->display();
			echo '</form>';
			print( '</div>' );
		}

		/**
		 * Adds the screen options to the page
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function add_options() {
			$this->table = new Tables\Page_List_Table();
		}

		/**
		 * Output the submenu page
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function do_submenu_page() {
			$this->display();
		}
	}
}