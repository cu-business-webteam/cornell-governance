<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Submenus {

	use Cornell\Governance\Admin\Submenus\Reports\Compliance_Status;
	use Cornell\Governance\Helpers;

	class Steward_Dashboard extends Base {
		/**
		 * @var Steward_Dashboard $instance holds the single instance of this class
		 * @access private
		 */
		private static Steward_Dashboard $instance;
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
				'title'           => __( 'Cornell Governance: Steward Dashboard', 'cornell/governance' ),
				'menu_name'       => __( 'Your Pages', 'cornell/governance' ),
				'slug'            => 'cornell-governance-steward-dashboard',
				'per_page_option' => 'cornell/governance/steward-dashboard/items_per_page',
				'description'     => __( 'A series of reports specifically about content that you currently own or manage.', 'cornell/governance' ),
			) );

			$this->table = new Tables\Steward_Page_List();
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Steward_Dashboard
		 * @since   0.1
		 */
		public static function instance(): Steward_Dashboard {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Set the current user ID, so that report data will be limited to that user
		 *
		 * @param int $user the current value of the user ID (most likely 0)
		 *
		 * @access public
		 * @return int the user ID
		 * @since  0.1
		 */
		public function current_user( int $user ): int {
			return get_current_user_id();
		}

		/**
		 * Set the object properties
		 *
		 * @param array $attributes the properties to assign
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function set_properties( array $attributes ) {
			foreach ( $attributes as $key => $attribute ) {
				switch ( $key ) {
					case 'page' :
						$this->page = $attribute;
						break;
					default :
						break;
				}
			}

			$this->cap = 'edit_pages';
		}

		/**
		 * Outputs the content of the Page List
		 *
		 * @access protected
		 * @return void
		 * @since  0.1
		 */
		protected function display() {
			add_filter( 'cornell/governance/reports/current-user', array( $this, 'current_user' ) );

			printf( '<div class="wrap"><h2>%s</h2>', $this->title );
			print( '<div class="steward-dashboard-table-container">' );
			$this->table->prepare_items();
			$this->do_search_box();
			$this->table->display();
			print( '</div><div class="steward-dashboard-chart-container cornell-governance-data-charts">' );
			Compliance_Status::instance()->display();
			print( '</div></div>' );

			remove_filter( 'cornell/governance/reports/current-user', array( $this, 'current_user' ) );
		}

		/**
		 * Adds the screen options to the page
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function add_options() {
			$option = 'per_page';
			$args   = array(
				'label'   => __( 'Pages', 'cornell/governance' ),
				'default' => 50,
				'option'  => $this->per_page_option,
			);
			add_screen_option( $option, $args );

			$this->table = new Tables\Steward_Page_List();
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