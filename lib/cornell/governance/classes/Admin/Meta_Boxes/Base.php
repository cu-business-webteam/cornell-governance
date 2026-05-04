<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes {

	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Base' ) ) {
		abstract class Base {
			/**
			 * @var string $id the HTML ID for this input
			 */
			protected string $id;
			/**
			 * @var string $title the plain-language title/label for the input
			 */
			protected string $title;
			/**
			 * @var string $section the settings section in which to output the field
			 */
			protected string $context;
			/**
			 * @var string $class CSS Class to be added to the <tr> element when the field is output
			 */
			protected string $priority;
			/**
			 * @var array $fields the list of fields in this meta box
			 */
			protected array $fields;
			/**
			 * @var string $meta_key the post meta key for this information
			 */
			protected string $meta_key;
			/**
			 * @var array|\WP_Error $meta holds the post meta for this item
			 */
			public $meta;

			/**
			 * @var string $namespace the current namespace name
			 * @access private
			 */
			private static string $namespace;

			/**
			 * @var \WP_Screen|null the current screen being accessed
			 * @access protected
			 */
			protected ?\WP_Screen $current_screen = null;

			/**
			 * Construct our Input object
			 *
			 * @param array $atts the input attributes
			 */
			public function __construct( array $atts = array() ) {
				self::$namespace = __NAMESPACE__;
				foreach ( array( 'id', 'title', 'context', 'priority', 'fields', 'meta_key' ) as $k ) {
					if ( array_key_exists( $k, $atts ) ) {
						$this->{$k} = $atts[ $k ];
					}
				}

				add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
				/*add_action( 'save_post', array( $this, 'save' ), 11, 3 );*/

				$this->current_screen = get_current_screen();

				if ( ! is_null( $this->current_screen ) ) {
					$screen_id = $this->current_screen->id;

					add_filter( "postbox_classes_{$screen_id}_{$this->id}", array( $this, 'meta_box_classes' ) );
				}
			}

			/**
			 * Add a generic class to this meta box
			 *
			 * @param array $classes the existing list of classes
			 *
			 * @access public
			 * @return array the updated array of classes
			 * @since  1.0.16
			 */
			public function meta_box_classes( array $classes ): array {
				$classes[] = 'cornell-governance-metabox';

				return $classes;
			}

			/**
			 * Unregisters the metabox if necessary
			 *
			 * @access protected
			 * @return void
			 * @since  2023.05
			 */
			protected function unhook_metabox() {
				remove_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
			}

			/**
			 * Retrieves the meta key
			 *
			 * @access public
			 * @return string the meta key
			 * @since  0.1
			 */
			public function get_meta_key(): string {
				return $this->meta_key;
			}

			/**
			 * Retrieves the field ID
			 *
			 * @access public
			 * @return string the field ID
			 * @since  0.1
			 */
			public function get_field_id(): string {
				return $this->id;
			}

			/**
			 * Open a new fieldset
			 *
			 * @param string|array $class the CSS class names for the fieldset
			 * @param string $legend the text for the fieldset legend
			 *
			 * @access protected
			 * @since  0.5.0
			 * @return string the HTML for the fieldset opening
			 */
			protected function fieldset_open( $class, string $legend ): string {
				if ( is_array( $class ) ) {
					$class = implode( ' ', $class );
				}

				return sprintf( '<fieldset class="%1$s"><legend>%2$s</legend>', $class, $legend );
			}

			/**
			 * Build and return the closing tags for an open fieldset
			 *
			 * @access protected
			 * @since  0.5.0
			 * @return string the HTML tags
			 */
			protected function fieldset_close(): string {
				return '</fieldset>';
			}

			/**
			 * Retrieves the value of a specific piece of meta data
			 *
			 * @param string $key the meta key to retrieve
			 *
			 * @access public
			 * @return mixed the piece of meta data
			 * @since  2023.05
			 */
			public function get_meta_value( string $key = '' ) {
				if ( empty( $key ) ) {
					return false;
				}

				if ( array_key_exists( $key, $this->meta ) ) {
					return $this->meta[ $key ];
				}

				return false;
			}

			/**
			 * Register the meta box
			 *
			 * @param string $post_type the slug to the screen being shown
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function add_meta_box( string $post_type ): void {
				$types = Plugin::instance()->get_post_types();

				if ( ! in_array( $post_type, $types ) ) {
					return;
				}

				add_meta_box(
					$this->id,
					$this->title,
					array( $this, 'do_meta_box' ),
					$post_type,
					$this->context,
					$this->priority
				);
			}

			/**
			 * Build the content of the meta box
			 *
			 * @access protected
			 * @return string the HTML for the meta box content
			 * @since  0.1
			 */
			abstract protected function get_meta_box(): string;

			/**
			 * Output the HTML for the meta box
			 */
			public function do_meta_box(): void {
				echo $this->get_meta_box();
			}

			/**
			 * Save the contents of the meta box
			 *
			 * @param int $post_id The ID of the post being saved
			 * @param \WP_Post $post the post object being managed
			 * @param bool $update whether this is a new post or an updated post
			 *
			 * @access public
			 * @return mixed the updated post meta data
			 * @since  0.1
			 */
			public function save( int $post_id, \WP_Post $post, bool $update ) {
				$nonce = $this->id . '-nonce';
				if ( ! array_key_exists( $nonce, $_REQUEST ) || ! wp_verify_nonce( $_REQUEST[ $nonce ], $this->id ) ) {
					$nonce = $this->id . '-readonly-nonce';
					if ( ! array_key_exists( $nonce, $_REQUEST ) || ! wp_verify_nonce( $_REQUEST[ $nonce ], $this->id ) ) {
						Helpers::log( 'We could not verify the nonce for this page' );

						return new \WP_Error( 'no-nonce', __( 'The nonce could not be verified for some reason', 'cornell-governance' ) );
					}
				}

				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					Helpers::log( 'It appears that we are auto-saving' );

					return new \WP_Error( 'autosave', __( 'We are in the autosave portion', 'cornell-governance' ) );
				}

				// We should check user permissions here
				if ( ! current_user_can( Plugin::instance()->get_capability() ) ) {
					Helpers::log( 'It does not appear that the current user has permission to update Governance information' );

					return new \WP_Error( 'no-access', __( 'The current user does not appear to have the appropriate cap', 'cornell-governance' ) );
				}

				/*if ( $post_parent_id = wp_get_post_parent_id( $post_id ) ) {
					$post_id = $post_parent_id;
				}*/

				$data = array();
				$meta = get_post_meta( $post_id, $this->meta_key, true );
				if ( ! is_array( $meta ) || empty( $meta ) ) {
					$meta = array();
				}

				// We should sanitize data here
				foreach ( $this->fields as $key => $field ) {
					if ( array_key_exists( $this->id . '-' . $key, $_REQUEST ) ) {
						$class = self::$namespace . '\\Fields\\Writable\\' . $field;

						if ( ! class_exists( $class ) ) {
							$class = self::$namespace . '\\Fields\\' . $field;
						}

						Helpers::log( 'Preparing to evaluate the new value of: ' . $key );
						Helpers::log( 'That value should be: ' . $_REQUEST[ $this->id . '-' . $key ] );
						$data[ $key ] = $class::instance()->validate( $_REQUEST[ $this->id . '-' . $key ] );
					} else {
						Helpers::log( 'We could not find ' . $this->id . '-' . $key . ' in the REQUEST array' );
					}
				}

				/* If this is the info meta box, we need to keep the last-review value in the meta */
				if ( ( is_array( $data ) && ! array_key_exists( 'last-review', $data ) ) && ( is_array( $meta ) && array_key_exists( 'last-review', $meta ) ) ) {
					$data['last-review'] = $meta['last-review'];
				}

				if ( is_array( $data ) && ! array_key_exists( 'initial-setup', $data ) && is_array( $meta ) && array_key_exists( 'initial-setup', $meta ) ) {
					$data['initial-setup'] = $meta['initial-setup'];
				}

				/* If this is the info meta box, and the user has specifically indicated they've completed the review, reset the last-review time */
				/*if ( array_key_exists( 'cornell-governance-page-completed-review', $_REQUEST ) && 1 == $_REQUEST['cornell-governance-page-completed-review'] ) {
					$data['last-review'] = time();
				}*/

				/*if ( array_key_exists( 'cornell-governance-page-info-mark-for-deletion', $_REQUEST ) && 1 == $_REQUEST['cornell-governance-page-info-mark-for-deletion'] ) {
					$data['mark-for-deletion'] = 1;
				}*/

				/* Since we don't have a specific field for initial setup meta, we need to add it manually to keep it in the DB */
				if ( is_array( $data ) && is_array( $_REQUEST ) ) {
					if ( array_key_exists( 'cornell-governance-page-info-initial-setup', $_REQUEST ) && ! array_key_exists( 'initial-setup', $data ) ) {
						$data['initial-setup'] = array();
						foreach ( $_REQUEST['cornell-governance-page-info-initial-setup'] as $key => $val ) {
							if ( is_numeric( $val ) ) {
								$data['initial-setup'][ $key ] = $val;
							}
						}
					}
				}

				$data = array_merge( $meta, $data );

				// We should save the data here
				$success = update_post_meta( $post_id, $this->meta_key, $data );

				if ( false === $success ) {
					Helpers::log( __( 'There was an unknown error saving the post meta', 'cornell-governance' ) );

					return new \WP_Error( 'no-save', __( 'There was an unknown error saving the post meta', 'cornell-governance' ) );
				} else if ( is_wp_error( $success ) ) {
					Helpers::log( 'It appears that we successfully saved the following data: ' . print_r( $data, true ) );

					return $success;
				}

				return $data;
			}
		}
	}
}