<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin {

	class Meta_Boxes {
		/**
		 * @var Meta_Boxes $instance holds the single instance of this class
		 * @access private
		 */
		private static Meta_Boxes $instance;
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
				'Meta_Boxes\Info',
				'Meta_Boxes\Notes',
				'Meta_Boxes\Revisions',
			);

			add_action( 'load-post.php', array( $this, 'load_meta_boxes' ) );
			add_action( 'load-post-new.php', array( $this, 'load_meta_boxes' ) );
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Meta_Boxes
		 * @since   0.1
		 */
		public static function instance(): Meta_Boxes {
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
		public function load_meta_boxes(): void {
			foreach ( $this->classes as $class ) {
				$classname = $this->namespace . '\\' . $class;
				$classname::instance();
			}
		}
	}
}