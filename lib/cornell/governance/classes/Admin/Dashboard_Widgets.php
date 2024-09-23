<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin {

	class Dashboard_Widgets {
		/**
		 * @var Dashboard_Widgets $instance holds the single instance of this class
		 * @access private
		 */
		private static Dashboard_Widgets $instance;
		/**
		 * @var array $classes a list of the class names to instantiate for meta boxes
		 * @access public
		 */
		public array $classes;
		/**
		 * @var string $namespace the current namespace for this class
		 * @access public
		 */
		public string $namespace;

		/**
		 * Creates the Meta_Boxes object
		 *
		 * @access private
		 * @since  0.1
		 */
		function __construct() {
			if ( ! is_admin() ) {
				return;
			}

			$this->namespace = __NAMESPACE__;
			$this->classes = array(
				'Dashboard_Widgets\Compliance',
			);

			add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Dashboard_Widgets
		 * @since   0.1
		 */
		public static function instance(): Dashboard_Widgets {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Register the necessary meta boxes
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		public function add_dashboard_widgets(): void {
			foreach ( $this->classes as $class ) {
				$classname = $this->namespace . '\\' . $class;
				$classname::instance();
			}
		}
	}
}