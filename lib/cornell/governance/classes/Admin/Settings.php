<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin {

	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Settings' ) ) {
		class Settings {
			/**
			 * @var Settings $instance holds the single instance of this class
			 * @access private
			 */
			private static Settings $instance;
			/**
			 * @var string $namespace the current namespace name
			 * @access private
			 */
			private static string $namespace;

			/**
			 * Creates the Admin object
			 *
			 * @access private
			 * @since  0.1
			 */
			private function __construct() {
				self::$namespace = __NAMESPACE__;
				add_action( 'admin_init', array( $this, 'register_setting' ) );
				add_action( 'admin_menu', array( $this, 'add_options_page' ) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Settings
			 * @since   0.1
			 */
			public static function instance(): Settings {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Enqueue the necessary admin styles and scripts for this plugin
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function admin_enqueue_scripts(): void {
				$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
				wp_enqueue_style( 'cornell-governance-admin', Helpers::plugins_url( '/dist/css/cornell-governance-admin' . $min . '.css' ), array(), Plugin::$version, 'all' );
				wp_enqueue_script( 'cornell-governance-admin', Helpers::plugins_url( '/dist/js/cornell-governance-admin' . $min . '.js' ), array(), Plugin::$version, true );
			}

			/**
			 * Register our Setting
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function register_setting(): void {
				add_settings_section(
					'cornell-governance-settings',
					__( 'Cornell Page Governance Settings', 'cornell/governance' ),
					array( $this, 'do_settings_section' ),
					'cornell-governance'
				);

				add_settings_section(
					'cornell-governance-settings-change-form',
					__( 'Change Form Settings', 'cornell/governance' ),
					array( $this, 'do_change_settings_section' ),
					'cornell-governance'
				);

				add_settings_section(
					'cornell-governance-settings-prompts',
					__( 'Email Prompt Settings', 'cornell/governance' ),
					array( $this, 'do_prompt_settings_section' ),
					'cornell-governance'
				);

				foreach (
					array(
						'Capability',
						'Managing_Office',
						'Post_Types',
						'Default_Tasks',
						/*'Message_Content',*/
					) as $c
				) {
					$class = self::$namespace . '\\Fields\\' . $c;
					$class::instance();
				}

				foreach (
					array(
                        'Change_Form_Active',
                        'Change_Form_Link_Text',
						'Change_Form_URL',
                        'Change_Form_Props',
					) as $c
				) {
					$class = self::$namespace . '\\Fields\\' . $c;
					$class::instance();
				}

				foreach (
					array(
						'Initial_Prompt',
						'Secondary_Prompt',
						'Tertiary_Prompt'
					) as $c
				) {
					$class = self::$namespace . '\\Fields\\' . $c;
					$class::instance();
				}
			}

			/**
			 * Register our settings page
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function add_options_page(): void {
				/*add_options_page(
					__( 'Page Governance', 'cornell/governance' ),
					__( 'Page Governance', 'cornell/governance' ),
					'delete_users',
					'cornell-governance',
					array( $this, 'do_options_page' )
				);*/

				add_submenu_page(
					Menu::instance()->get_page_slug(),
					__( 'Governance Settings', 'cornell/governance' ),
					__( 'Governance Settings', 'cornell/governance' ),
					'delete_users',
					'cornell-governance-settings',
					array( $this, 'do_options_page' )
				);
			}

			/**
			 * Output our options page
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function do_options_page(): void {
				?>
                <div class="wrap">
                    <h2><?php _e( 'Page Governance', 'cornell/governance' ) ?></h2>
                    <form action="options.php" method="POST">
						<?php settings_fields( 'cornell-governance' ) ?>
						<?php do_settings_sections( 'cornell-governance' ) ?>
						<?php submit_button() ?>
                    </form>
                </div>
				<?php
			}

			/**
			 * Validate and sanitize our settings
			 *
			 * @param mixed $settings
			 *
			 * @access public
			 * @return mixed
			 * @since  0.1
			 */
			public function sanitize_settings( $settings ) {
				return $settings;
			}

			/**
			 * Output the Settings Section for this page
			 *
			 * @param array $args Display arguments.
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function do_settings_section( array $args ): void {
				_e( 'General settings for the Cornell Governance plugin', 'cornell/governance' );
			}

			/**
			 * Output the Settings Section for change form options
             *
             * @param array $args Display arguments
             *
             * @access public
             * @return void
             * @since  0.1
			 */
            public function do_change_settings_section( array $args ) {
                _e( 'Change Form Options', 'cornell/governance' );
            }

			/**
			 * Output the Settings Section for email prompt timing
			 *
			 * @param array $args Display arguments
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function do_prompt_settings_section( array $args ): void {
				_e( 'Email message timing', 'cornell/governance' );
			}
		}
	}
}
