<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Field_Types {
	if ( ! class_exists( 'WYSIWYG' ) ) {
		abstract class WYSIWYG extends Textarea {
			/**
			 * @var array $wysiwyg_settings {
			 *      Array of editor arguments.
			 *
			 * @type bool $wpautop Whether to use wpautop(). Default true.
			 * @type bool $media_buttons Whether to show the Add Media/other media buttons.
			 * @type string $default_editor When both TinyMCE and Quicktags are used, set which
			 *          editor is shown on page load. Default empty.
			 * @type bool $drag_drop_upload Whether to enable drag & drop on the editor uploading. Default false.
			 *          Requires the media modal.
			 * @type string $textarea_name Give the textarea a unique name here. Square brackets
			 *          can be used here. Default $editor_id.
			 * @type int $textarea_rows Number rows in the editor textarea. Default 20.
			 * @type string|int $tabindex Tabindex value to use. Default empty.
			 * @type string $tabfocus_elements The previous and next element ID to move the focus to
			 *          when pressing the Tab key in TinyMCE. Default ':prev,:next'.
			 * @type string $editor_css Intended for extra styles for both Visual and Text editors.
			 *          Should include `<style>` tags, and can use "scoped". Default empty.
			 * @type string $editor_class Extra classes to add to the editor textarea element. Default empty.
			 * @type bool $teeny Whether to output the minimal editor config. Examples include
			 *          Press This and the Comment editor. Default false.
			 * @type bool $dfw Deprecated in 4.1. Unused.
			 * @type bool|array $tinymce Whether to load TinyMCE. Can be used to pass settings directly to
			 *          TinyMCE using an array. Default true.
			 * @type bool|array $quicktags Whether to load Quicktags. Can be used to pass settings directly to
			 *          Quicktags using an array. Default true.
			 * }
			 */
			protected array $wysiwyg_settings = array();
			/**
			 * @var array $allowed_tags a list of tags that should be allowed in the HTML of a WYSIWYG field
			 */
			protected array $allowed_tags = array();

			/**
			 * Construct the WYSIWYG object
			 *
			 * @param array $atts the input attributes
			 */
			public function __construct( array $atts = array() ) {
				parent::__construct( $atts );

				if ( array_key_exists( 'wysiwyg_settings', $atts ) ) {
					$this->wysiwyg_settings = $atts['wysiwyg_settings'];
				}

				if ( array_key_exists( 'allowed_tags', $atts ) ) {
					$this->allowed_tags = $atts['allowed_tags'];
				}
			}

			/**
			 * Builds the HTML for the input
			 *
			 * @return string
			 */
			public function get_input(): string {
				$value = $this->get_input_value();

				if ( $this->is_readonly ) {
					return $this->get_input_readonly( $value );
				}

				if ( is_array( $value ) ) {
					$value = implode( ' ', $value );
				}

				return $this->get_wysiwyg_input( $value );
			}

			/**
			 * Outputs text instead of an input if the readonly property is true
			 *
			 * @param mixed $value the value to output
			 *
			 * @access protected
			 * @return string the HTML output
			 * @since  0.1
			 */
			protected function get_input_readonly( $value ): string {
				return sprintf( '
				<div class="%1$s">
	<h3 class="text-label">%2$s</h3>
	<div class="input-value wysiwyg-value">%3$s</div>
</div>',
					implode( ' ', $this->classes ),
					$this->label,
					$value
				);
			}

			/**
			 * Retrieve and return a WYSIWYG editor instance
			 *
			 * @param mixed $value the value of the textarea
			 *
			 * @access private
			 * @return string the WYSIWYG editor
			 * @since  0.1
			 */
			private function get_wysiwyg_input( $value = null ): string {
				ob_start();
				wp_editor( $value, $this->id, $this->get_wysiwyg_settings() );
				return ob_get_clean();
			}

			/**
			 * Build and return the array of WYSIWYG attributes/settings
			 *
			 * @access private
			 * @return array the settings for the WYSIWYG editor
			 * @since  0.1
			 */
			private function get_wysiwyg_settings(): array {
				$atts = array();
				$keys = array(
					'wpautop',
					'media_buttons',
					'default_editor',
					'drag_drop_upload',
					'textarea_name',
					'textarea_rows',
					'tabindex',
					'tabfocus_elements',
					'editor_css',
					'editor_class',
					'teeny',
					'tinymce',
					'quicktags'
				);

				if ( count( $this->wysiwyg_settings ) ) {
					foreach ( $this->wysiwyg_settings as $key => $value ) {
						if ( in_array( $key, $keys ) ) {
							$atts[$key] = $value;
						}
					}
				}

				if ( ! array_key_exists( 'textarea_name', $atts ) ) {
					$atts['textarea_name'] = $this->id;
				}

				if ( ! array_key_exists( 'media_buttons', $atts ) ) {
					$atts['media_buttons'] = false;
				}

				return $atts;
			}

			/**
			 * Validate the value of the input and prepare it for the DB
			 *
			 * @param mixed $value the current value of the field
			 *
			 * @access public
			 * @return mixed the sanitized value
			 * @since  0.1
			 */
			public function validate( $value ) {
				return wp_kses( $value, $this->get_allowed_tags() );
			}

			/**
			 * Build and return an array of allowed tags for WYSIWYG editing
			 *
			 * @access private
			 * @since  0.1
			 * @return array the list of allowed tags
			 */
			private function get_allowed_tags(): array {
				if ( count( $this->allowed_tags ) ) {
					return $this->allowed_tags;
				}

				$tags = array();

				if ( array_key_exists( 'teeny', $this->wysiwyg_settings ) && boolval( $this->wysiwyg_settings['teeny'] ) ) {
					$tags = array(
						'a'          => array(
							'href'     => true,
							'rel'      => true,
							'rev'      => true,
							'name'     => true,
							'target'   => true,
							'download' => array(
								'valueless' => 'y',
							),
						),
						'abbr'       => array(),
						'acronym'    => array(),
						'b'          => array(),
						'blockquote' => array(
							'cite' => true,
						),
						'br'         => array(),
						'code'       => array(),
						'dd'         => array(),
						'del'        => array(
							'datetime' => true,
						),
						'dl'         => array(),
						'dt'         => array(),
						'em' => array(),
						'h2'         => array(
							'align' => true,
						),
						'h3'         => array(
							'align' => true,
						),
						'h4'         => array(
							'align' => true,
						),
						'h5'         => array(
							'align' => true,
						),
						'h6'         => array(
							'align' => true,
						),
						'hr'         => array(
							'align'   => true,
							'noshade' => true,
							'size'    => true,
							'width'   => true,
						),
						'i'          => array(),
						'ins'        => array(
							'datetime' => true,
							'cite'     => true,
						),
						'li'         => array(
							'align' => true,
							'value' => true,
						),
						'mark'       => array(),
						'ol'         => array(
							'start'    => true,
							'type'     => true,
							'reversed' => true,
						),
						'p'          => array(
							'align' => true,
							'style' => true,
						),
						'pre'        => array(
							'width' => true,
						),
						'q'          => array(
							'cite' => true,
						),
						'span' => array(
							'style' => true,
						),
						'strong'     => array(),
						'strike'     => array(),
						'sub'        => array(),
						'sup'        => array(),
						'u'          => array(),
						'ul'         => array(
							'type' => true,
						),
					);
				} else if ( array_key_exists( 'tinymce', $this->wysiwyg_settings ) && ! boolval( $this->wysiwyg_settings['tinymce'] ) ) {
					$tags = array(
						'a'          => array(
							'href'     => true,
							'rel'      => true,
							'rev'      => true,
							'name'     => true,
							'target'   => true,
							'download' => array(
								'valueless' => 'y',
							),
						),
						'code'       => array(),
						'del'        => array(
							'datetime' => true,
						),
						'em' => array(),
						'img'        => array(
							'alt'      => true,
							'align'    => true,
							'border'   => true,
							'height'   => true,
							'hspace'   => true,
							'loading'  => true,
							'longdesc' => true,
							'vspace'   => true,
							'src'      => true,
							'usemap'   => true,
							'width'    => true,
						),
						'ins'        => array(
							'datetime' => true,
							'cite'     => true,
						),
						'li'         => array(
							'align' => true,
							'value' => true,
						),
						'ol'         => array(
							'start'    => true,
							'type'     => true,
							'reversed' => true,
						),
						'p'          => array(
							'align' => true,
						),
						'pre'        => array(
							'width' => true,
						),
						'strong' => array(),
						'ul'         => array(
							'type' => true,
						),
					);
				} else {
					$tags = $GLOBALS['allowedposttags'];
				}

				if ( array_key_exists( 'media_buttons', $this->wysiwyg_settings ) && boolval( $this->wysiwyg_settings['media_buttons'] ) ) {
					$img_tags = array(
						'figure'     => array(
							'align' => true,
						),
						'figcaption' => array(
							'align' => true,
						),
						'img'        => array(
							'alt'      => true,
							'align'    => true,
							'border'   => true,
							'height'   => true,
							'hspace'   => true,
							'loading'  => true,
							'longdesc' => true,
							'vspace'   => true,
							'src'      => true,
							'usemap'   => true,
							'width'    => true,
						),
					);

					$tags = array_merge( $tags, $img_tags );
				}

				return $tags;
			}
		}
	}
}