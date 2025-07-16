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
					'cornell-governance',
					array(
						'before_section' => sprintf( '<div id="panel-%1$d" role="tabpanel" class="cornell-governance-tabpanel" tabindex="0" aria-labelledby="tab-%1$d">', 1 ),
						'after_section'  => '</div>',
					)
				);

				add_settings_section(
					'cornell-governance-settings-archive',
					__( 'Wayback Integration', 'cornell/governance' ),
					array( $this, 'do_wayback_settings_section' ),
					'cornell-governance',
					array(
						'before_section' => sprintf( '<div id="panel-%1$d" role="tabpanel" class="cornell-governance-tabpanel" tabindex="0" aria-labelledby="tab-%1$d" hidden>', 2 ),
						'after_section'  => '</div>',
					)
				);

				add_settings_section(
					'cornell-governance-settings-change-form',
					__( 'Change Form Settings', 'cornell/governance' ),
					array( $this, 'do_change_settings_section' ),
					'cornell-governance',
					array(
						'before_section' => sprintf( '<div id="panel-%1$d" role="tabpanel" class="cornell-governance-tabpanel" tabindex="0" aria-labelledby="tab-%1$d" hidden>', 3 ),
						'after_section'  => '</div>',
					)
				);

				add_settings_section(
					'cornell-governance-settings-prompts',
					__( 'Email Prompt Settings', 'cornell/governance' ),
					array( $this, 'do_prompt_settings_section' ),
					'cornell-governance',
					array(
						'before_section' => sprintf( '<div id="panel-%1$d" role="tabpanel" class="cornell-governance-tabpanel" tabindex="0" aria-labelledby="tab-%1$d" hidden>', 4 ),
						'after_section'  => '</div>',
					)
				);

				$general = array(
					'Capability',
					'Managing_Office',
					'Post_Types',
					'Default_Tasks',
					/*'Message_Content',*/
					'Mark_For_Deletion',
					'Frontend_Compliance',
				);

				$archive = array(
					'Archive_Active',
					'Archive_URL_Search',
					'Archive_URL_Replace',
					'Archive_Log_Limit',
				);

				foreach ( $general as $c ) {
					$class = self::$namespace . '\\Fields\\' . $c;
					$class::instance();
				}

				/*foreach ( $archive as $c ) {
					$class = self::$namespace . '\\Fields\\' . $c;
					$class::instance();
				}*/

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
						'Email_Active',
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
						<?php echo '<div class="tabs cornell-governance-tablist">'; ?>
						<?php $this->do_tab_handles(); ?>
						<?php do_settings_sections( 'cornell-governance' ) ?>
						<?php echo '</div>'; ?>
						<?php submit_button( __( 'Save All Settings', 'cornell/governance' ) ); ?>
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
			 * Output the Settings Section for the Wayback integration
			 *
			 * @param array $args Display arguments
			 *
			 * @access public
			 * @return void
			 * @since  0.6.2
			 */
			public function do_wayback_settings_section( array $args ): void {
				_e( '<p>Settings for Wayback Machine integration and the Internet Archive</p>', 'cornell/governance' );
                _e( '<p><em>The Wayback Machine integration is still under development, and is experimental. Please use caution when activating this feature.</em></p>', 'cornell/governance' );
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
			public function do_prompt_settings_section( array $args ) {
				_e( 'Email message timing', 'cornell/governance' );
			}

			/**
			 * Output the tab handles for the admin settings page
			 *
			 * @access protected
			 * @return void
			 * @since  0.6.3
			 */
			protected function do_tab_handles() {
				$tablist = array(
					__( 'General Settings', 'cornell/governance' ),
					__( 'Wayback Integration Settings', 'cornell/governance' ),
					__( 'Change Form Settings', 'cornell/governance' ),
					__( 'Email Settings', 'cornell/governance' ),
				);

				$handles = array();

				for ( $i = 0; $i < count( $tablist ); $i ++ ) {
					$selected  = $i === 0 ? 'true' : 'false';
					$handles[] = sprintf( '<button
      role="tab"
      aria-selected="%3$s"
      aria-controls="panel-%1$d"
      id="tab-%1$d"
      tabindex="0">
      %2$s
    </button>', ( $i + 1 ), $tablist[ $i ], $selected );
				}

				printf(
					'<div role="tablist" aria-label="%s">%s</div>',
					__( 'Settings Areas', 'cornell/governance' ),
					implode( '', $handles )
				);
			}
		}
	}
}
