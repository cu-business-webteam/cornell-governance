<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin {

	use Cornell\Governance\Admin\Submenus\Page_List;
	use Cornell\Governance\Helpers;

	class Menu {
		/**
		 * @var Menu $instance holds the single instance of this class
		 * @access private
		 */
		private static Menu $instance;
		/**
		 * @var string $page the handle for the main admin menu page for this plugin
		 * @access protected
		 */
		protected string $page;
		/**
		 * @var string $cap the capability that should have access to the pages
		 * @access protected
		 */
		protected string $cap;
		/**
		 * @var string $namespace the namespace of this class
		 * @access protected
		 */
		protected string $namespace;
		/**
		 * @var array $submenus holds the list of submenus classes to invoke for this menu
		 * @access protected
		 */
		protected array $submenus;

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

			$this->namespace = __NAMESPACE__;

			$this->submenus = array(
				'Page_List',
				'Unreviewed',
				'Reports',
				'Steward_Dashboard',
				'Page_Meta',
				'Email_Test',
				'Setup',
			);

			add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Menu
		 * @since   0.1
		 */
		public static function instance(): Menu {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Retrieve and return the page slug for this menu page
		 *
		 * @access public
		 * @since  2023.04
		 * @return string the page slug
		 */
		public function get_page_slug(): string {
			return $this->page;
		}

		/**
		 * Add the appropriate admin menu pages for this plugin
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		public function add_menu_page() {
			/*if ( Helpers::is_plugin_active( 'cornell-data/cornell-data.php' ) ) {
				$this->page = \Cornell_Data\Admin\Report::PAGE_MAIN;
				$this->cap = \Cornell_Data\Admin\Report::OPTIONS_USER_CAP;
			} else {*/
				$this->page = 'cornell-governance';
				$this->cap = 'manage_options';

				add_menu_page(
					__( 'Cornell Business: Governance', 'cornell/governance' ),
					__( 'Governance', 'cornell/governance' ),
					'edit_pages',
					$this->page,
					array( $this, 'do_menu_page' ),
					'dashicons-bell'
				);
			/*}*/

			$this->add_submenu_pages();
		}

		/**
		 * Register the sub-menu pages that we need
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		public function add_submenu_pages() {
			foreach ( $this->submenus as $class ) {
				$classname = $this->namespace . '\Submenus\\' . $class;
				$classname::instance()->set_properties( array(
					'cap' => $this->cap,
					'page' => $this->page,
				) );
				$classname::instance()->register();
			}
		}

		/**
		 * Output the main admin menu page (if needed)
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		public function do_menu_page() {
			Admin::instance()->admin_enqueue_scripts();

			print( '<div class="wrap">' );
			printf( '<h2>%s</h2>', __( 'Governance Reports', 'cornell/governance' ) );
			print( '<div class="governance-menu-boxes">' );
			foreach ( $this->submenus as $class ) {
				$classname = $this->namespace . '\Submenus\\' . $class;

				/* Stop the "Content Governance Meta" link from appearing on the main Governance page, since
					it requires a page ID in order to show anything */
				if ( stristr( $classname, 'Page_Meta' ) ) {
					continue;
				}

				$classname::instance()->set_properties( array(
					'cap' => $this->cap,
					'page' => $this->page,
				) );
				$classname::instance()->main_menu_output();
			}
			print( '</div>' );
			print( '</div>' );
		}
	}
}