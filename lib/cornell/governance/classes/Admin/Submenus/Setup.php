<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Submenus {

	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	class Setup extends Base {
		/**
		 * @var Setup $instance holds the single instance of this class
		 * @access private
		 */
		private static Setup $instance;

		/**
		 * Creates the Setup object
		 *
		 * @access private
		 * @since  0.1
		 */
		function __construct() {
			if ( ! is_admin() ) {
				return;
			}

			parent::__construct( array(
				'title'       => __( 'Cornell Governance: Plugin Setup', 'cornell/governance' ),
				'menu_name'   => __( 'Plugin Setup', 'cornell/governance' ),
				'slug'        => 'cornell-governance-setup',
				'description' => __( 'Instructions explaining how to configure this plugin.', 'cornell/governance' ),
			) );
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Setup
		 * @since   0.1
		 */
		public static function instance(): Setup {
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
			printf( '<div class="wrap"><h2>%s</h2><div class="cornell-governance-documentation">', $this->title );

			ob_start();
			include_once( Helpers::plugins_path( '/README.md' ) );
			$readme = ob_get_clean();
			/*$readme = file_get_contents( Helpers::plugins_url( '/README.md' ) );*/

			$audience_link = admin_url( '/edit-tags.php?taxonomy=audience&post_type=page' );
			$settings_link = admin_url( '/admin.php?page=cornell-governance-settings' );

			$search = array(
				'Governance -> Audiences' => '[Governance -> Audiences](' . $audience_link . ')',
				'Governance -> Governance Settings' => '[Governance -> Governance Settings](' . $settings_link . ')',
				'(assets/' => '(' . Helpers::plugins_url( '/assets/' ),
				'the plugin settings' => '[the plugin settings](' . $settings_link . ')',
			);

			$readme = str_replace( array_keys( $search ), array_values( $search ), $readme );

			$Parsedown = new \Parsedown();
			echo $Parsedown->text( $readme );

			print( '</div></div>' );
		}

		/**
		 * Adds the screen options to the page
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function add_options() {
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
			parent::set_properties( $attributes );
		}

	}
}