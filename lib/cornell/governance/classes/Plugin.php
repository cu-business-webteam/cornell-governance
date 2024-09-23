<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance {

	use Cornell\Governance\Admin\Admin;

	if ( ! class_exists( 'Plugin' ) ) {
		class Plugin {
			/**
			 * @var Plugin $instance holds the single instance of this class
			 * @access private
			 */
			private static Plugin $instance;
			/**
			 * @var string $version holds the version number for the plugin
			 * @access public
			 */
			public static string $version = '0.4.6';
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
			 * @var Emails $email_obj a property to hold the Emails object used by this plugin
			 * @access protected
			 */
			protected Emails $email_obj;

			/**
			 * Creates the Plugin object
			 *
			 * @access private
			 * @since  0.1
			 */
			private function __construct() {
				$this->set_initial_variables();

				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
				add_action( 'init', array( $this, 'load_types' ) );

				if ( is_admin() ) {
					Admin::instance();
				}

				/*if ( in_array( Helpers::get_environment(), array( 'production', 'staging', 'development' ) ) ) {
					Helpers::log('The current Pantheon environment appears to be ' . Helpers::get_environment() );
					return;
				}*/

				if ( isset( $_GET['cornell/governance/run-email-cron'] ) ) {
					add_action( 'init', array( $this, 'send_emails' ) );
				}
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
			 * Enqueue the necessary styles and scripts for this plugin
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function enqueue_scripts(): void {
				//There is no "frontend" to this plugin, so we do not need any frontend scripts or styles at this time
				return;

				$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
				wp_enqueue_style( 'cornell-governance', Helpers::plugins_url( '/dist/css/cornell-governance' . $min . '.css' ), array(), self::$version, 'all' );
				wp_enqueue_script( 'cornell-governance', Helpers::plugins_url( '/dist/js/cornell-governance' . $min . '.js' ), array(), self::$version, true );
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
					register_post_meta( $type, 'cornell/governance/information', array(
						'type'              => 'array',
						'description'       => __( 'The Page Governance Information associated with this piece of content', 'cornell/governance' ),
						'sanitize_callback' => array( $this, 'validate_meta_fields' ),
						'show_in_rest'      => true,
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
			 * Replaces "MarCom" with the filtered name of the managing office in field labels/descriptions
			 *
			 * @param array $field the ACF field array
			 *
			 * @access public
			 * @return array the updated field array
			 * @since  0.1
			 */
			public function managing_office_labels( array $field ): array {
				if ( __( 'MarCom', 'cornell/governance' ) === $this->get_managing_office() ) {
					return $field;
				}

				$keys = array(
					'label',
					'aria-label',
					'instructions',
					'placeholder',
				);

				foreach ( $keys as $key ) {
					if ( empty( $field[ $key ] ) ) {
						continue;
					}

					$field[ $key ] = str_replace( __( 'MarCom', 'cornell/governance' ), $this->get_managing_office(), $field[ $key ] );
				}

				return $field;
			}

			/**
			 * Fills in the dynamic values of the "Last Reviewed" message field
			 *
			 * @param array $field the ACF field array
			 *
			 * @access public
			 * @return array the updated field array
			 * @since  0.1
			 */
			public function dynamic_review_message( array $field ): array {
				if ( $field['type'] !== 'message' ) {
					return $field;
				}

				preg_match_all( '/\{\{([^\}]*?)\}\}/', $field['message'], $matches );
				if ( $matches ) {
					foreach ( $matches[1] as $keyword ) {
						switch ( $keyword ) {
							case 'change_form_url' :
								$value = $this->get_change_form_url();
								break;
							default :
								$value = get_post_meta( $GLOBALS['post']->ID, $keyword, true );
								break;
						}
						$field['message'] = str_replace( '{{' . $keyword . '}}', $value, $field['message'] );
					}
				}

				return $field;
			}

			/**
			 * Set all fields to read-only for non-admins
			 *
			 * @param array $field the ACF field array
			 *
			 * @access public
			 * @return array the updated field array
			 * @since  0.1
			 */
			public function disable_fields( array $field ): array {
				if ( current_user_can( $this->capability ) ) {
					return $field;
				}

				$disabled_fields = array(
					'group_63b2e80616138',
					'field_63b2e8a42cef9',
					'field_63b2e9422cefe',
					'field_63b2e9a12ceff'
				);

				$excluded_fields = array(
					'field_63b2f888a5114',
					'field_63b2f8bba5115',
				);

				if ( in_array( $field['parent'], $disabled_fields ) && ! in_array( $field['key'], $excluded_fields ) ) {
					$field['readonly']         = true;
					$field['disabled']         = true;
					$field['wrapper']['class'] .= ' disabled';
				}

				return $field;
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
		}
	}
}
