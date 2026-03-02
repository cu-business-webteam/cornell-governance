<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Submenus {

	use Cornell\Governance\Admin\Submenus\Reports\Compliance_Status;
	use Cornell\Governance\Admin\Submenus\Reports\Liaison_Status;
	use Cornell\Governance\Admin\Submenus\Reports\Portfolio_Compliance;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	class Liaison_Dashboard extends Base {
		/**
		 * @var Liaison_Dashboard $instance holds the single instance of this class
		 * @access private
		 */
		private static Liaison_Dashboard $instance;
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
				'title'           => __( 'Cornell Governance: Liaison Dashboard', 'cornell/governance' ),
				'menu_name'       => __( 'Liaison Dashboard', 'cornell/governance' ),
				'slug'            => 'cornell-governance-liaison-dashboard',
				'per_page_option' => 'cornell-governance-liaison-dashboard-items_per_page',
				'description'     => __( 'A series of reports specifically about content that you currently manage as the liaison.', 'cornell/governance' ),
			) );
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Liaison_Dashboard
		 * @since   0.1
		 */
		public static function instance(): Liaison_Dashboard {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Add the appropriate meta query to only retrieve results for this specific liaison
		 *
		 * @param array $args the existing query arguments
		 *
		 * @access public
		 * @return array the updated query arguments
		 * @since  0.6.5
		 */
		public function add_meta_query( array $args ): array {
			$user           = wp_get_current_user();
			if ( ! is_a( $user, 'WP_User' ) ) {
				return $args;
			}

			$email = $user->user_email;
			$length = strlen( $email );
			$search = sprintf( 's:7:"liaison";s:%1$d:"%2$s";', $length, $email );

			$args['meta_query'] = array(
				array(
					'key' => \Cornell\Governance\Plugin::INFO_META_KEY,
					'compare' => 'LIKE',
					'value' => $search,
				)
			);

			return $args;
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

			$this->cap = Plugin::instance()->get_capability();
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
			Portfolio_Compliance::instance()->display();
			print( '<div class="steward-dashboard-table-container">' );
			$this->table->prepare_items();
			echo '<form method="get">';
			printf( '<h3>%s</h3>', __( 'Full Liaison Page List', 'cornell/governance' ) );
			$this->do_search_box();
			$this->table->display();
			echo '</form>';
			/*print( '</div><div class="steward-dashboard-chart-container cornell-governance-data-charts">' );
			Liaison_Status::instance()->display();*/
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
			$this->table = new Tables\Liaison_Page_List();
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