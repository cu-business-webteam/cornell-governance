<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Taxonomies {

	use Cornell\Governance\Admin\Menu;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Audience' ) ) {
		class Audience extends Base {
			/**
			 * @var Audience $instance holds the single instance of this class
			 * @access private
			 */
			private static Audience $instance;

			protected function __construct() {
				$this->register_taxonomy();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Audience
			 * @since   0.1
			 */
			public static function instance(): Audience {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Register the taxonomy, with additional actions afterwards
			 *
			 * @access public
			 * @since  2023.05
			 * @return void
			 */
			public function register_taxonomy(): void {
				parent::register_taxonomy();

				add_action( 'admin_menu', array( $this, 'move_taxonomy_menu' ) );
				add_filter( 'parent_file', array( $this, 'highlight_governance_menu' ) );
				add_filter( 'submenu_file', array( $this, 'highlight_submenu' ), 10, 2 );
			}

			/**
			 * Move the taxonomy menu to the Governance menu rather than the Pages menu
			 *
			 * @access public
			 * @since  2023.05
			 * @return void
			 */
			public function move_taxonomy_menu() {
				add_submenu_page(
					Menu::instance()->get_page_slug(),
					$this->get_args()['label'],
					$this->get_args()['label'],
					Plugin::instance()->get_capability(),
					'edit-tags.php?taxonomy=audience&post_type=page'
				);
			}

			/**
			 * Make sure the Governance menu is highlighted, rather than the Pages menu, when
			 *      someone is editing Audiences
			 *
			 * @param string|null $parent_file the slug for the existing highlighted menu
			 *
			 * @access public
			 * @return string|null
			 *@since  2023.05
			 */
			public function highlight_governance_menu( ?string $parent_file ): ?string {
				if ( get_current_screen()->taxonomy == $this->get_handle() ) {
					return Menu::instance()->get_page_slug();
				}

				return $parent_file;
			}

			/**
			 * Highlight the Audiences submenu when it's selected
			 *
			 * @param string|null $submenu_file the current submenu slug
			 * @param string|null $parent_file the current menu slug
			 *
			 * @access public
			 * @since  2023.05
			 * @return string|null the updated submenu slug
			 */
			public function highlight_submenu( ?string $submenu_file, ?string $parent_file='' ): ?string {
				if ( $submenu_file == 'edit-tags.php?taxonomy=audience&amp;post_type=page' ) {
					global $plugin_page;
					$plugin_page = Menu::instance()->get_page_slug();

					return 'edit-tags.php?taxonomy=audience&post_type=page';
				}

				return $submenu_file;
			}

			/**
			 * Returns the handle for the taxonomy
			 *
			 * @access protected
			 * @return string
			 * @since  0.1
			 */
			protected function get_handle(): string {
				return 'audience';
			}

			/**
			 * Returns the array of post types to associate with this taxonomy
			 *
			 * @access protected
			 * @return array the array of post type handles
			 * @since  0.1
			 */
			protected function get_post_types(): array {
				$types = Plugin::instance()->get_post_types();
				if ( empty( $types ) || ! is_array( $types ) ) {
					$types = array( 'page' );
				}

				return $types;
			}

			/**
			 * Returns the array of arguments for the taxonomy
			 *
			 * @access protected
			 * @return array the array of arguments
			 * @since  0.1
			 */
			protected function get_args(): array {
				return array(
					'label'                 => __( 'Audiences', 'cornell/governance' ),
					'labels'                => $this->get_labels(),
					'public'                => true,
					'publicly_queryable'    => false,
					'hierarchical'          => true,
					'show_ui'               => current_user_can( Plugin::instance()->get_capability() ),
					'show_in_menu'          => false,
					'show_in_nav_menus'     => false,
					'query_var'             => true,
					'rewrite'               => false,
					'show_admin_column'     => false,
					'show_in_rest'          => false,
					'show_in_quick_edit'    => false,
					'meta_box_cb'           => false,
				);
			}

			/**
			 * Returns the array of labels for this taxonomy
			 *
			 * @access protected
			 * @return array the array of labels
			 * @since  0.1
			 */
			protected function get_labels(): array {
				$labels = array(
					'name'          => esc_html__( 'Audiences', 'cornell/governance' ),
					'singular_name' => esc_html__( 'Audience', 'cornell/governance' ),
				);

				return $this->populate_labels( $labels );
			}
		}
	}
}