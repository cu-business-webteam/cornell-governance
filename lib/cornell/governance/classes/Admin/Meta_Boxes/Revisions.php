<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes {

	use Cornell\Governance\Admin\Meta_Boxes\Fields\Commit_Message;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Editor;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Revisions' ) ) {
		class Revisions extends Base {
			/**
			 * @var Revisions $instance holds the single instance of this class
			 * @access private
			 */
			protected static Revisions $instance;

			function __construct() {
				parent::__construct( array(
					'id'       => 'cornell-governance-page-revisions',
					'title'    => __( 'Page Revision Information', 'cornell/governance' ),
					'context'  => 'side',
					'priority' => 'high',
					'fields'   => array(
						'commit-message' => 'Commit_Message',
						'editor'         => 'Editor',
					),
					'meta_key' => 'cornell/governance/revisions',
				) );

				$this->get_meta_data();

				if ( false === Helpers::is_block_editor_active() ) {
					add_action( 'post_submitbox_misc_actions', array( $this, 'get_publish_meta' ) );
					remove_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
				}
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Revisions
			 * @since   0.1
			 */
			public static function instance(): Revisions {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Retrieve the values of the current post meta
			 *
			 * @access protected
			 * @return void
			 * @since  0.1
			 */
			protected function get_meta_data(): void {
				$this->meta = array(
					'editor'         => get_current_user_id(),
					'commit-message' => '',
				);

				if ( isset( $_GET['post'] ) ) {
					$info = get_post_meta( $_GET['post'], $this->meta_key, true );
				} else if ( isset( $GLOBALS['post'] ) ) {
					if ( is_numeric( $GLOBALS['post'] ) ) {
						$info = get_post_meta( $GLOBALS['post'], $this->meta_key );
					} else {
						$info = get_post_meta( $GLOBALS['post']->ID, $this->meta_key );
					}
				} else {
					$info = array();
				}

				if ( false === $info ) {
					$this->meta = array(
						'editor'         => get_current_user_id(),
						'commit-message' => '',
					);

					return;
				} else if ( empty( $info ) ) {
					$this->meta = array(
						'editor'         => get_current_user_id(),
						'commit-message' => '',
					);
				} else if ( is_array( $info ) ) {
					$this->meta = $info;
				}
			}

			/**
			 * Retrieve the latest commit
			 *
			 * @param int $post_id the ID of the post being queried
			 *
			 * @access public
			 * @return array the commit information
			 * @since  0.1
			 */
			public function get_latest_commit( int $post_id ): array {
				$all_meta = get_post_meta( $post_id, $this->meta_key . '/all', true );
				if ( ! empty( $all_meta ) ) {
					$revision = array( 'commit-message' => '' );
					while ( empty( $revision['commit-message'] ) && count( $all_meta ) > 0 ) {
						$revision = array_pop( $all_meta );
					}

					return is_array( $revision ) && ! empty( $revision['commit-message'] ) ? $revision : array();
				}

				$revisions = wp_get_post_revisions( $post_id );
				if ( ! is_array( $revisions ) ) {
					return array();
				}
				$revision = array( 'commit-message' => '' );
				$i        = 0;

				while ( empty( $revision['commit-message'] ) && count( $revisions ) > 0 ) {
					$p = array_shift( $revisions );
					$revision = get_post_meta( $p->ID, $this->meta_key, true );
					if ( ! is_array( $revision ) || ! array_key_exists( 'commit-message', $revision ) ) {
						$revision = array( 'commit-message' => '' );
					}
				}

				if ( ! empty( $revision['commit-message'] ) ) {
					$meta = $revision;
				} else {
					$meta = null;
				}

				return is_array( $meta ) ? $meta : array();
			}

			/**
			 * Build content to be inserted into the Publish metabox
			 *
			 * @param \WP_Post $post the post being edited
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function get_publish_meta( \WP_Post $post ) {
				$post_type = $post->post_type;
				$types     = Plugin::instance()->get_post_types();

				if ( ! in_array( $post_type, $types ) ) {
					return;
				}

				echo $this->get_meta_box();
			}

			/**
			 * Build the content of the meta box
			 *
			 * @access protected
			 * @return string
			 * @since  0.1
			 */
			protected function get_meta_box(): string {
				$post_id = 0;
				if ( isset( $_REQUEST['post'] ) ) {
					$post_id = $_REQUEST['post'];
				} else if ( isset( $GLOBALS['post'] ) ) {
					if ( is_numeric( $GLOBALS['post'] ) ) {
						$post_id = $GLOBALS['post'];
					} else if ( is_a( $GLOBALS['post'], '\WP_Post' ) ) {
						$post_id = $GLOBALS['post']->ID;
					}
				}

				$output = '';

				if ( empty( $post_id ) ) {
					return __( '<p class="note">You will not be able to set up governance information until you have saved this piece of content for the first time</p>', 'cornell/governance' );
					/*} else {
						$output .= '<p>The current post ID appears to be: ' . $post_id . '</p>';*/
				}

				$latest = $this->get_latest_commit( $post_id );

				$output .= wp_nonce_field( $this->id, $this->id . '-nonce', true, false );
				$output .= Editor::instance()->get_input();
				$output .= Commit_Message::instance()->get_input();
				if ( ! empty( $latest ) ) {
					$output .= sprintf( '
						<div class="previous-commit">
							<p>
								<strong>%1$s</strong>
							</p>
							<blockquote>
								%2$s
							</blockquote>
						</div>',
						__( 'Latest commit message: ', 'cornell/governance' ),
						self::format_commit_message( $latest )
					);

					$output .= sprintf( __( '<p class="field-note"><a href="%s">View more commit messages</a></p>', 'cornell/governance' ), '#cornell-governance-revisions-list-container' );
				}

				return sprintf( '<div class="%1$s">%2$s</div>', 'cornell-governance-metabox', $output );
			}

			/**
			 * Save the commit message
			 *
			 * @param int $post_id the ID of the post being modified
			 * @param \WP_Post $post the post object being modified
			 * @param bool $update whether this is a revision or a new post
			 *
			 * @access public
			 * @return void
			 * @since  2023.02
			 */
			public function save_revision( int $post_id, \WP_Post $post, bool $update = false ) {
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}

				$nonce = $this->id . '-nonce';
				if ( ! wp_verify_nonce( $_REQUEST[ $nonce ], $this->id ) ) {
					return;
				}

				$types = Plugin::instance()->get_post_types();
				if ( ! in_array( $post->post_type, $types ) && 'revision' !== $post->post_type ) {
					return;
				}

				if ( empty( $_REQUEST['cornell-governance-page-revisions-commit-message'] ) ) {
					$message = '';
				} else {
					$message = $_REQUEST['cornell-governance-page-revisions-commit-message'];
				}

				$postmeta = array(
					'commit-message' => $message,
					'editor'         => $_REQUEST['cornell-governance-page-revisions-editor'],
					'timestamp'      => time(),
				);

				$parent = wp_is_post_revision( $post_id );
				if ( $parent ) {
					return;
				}

				$parent = $post_id;

				$all_meta = get_post_meta( $parent, $this->meta_key . '/all', true );
				if ( ! is_array( $all_meta ) ) {
					$all_meta = array();

					$test = get_post_meta( $parent, $this->meta_key, true );
					if ( is_array( $test ) ) {
						$copy = array_values( $test );
						$tmp = array_pop( $copy );
						if ( is_array( $tmp ) ) {
							$all_meta = $test;
						} else {
							$all_meta = array();
							$revisions = wp_get_post_revisions( $parent );
							foreach ( $revisions as $revision ) {
								$message = get_post_meta( $revision->ID, $this->meta_key, true );
								if ( is_array( $message ) && array_key_exists( 'commit-message', $message ) ) {
									$all_meta[$revision->ID] = $message;
								}
							}
						}
					} else {
						$all_meta = array();
					}
				}

				$revisions = wp_get_post_revisions( $parent );
				foreach ( $revisions as $revision ) {
					if ( $post->post_modified_gmt !== $revision->post_modified_gmt ) {
						continue;
					}

					$revision_id = $revision->ID;
				}

				if ( isset( $revision_id ) ) {
					$all_meta[ $revision_id ] = $postmeta;
				} else {
					$all_meta[] = $postmeta;
				}

				ksort( $all_meta, SORT_NUMERIC );

				update_post_meta( $parent, $this->meta_key, $postmeta );
				update_post_meta( $parent, $this->meta_key . '/all', $all_meta );
			}

			/**
			 * Format revision information for output
			 *
			 * @param array $message the revision data
			 *
			 * @access public
			 * @return string the formatted output
			 * @since  0.1
			 */
			static public function format_commit_message( array $message ): string {
				$date = Helpers::get_date_time( $message['timestamp'] );

				$user = get_user_by( 'id', $message['editor'] );

				return sprintf( '<time datetime="%4$s">%1$s</time> by %2$s: <div>%3$s</div>',
					Helpers::format_date_time( $date ),
					$user->display_name,
					$message['commit-message'],
					false === $date ? '' : $date->format( 'c' )
				);
			}
		}
	}
}