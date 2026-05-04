<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Submenus {

	use Cornell\Governance\Admin\Submenus\Tables\Unreviewed_Table;
	use Cornell\Governance\Helpers;

	class Unreviewed extends Base {
		/**
		 * @var Unreviewed $instance holds the single instance of this class
		 * @access private
		 */
		private static Unreviewed $instance;
		/**
		 * @var Unreviewed_Table $table holds the WP_List_Table that is part of this page
		 * @access protected
		 */
		protected Unreviewed_Table $table;

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
				'title'           => esc_html( __( 'Cornell Governance: Unreviewed Pages', 'cornell-governance' ) ),
				'menu_name'       => esc_html( __( 'Unreviewed Pages', 'cornell-governance' ) ),
				'slug'            => 'cornell-governance-unreviewed',
				'per_page_option' => 'cornell/governance/unreviewed/items_per_page',
				'description'     => esc_html( __( 'A list of all governable content on this site that has not been reviewed for governance purposes, yet.', 'cornell-governance' ) ),
			) );
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Unreviewed
		 * @since   0.1
		 */
		public static function instance(): Unreviewed {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Outputs the content of the Unreviewed Report
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
			$this->table = new Unreviewed_Table();
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