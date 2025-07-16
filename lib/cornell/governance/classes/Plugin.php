<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance {

	use Cornell\Governance\Admin\Admin;
	use Cornell\Governance\Wayback\Trigger;
	use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

	if ( ! class_exists( 'Plugin' ) ) {
		class Plugin {
			const INFO_META_KEY = 'cornell/governance/information';
			const NOTES_META_KEY = 'cornell/governance/notes';
			const REVISIONS_META_KEY = 'cornell/governance/revisions';

			/**
			 * @var Plugin $instance holds the single instance of this class
			 * @access private
			 */
			private static Plugin $instance;
			/**
			 * @var string $version holds the version number for the plugin
			 * @access public
			 */
			public static string $version = '0.6.3';
			/**
			 * @var string $capability the WP capability required to access settings
			 * @access private
			 */
			private string $capability = '';
			/**
			 * @var string $managing_office the name of the office that manages the site
			 * @access private
			 */
			private string $managing_office = '';
			/**
			 * @var bool $change_form_active whether or not a link to a change request form should be included
			 * @access private
			 */
			private bool $change_form_active = false;
			/**
			 * @var string $change_form_text the text that should be used for the change form link
			 * @access private
			 */
			private string $change_form_link_text = '';
			/**
			 * @var string $change_form_url the URL to the form used for requesting changes
			 * @access private
			 */
			private string $change_form_url = '';
			/**
			 * @var array $change_form_props the attributes that should be appended as the query string of the change form URL
			 * @access private
			 */
			private array $change_form_props = array();
			/**
			 * @var array $post_types the list of post types with which to associate this governance information
			 * @access private
			 */
			private array $post_types = array();
			/**
			 * @var bool $mark_for_deletion_active whether the Mark for Deletion option is enabled
			 * @access private
			 */
			private bool $mark_for_deletion_active = false;
			/**
			 * @var Emails $email_obj a property to hold the Emails object used by this plugin
			 * @access protected
			 */
			protected Emails $email_obj;
			/**
			 * @var Trigger $archive_obj a property to hold the Trigger object used by this plugin
			 * @access protected
			 */
			protected Trigger $archive_obj;
			/**
			 * @var bool $frontend_compliance_active whether the plugin should display a compliance status on the frontend for logged-in privileged users
			 * @access private
			 */
			private bool $frontend_compliance_active = false;
			/**
			 * @var bool $archive_active whether the plugin should integrate the Wayback Machine
			 * @access private
			 */
			private bool $archive_active = false;
			/**
			 * @var bool $email_active whether the plugin should send out email prompts
			 * @access private
			 */
			private bool $email_active = true;
			/**
			 * @var string $archive_search the URL to be replaced in snapshot URL queries
			 * @access private
			 */
			private string $archive_search = '';
			/**
			 * @var string $archive_replace the URL with which this site's URL should be replaced in snapshot queries
			 * @access private
			 */
			private string $archive_replace = '';

			/**
			 * Creates the Plugin object
			 *
			 * @access private
			 * @since  0.1
			 */
			private function __construct() {
				Config::instance();

				$this->set_initial_variables();

				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
				add_action( 'init', array( $this, 'load_types' ) );

				Updates::instance();
				REST::instance();

				if ( is_admin() ) {
					Admin::instance();
				} else {
					Frontend::instance();
				}

				/*if ( in_array( Helpers::get_environment(), array( 'production', 'staging', 'development' ) ) ) {
					Helpers::log('The current Pantheon environment appears to be ' . Helpers::get_environment() );
					return;
				}*/

				if ( $this->get_email_active() ) {
					if ( isset( $_GET['cornell/governance/run-email-cron'] ) || isset( $_GET['cornell/governance/daily-cron'] ) ) {
						add_action( 'init', array( $this, 'send_emails' ) );
					}
				}

				/*if ( $this->get_archive_settings( 'active' ) ) {
					if ( isset( $_REQUEST['cornell/governance/trigger-snapshots'] ) || isset( $_GET['cornell/governance/daily-cron'] ) ) {
						add_action( 'init', array( $this, 'do_snapshots' ) );
					}
				}*/
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Plugin
			 * @since   0.1
			 */
			public static function instance(): Plugin {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Set initial variable values
			 *
			 * @access private
			 * @return void
			 * @since  0.1
			 */
			private function set_initial_variables(): void {
				$this->set_capability();
				$this->set_managing_office();
				$this->set_change_form_vars();
				$this->set_post_types();
				$this->set_mark_for_deletion_active();
				$this->set_frontend_compliance_active();
				$this->set_archive_settings();
				$this->set_email_active();
			}

			/**
			 * Set the capability variable
			 *
			 * @access private
			 * @return void
			 * @since  0.1
			 */
			private function set_capability(): void {
				$this->capability = apply_filters( 'cornell/governance/capability', get_option( 'cornell-governance-capability', 'manage_options' ) );
			}

			/**
			 * Get the value of the capability variable
			 *
			 * @access public
			 * @return string the value of the capability variable
			 * @since  0.1
			 */
			public function get_capability(): string {
				return $this->capability;
			}

			/**
			 * Set the managing_office variable
			 *
			 * @access private
			 * @return void
			 * @since  0.1
			 */
			private function set_managing_office(): void {
				$this->managing_office = apply_filters( 'cornell/governance/managing-office', get_option( 'cornell-governance-managing-office', __( 'MarCom', 'cornell/governance' ) ) );
			}

			/**
			 * Get the value of the managing_office variable
			 *
			 * @access public
			 * @return string the value of the managing_office variable
			 * @since  0.1
			 */
			public function get_managing_office(): string {
				return $this->managing_office;
			}

			/**
			 * Set the various change_form variables
			 *
			 * @access private
			 * @return void
			 * @since  0.1
			 */
			private function set_change_form_vars(): void {
				$this->change_form_url       = esc_url( apply_filters( 'cornell/governance/change-form/url', get_option( 'cornell-governance-change-form-url', 'https://www.google.com/' ) ) );
				$this->change_form_active    = ! empty( apply_filters( 'cornell/governance/change-form/active', get_option( 'cornell-governance-change-form-active', false ) ) );
				$this->change_form_link_text = sanitize_text_field( apply_filters( 'cornell/governance/change-form/link-text', get_option( 'cornell-governance-change-form-link-text', '' ) ) );
				$this->change_form_props     = apply_filters( 'cornell/governance/change-form/props', get_option( 'cornell-governance-change-form-props', array() ) );
			}

			/**
			 * Get the value of one of the change_form variables
			 *
			 * @param string $key the variable to be retrieved
			 *      If left empty, the full array of change_form vars will be retrieved and returned
			 *
			 * @access public
			 * @return mixed the value of the variable
			 * @since  0.1
			 */
			public function get_change_form_var( string $key = '' ) {
				switch ( $key ) {
					case 'url' :
						return $this->change_form_url;
						break;
					case 'active' :
						return $this->change_form_active;
						break;
					case 'link_text' :
						return $this->change_form_link_text;
						break;
					case 'props' :
						return $this->change_form_props;
						break;
					default :
						return array(
							'active'    => $this->change_form_active,
							'link_text' => $this->change_form_link_text,
							'url'       => $this->change_form_url,
							'props'     => $this->change_form_props,
						);
						break;
				}
			}

			/**
			 * Get the value of the change_form_url variable
			 *
			 * @access public
			 * @return string the value of the change_form_url variable
			 * @since  0.1
			 */
			public function get_change_form_url(): string {
				return $this->get_change_form_var( 'url' );
			}

			/**
			 * Build the change form URL with appropriate URL parameters
			 *
			 * @access public
			 * @return string the built URL
			 * @since  2023.05
			 */
			public function build_change_form_url(): string {
				$props = $this->get_change_form_var( 'props' );

				$args = array();
				if ( isset( $_REQUEST['post'] ) ) {
					$post = get_post( $_REQUEST['post'] );
				} else if ( isset( $_REQUEST['post_id'] ) ) {
					$post = get_post( $_REQUEST['post_id'] );
				}

				if ( isset( $post ) && is_a( $post, '\WP_Post' ) ) {
					if ( in_array( 'post_title', $props ) ) {
						$args['post_title'] = $post->post_title;
					}
					if ( in_array( 'ID', $props ) ) {
						$args['post_id'] = $post->ID;
					}
					if ( in_array( 'permalink', $props ) ) {
						$args['post_url'] = get_permalink( $post );
					}
				}

				$user = get_current_user_id();
				if ( is_numeric( $user ) ) {
					$user = get_user_by( 'id', $user );

					if ( is_a( $user, '\WP_User' ) ) {
						if ( in_array( 'current_user_email', $props ) ) {
							$args['user_email'] = $user->user_email;
						}
						if ( in_array( 'current_user_id', $props ) ) {
							$args['user_id'] = $user->ID;
						}
						if ( in_array( 'current_user_display_name', $props ) ) {
							$args['user_display_name'] = $user->display_name;
						}
					}
				}

				$url = $this->get_change_form_url();

				$args = apply_filters( 'cornell/governance/change-form-url/parameters', $args );

				if ( count( $args ) ) {
					$args = array_map( 'urlencode', $args );
					$url  = add_query_arg( $args, $url );
				}

				return $url;
			}

			/**
			 * Set the post_types array
			 *
			 * @access private
			 * @return void
			 * @since  0.1
			 */
			private function set_post_types(): void {
				$this->post_types = apply_filters( 'cornell/governance/post-types', get_option( 'cornell-governance-post-types', array( 'page' ) ) );
			}

			/**
			 * Get the value of the post_types variable
			 *
			 * @access public
			 * @return array the value of the post_types variable
			 * @since  0.1
			 */
			public function get_post_types(): array {
				return $this->post_types;
			}

			/**
			 * Set the value of the setting that determines whether the "Mark for Deletion" option is enabled
			 *
			 * @access private
			 * @return void
			 * @since  0.4.9
			 */
			private function set_mark_for_deletion_active() {
				$this->mark_for_deletion_active = ! empty( apply_filters( 'cornell/governance/mark-for-deletion/active', get_option( 'cornell-governance-mark-for-deletion-active', false ) ) );
			}

			/**
			 * Get the value of the mark_for_deletion_active option
			 *
			 * @access public
			 * @return bool whether the option is enabled or not
			 * @since  0.4.9
			 */
			public function get_mark_for_deletion_active(): bool {
				return $this->mark_for_deletion_active;
			}

			/**
			 * Set the value of the setting that determines whether the "Front-End Compliance" option is enabled
			 *
			 * @access private
			 * @return void
			 * @since  0.5.8
			 */
			private function set_frontend_compliance_active() {
				$this->frontend_compliance_active = ! empty( apply_filters( 'cornell/governance/frontend-compliance/active', get_option( 'cornell-governance-frontend-compliance-active', false ) ) );
			}

			/**
			 * Get the value of the frontend_compliance_active option
			 *
			 * @access public
			 * @return bool whether the option is enabled or not
			 * @since  0.5.8
			 */
			public function get_frontend_compliance_active(): bool {
				return $this->frontend_compliance_active;
			}

			/**
			 * Set the values of the Archive settings
			 *
			 * @access private
			 * @return void
			 * @since  0.6.2
			 */
			private function set_archive_settings() {
				$this->archive_active = ! empty( apply_filters( 'cornell/governance/archive/active', get_option( 'cornell-governance-archive-active', false ) ) );
				$this->archive_search  = apply_filters( 'cornell/governance/archive/search', get_option( 'cornell-governance-archive-url-search', get_bloginfo( 'url' ) ) );
				$this->archive_replace = apply_filters( 'cornell/governance/archive/replace', get_option( 'cornell-governance-archive-url-replace', '' ) );
			}

			/**
			 * Retrieve and return the value of one of the Archive settings
			 *
			 * @param string $key the variable to be retrieved
			 *       If left empty, the full array of Archive vars will be retrieved and returned
			 *
			 * @access public
			 * @return string|array|bool the value of the setting
			 * @since  0.6.2
			 */
			public function get_archive_settings( string $key = '' ) {
				switch ( $key ) {
					case 'active' :
						return $this->archive_active;
						break;
					case 'search' :
						return $this->archive_search;
						break;
					case 'replace' :
						return $this->archive_replace;
						break;
					default :
						return array(
							'active'  => $this->archive_active,
							'search'  => $this->archive_search,
							'replace' => $this->archive_replace,
						);
						break;
				}
			}

			/**
			 * Set the value of the setting that determines whether the "Archive" option is enabled
			 *
			 * @access private
			 * @return void
			 * @since  0.5.8
			 */
			private function set_email_active() {
				$this->email_active = ! empty( apply_filters( 'cornell/governance/email/active', get_option( 'cornell-governance-email-prompts-active', true ) ) );
			}

			/**
			 * Get the value of the archive_active option
			 *
			 * @access public
			 * @return bool whether the option is enabled or not
			 * @since  0.5.8
			 */
			public function get_email_active(): bool {
				return $this->email_active;
			}

			/**
			 * Enqueue the necessary styles and scripts for this plugin
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function enqueue_scripts(): void {
				$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
				wp_enqueue_style( 'cornell-governance', Helpers::plugins_url( '/dist/css/cornell-governance' . $min . '.css' ), array(), self::$version, 'all' );
				/*wp_enqueue_script( 'cornell-governance', Helpers::plugins_url( '/dist/js/cornell-governance' . $min . '.js' ), array(), self::$version, true );*/
			}

			/**
			 * Invoke any plugins that need to be invoked
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function plugins_loaded(): void {
			}

			/**
			 * Instantiate the custom post types & taxonomies
			 *
			 * @access public
			 * @return void
			 * @since  1.0
			 */
			public function load_types(): void {
				Taxonomies\Audience::instance();

				$this->register_post_meta();
			}

			/**
			 * Register the post meta for this plugin
			 *
			 * @access private
			 * @return void
			 * @since  0.1
			 */
			private function register_post_meta(): void {
				$types = $this->get_post_types();

				if ( count( $types ) <= 0 ) {
					return;
				}

				foreach ( $types as $type ) {
					register_post_meta( $type, self::INFO_META_KEY, array(
						'type'              => 'array',
						'description'       => __( 'The Page Governance Information associated with this piece of content', 'cornell/governance' ),
						'sanitize_callback' => array( $this, 'validate_meta_fields' ),
						'revisions_enabled' => true,
					) );

					register_post_meta( $type, 'cornell/governance/notes', array(
						'type'              => 'array',
						'description'       => __( 'Any Page Governance notes associated with this piece of content', 'cornell/governance' ),
						'sanitize_callback' => array( $this, 'validate_meta_fields' ),
						'show_in_rest'      => false,
						'revisions_enabled' => true,
					) );

					register_post_meta( $type, 'cornell/governance/revisions', array(
						'type'              => 'array',
						'description'       => __( 'Any Page Governance commit messages associated with this object', 'cornell/governance' ),
						'sanitize_callback' => array( $this, 'validate_meta_fields' ),
						'show_in_rest'      => false,
						'revisions_enabled' => true,
					) );
				}
			}

			/**
			 * Sanitize meta data
			 *
			 * @param mixed $value the meta value
			 *
			 * @access public
			 * @return mixed the sanitized value
			 * @since  0.1
			 */
			public function validate_meta_fields( $value ) {
				return $value;
			}

			/**
			 * Attempt to send scheduled email messages
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function send_emails() {
				if ( isset( $this->email_obj ) && is_a( $this->email_obj, 'Cornell\Governance\Emails' ) ) {
					return;
				}

				$this->email_obj = Emails::instance();
				/*$this->email_obj->send_messages();*/

				$email_text = isset( $_GET['cornell/governance/run-email-cron'] ) ? ' The email trigger is set to ' . $_GET['cornell/governance/run-email-cron'] : '';
				$debug_text = isset( $_GET['cornell/governance/debug'] ) ? ' The debug switch is set to ' . $_GET['cornell/governance/debug'] : '';

				wp_die( __( 'The cron task to send Governance notifications has completed.' . $email_text . $debug_text, 'cornell/governance' ), __( 'Governance Emails Sent', 'cornell/governance' ), array( 'response' => 200 ) );
			}

			/**
			 * Attempt to perform the scheduled Wayback Machine snapshots
			 *
			 * @access public
			 * @return void
			 * @since  0.6.2
			 */
			public function do_snapshots() {
				if ( isset( $this->archive_obj ) && is_a( $this->archive_obj, 'Cornell\Governance\Wayback\Trigger' ) ) {
					return;
				}

				$this->archive_obj = Trigger::instance();
				$this->archive_obj->do_cron();
			}
		}
	}
}
