<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes {

	use Cornell\Governance\Admin\Meta_Boxes\Fields\Notes_Notes;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Notes_Timestamp;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Save_Notes;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Notes' ) ) {
		class Notes extends Base {
			/**
			 * @var Notes $instance holds the single instance of this class
			 * @access private
			 */
			protected static Notes $instance;

			function __construct() {
				parent::__construct( array(
					'id'       => 'cornell-governance-page-notes',
					'title'    => __( 'Page Notes', 'cornell/governance' ),
					'context'  => 'advanced',
					'priority' => 'high',
					'fields'   => array(
						'notes'     => 'Notes_Notes',
						'timestamp' => 'Notes_Timestamp',
					),
					'meta_key' => 'cornell/governance/notes',
				) );

				$this->get_meta_data();

				/*$this->maybe_unregister_metabox();*/
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Notes
			 * @since   0.1
			 */
			public static function instance(): Notes {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Check to see if there is anything to show in this metabox and
			 *      if not, unregister it
			 *
			 * @access private
			 * @return void
			 * @since  2023.05
			 */
			private function maybe_unregister_metabox() {
				if ( current_user_can( Plugin::instance()->get_capability() ) ) {
					return;
				}

				$post_id = $this->get_post_ID();

				if ( empty( $post_id ) ) {
					$this->unhook_metabox();

					return;
				}

				if ( empty( $this->get_commit_messages( $post_id ) ) ) {
					$this->unhook_metabox();
				}

				return;
			}

			/**
			 * Retrieve the values of the current post meta
			 *
			 * @access protected
			 * @return void
			 * @since  0.1
			 */
			protected function get_meta_data(): void {
				$info = false;
				if ( isset( $_GET['post'] ) ) {
					$info = get_post_meta( $_GET['post'], $this->meta_key, true );
				} else if ( isset( $GLOBALS['post'] ) ) {
					if ( is_numeric( $GLOBALS['post'] ) ) {
						$info = get_post_meta( $GLOBALS['post'], $this->meta_key );
					} else {
						$info = get_post_meta( $GLOBALS['post']->ID, $this->meta_key );
					}
				}

				if ( false === $info ) {
					$this->meta = array();

					return;
				} else if ( empty( $info ) ) {
					$this->meta = array();
				} else if ( is_array( $info ) ) {
					$this->meta = $info;
				}
			}

			/**
			 * Build the content of the meta box
			 *
			 * @access protected
			 * @return string
			 * @since  0.1
			 */
			protected function get_meta_box(): string {
				$cap = Plugin::instance()->get_capability();

				$post_id = $this->get_post_ID();

				$output = '';

				$messages = $this->get_commit_messages( $post_id );
				$output   .= '<blockquote id="cornell-governance-revisions-list-container">';
				$output   .= sprintf( '<h3>%s</h3>', __( 'Recent Commit Messages', 'cornell/governance' ) );
				if ( count( $messages ) > 0 ) {
					$output .= '<ol class="commit-messages">';
					$output .= sprintf( '<li>%s</li>', implode( '</li><li>', $messages ) );
					$output .= '</ol>';
				} else {
					$output .= sprintf( '<p>%s</p>', __( 'There have not been any commit messages added to this page, yet.', 'cornell/governance' ) );
				}
				$output .= '</blockquote>';

				if ( empty( $post_id ) ) {
					return __( '<p class="note">You will not be able to set up governance information until you have saved this piece of content for the first time</p>', 'cornell/governance' );
				}

				$output .= Notes_Notes::instance()->get_input();

				if ( current_user_can( $cap ) ) {
					$output .= wp_nonce_field( $this->id, $this->id . '-nonce', true, false );
					$output .= sprintf( '<input type="hidden" name="cornell-governance-notes-post-id" value="%d"/>', $post_id );
					$output .= sprintf( '<input type="hidden" name="cornell-governance-action" value="%s"/>', 'notes' );
					$output .= Save_Notes::instance()->get_input();
				}

				$output .= sprintf( '<div class="field-note timestamp-container">%s</div>', Notes_Timestamp::instance()->get_input() );

				return sprintf( '<div class="%1$s">%2$s</div>', 'cornell-governance-metabox', $output );
			}

			/**
			 * Retrieve revision commit messages to display
			 *
			 * @param int $post the post ID for the current post
			 * @param int $limit the maximum number of messages to display
			 *
			 * @access public
			 * @return array an array of commit messages
			 * @since  0.1
			 */
			public function get_commit_messages( int $post, int $limit = 5 ): array {
				$messages = array();

				$all_commits = get_post_meta( $post, 'cornell/governance/revisions/all', true );
				if ( ! is_array( $all_commits ) ) {
					$all_commits = array();
				}

				$tmp_commits = array();
				foreach( $all_commits as $key => $commit ) {
					if ( empty( $commit['commit-message'] ) ) {
						continue;
					}

					$tmp_commits[$key] = $commit;
				}
				$all_commits = $tmp_commits;

				if ( count( $all_commits ) > $limit && $limit > 0 ) {
					$all_commits = array_slice( $all_commits, ( 0 - $limit ), $limit, true );
				}

				foreach ( $all_commits as $revision_id => $commit ) {
					$messages[$revision_id] = Revisions::format_commit_message( $commit );
				}

				return $messages;
			}

			/**
			 * Perform the AJAX action
			 */
			public function ajax_save() {
				$post_id = intval( $_POST['cornell-governance-notes-post-id'] );
				if ( empty( $post_id ) ) {
					return;
				}
				$meta = get_post_meta( $post_id, $this->meta_key, true );

				if ( ! current_user_can( Plugin::instance()->get_capability() ) ) {
					return;
				}

				$post = get_post( $post_id );

				$success = $this->save( $post_id, $post, true );

				if ( is_wp_error( $success ) ) {
					wp_send_json_error( $success, 500 );
				} else {
					wp_send_json_success( $success, 200 );
				}
			}
		}
	}
}