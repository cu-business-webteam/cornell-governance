<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin {

	use Cornell\Governance\Admin\Meta_Boxes\Fields\Commit_Message;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Admin\Meta_Boxes\Notes;
	use Cornell\Governance\Admin\Meta_Boxes\Revisions;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Admin' ) ) {
		class Admin {
			/**
			 * @var Admin $instance holds the single instance of this class
			 * @access private
			 */
			private static Admin $instance;

			/**
			 * Creates the Admin object
			 *
			 * @access private
			 * @since  0.1
			 */
			private function __construct() {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
				Meta_Boxes::instance();
				Menu::instance();
				Settings::instance();
				Dashboard_Widgets::instance();

				add_action( 'admin_init', array( $this, 'add_status_column' ) );
				add_action( 'wp_ajax_cornell_governance_save_meta', array( $this, 'save_ajax' ) );
				add_action( 'save_post', array( $this, 'save_revision' ), 10, 3 );
				/*add_action( '_wp_put_post_revision', array( $this, 'save_revision_meta' ) );*/
				add_filter( '_wp_post_revision_fields', array( $this, 'revision_fields' ) );

				/*add_action( 'admin_init', array( $this, 'change_author_label' ) );*/

				// We need to do this now, because the Info object isn't instantiated in admin-ajax otherwise
				/*add_action( 'wp_ajax_cornell_governance_save_meta_info', array( Info::instance(), 'ajax_save' ) );*/
				/*if ( current_user_can( Plugin::instance()->get_capability() ) ) {
					add_action( 'wp_ajax_cornell_governance_save_meta_notes', array( Notes::instance(), 'ajax_save' ) );
				}*/
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Admin
			 * @since   0.1
			 */
			public static function instance(): Admin {
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
			 * Run the necessary AJAX actions to save form updates
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function save_ajax(): void {
				if ( ! isset( $_POST['cornell-governance-action'] ) ) {
					return;
				}

				if ( 'info' === $_POST['cornell-governance-action'] ) {
					Info::instance()->ajax_save();
				} else if ( 'notes' === $_POST['cornell-governance-action'] ) {
					if ( current_user_can( Plugin::instance()->get_capability() ) ) {
						Notes::instance()->ajax_save();
					}
				}
			}

			/**
			 * Run any necessary save actions when a post is saved
			 *
			 * @param int $post_id the ID of the post being modified
			 * @param \WP_Post $post the post object being modified
			 * @param bool $update whether this is a revision or a new post
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function save_revision( int $post_id, \WP_Post $post, bool $update = false ) {
				Revisions::instance()->save_revision( $post_id, $post, $update );
			}

			/**
			 * Attempt to save metadata for a specific revision of a post
			 *
			 * @param int $post_id
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function save_revision_meta( int $post_id ) {
				Revisions::instance()->save_revision_meta( $post_id );
			}

			/**
			 * Add the appropriate metadata to be loaded/displayed with a revision
			 *
			 * @param array $fields the existing list of fields to include
			 *
			 * @access public
			 * @return array the updated list of fields
			 * @since  0.1
			 */
			public function revision_fields( array $fields ): array {
				$key            = 'cornell/governance/commit-message';
				$fields[ $key ] = __( 'Commit Message', 'cornell/governance' );
				add_filter( '_wp_post_revision_field_' . $key, array( Commit_Message::instance(), 'revision_field' ), 10, 4 );

				return $fields;
			}

			/**
			 * Rename the "Author" metabox/field/column to "Steward" where appropriate
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function change_author_label() {
				$types = Plugin::instance()->get_post_types();

				foreach ( $types as $type ) {
					$filter = sprintf( 'manage_edit-%s_columns', $type );
					add_filter( $filter, array( $this, 'rename_author_column' ) );
				}

				/*add_filter( 'gettext', array( $this, 'change_author_translation' ), 10, 3 );*/
				add_action( 'add_meta_boxes', array( $this, 'change_author_metabox' ) );
			}

			/**
			 * Rename the "Author" column in list tables
			 *
			 * @param array $columns the list of table columns
			 *
			 * @access public
			 * @return array the updated list of columns
			 * @since  0.1
			 */
			public function rename_author_column( array $columns ): array {
				if ( array_key_exists( 'author', $columns ) ) {
					$columns['author'] = __( 'Steward', 'cornell/governance' );
				}

				return $columns;
			}

			/**
			 * Rename the "Author" field when editing content
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function change_author_metabox() {
				global $wp_meta_boxes;
				$wp_meta_boxes['post']['normal']['core']['authordiv']['title'] = __( 'Steward', 'cornell/governance' );
			}

			/**
			 * If all else fails, change the translation of the word "Author"
			 *
			 * @param string $translation the translated version of the string
			 * @param string $text the original version of the string
			 * @param string $domain the text domain being used
			 *
			 * @access public
			 * @return string the updated translation
			 * @since  0.1
			 */
			public function change_author_translation( string $translation, string $text, string $domain ): string {
				if ( 'Author' === $translation && 'default' === $domain ) {
					return 'Steward';
				}

				return $translation;
			}

			/**
			 * Add a governance status column to each selected post type column list
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function add_status_column() {
				$types = Plugin::instance()->get_post_types();

				foreach ( $types as $post_type ) {
					$hooks = array(
						'column' => "manage_{$post_type}_posts_columns",
						'custom' => "manage_{$post_type}_posts_custom_column"
					);

					add_filter( $hooks['column'], array( $this, 'status_posts_column' ), 99 );
					add_action( $hooks['custom'], array( $this, 'do_status_posts_column' ), 99, 2 );
				}
			}

			/**
			 * Add the Governance column to the post list table
			 *
			 * @param array $columns the existing list of columns
			 *
			 * @access public
			 * @return array the updated list of columns
			 * @since  0.1
			 */
			public function status_posts_column( array $columns ): array {
				$columns['governance'] = __( 'Governance Status', 'cornell/governance' );

				return $columns;
			}

			/**
			 * Output the Governance column in the post list table
			 *
			 * @param string $column_name the name of the column being output
			 * @param int $post_id the ID of the post being processed
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function do_status_posts_column( string $column_name, int $post_id ) {
				if ( 'governance' !== $column_name ) {
					return;
				}

				$now = time();

				$data = get_post_meta( $post_id, 'cornell/governance/information', true );

				if ( empty( $data['goals'] ) ) {
					_e( 'Unreviewed', 'cornell/governance' );
					return;
				}

				$due  = Helpers::calculate_next_review_date( $data['last-review'], $data['review-cycle'] );

				if ( $due <= $now ) {
					_e( 'Overdue', 'cornell/governance' );
				} else if ( strtotime( '+ 60 days' ) < $due ) {
					_e( 'Compliant', 'cornell/governance' );
				} else if ( strtotime( '+ 30 days' ) < $due ) {
					_e( 'Due in &lt;60 days', 'cornell/governance' );
				} else if ( strtotime( '+ 7 days' ) < $due ) {
					_e( 'Due in &lt;30 days', 'cornell/governance' );
				} else {
					_e( 'Due in &lt;7 days', 'cornell/governance' );
				}
			}
		}
	}
}
