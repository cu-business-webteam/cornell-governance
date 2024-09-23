<?php
namespace Cornell\Governance\Admin\Submenus {

	use Cornell\Governance\Admin\Admin;
	use Cornell\Governance\Admin\Menu;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Base' ) ) {
		abstract class Base {
			/**
			 * @var string $hook the menu/page hook
			 */
			protected string $hook;
			/**
			 * @var string $page the hook for the parent menu page
			 */
			protected string $page;
			/**
			 * @var string $cap the capability that controls access to this menu page
			 */
			protected string $cap;
			/**
			 * @var string $title the page title
			 */
			protected string $title;
			/**
			 * @var string $menu_name the menu title
			 */
			protected string $menu_name;
			/**
			 * @var string $slug the slug to be registered
			 */
			protected string $slug;
			/**
			 * @var string $per_page_option the option name for per_page setting
			 */
			protected string $per_page_option;
			/**
			 * @var string $description a full-text description of this submenu page
			 */
			protected string $description;

			/**
			 * Construct the Submenu object
			 *
			 * @param array $atts the list of properties to add to this menu object
			 */
			public function __construct( array $atts ) {
				foreach ( $atts as $key => $val ) {
					switch( $key ) {
						case 'cap' :
						case 'page' :
						case 'title' :
						case 'menu_name' :
						case 'slug' :
						case 'per_page_option' :
						case 'description' :
							$this->{$key} = $val;
							break;
						default :
							break;
					}
				}
			}

			/**
			 * Set the object properties
			 *
			 * @param array $attributes the properties to assign
			 *
			 * @access public
			 * @since  0.1
			 * @return void
			 */
			public function set_properties( array $attributes ) {
				foreach ( $attributes as $key => $attribute ) {
					switch( $key ) {
						case 'cap' :
							$this->cap = $attribute;
							break;
						case 'page' :
							$this->page = $attribute;
							break;
						default :
							break;
					}
				}
			}

			/**
			 * Run the registration action
			 *
			 * @access public
			 * @since  0.1
			 * @return void
			 */
			public function register() {
				$this->add_submenu_page();
			}

			/**
			 * Register the sub-menu
			 */
			public function add_submenu_page() {
				$this->hook = add_submenu_page(
					$this->page,
					$this->title,
					$this->menu_name,
					$this->cap,
					$this->slug,
					array( $this, 'do_submenu_page' )
				);

				/*add_action( 'load-' . $this->hook, array( $this, 'add_options' ) );*/
			}

			/**
			 * Output the submenu page
			 *
			 * @access public
			 * @since  0.1
			 * @return void
			 */
			public function do_submenu_page() {
				Admin::instance()->admin_enqueue_scripts();

				$this->display();
			}

			/**
			 * Output the content of the submenu page
			 */
			abstract protected function display();

			/**
			 * Register the screen options
			 *
			 * @access public
			 * @since  0.1
			 * @return void
			 */
			abstract public function add_options();

			/**
			 * Save the per_page setting
			 *
			 * @param mixed  $screen_option The value to save instead of the option value.
			 *                              Default false (to skip saving the current option).
			 * @param string $option        The option name.
			 * @param int    $value         The option value.
			 *
			 * @access public
			 * @since  0.1
			 * @return int the per_page option value
			 */
			public function set_per_page_option( $screen_option, string $option, int $value ): int {
				if ( $this->per_page_option === $option ) {
					$value = (int) $value;

					if ( $value < 1 || $value > 999 ) {
						return $screen_option;
					}

					return $value;
				}

				return $screen_option;
			}

			/**
			 * Output the table search form
			 *
			 * @access protected
			 * @since  0.1
			 * @return void
			 */
			protected function do_search_box() {
				?>
				<form method="post">
					<input type="hidden" name="page" value="<?php echo $this->hook . '_table' ?>" />
					<?php $this->table->search_box( __( 'Search:', 'cornell/governance' ), 'search_id') ?>
				</form>
				<?php
			}

			/**
			 * Output any content for this submenu that belongs on the main menu page
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function main_menu_output() {
				if ( ! current_user_can( $this->cap ) ) {
					return;
				}

				printf(
					'<div class="governance-box"><h3><a href="%3$s">%1$s</a></h3><p>%2$s</p></div>',
					$this->title,
					$this->description,
					add_query_arg( 'page', $this->slug, admin_url( '/admin.php' ) )
				);
			}
		}
	}
}