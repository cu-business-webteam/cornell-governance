<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Textarea;
	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\WYSIWYG;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Notes_Notes' ) ) {
		abstract class Notes_Notes extends Textarea {
			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-notes-notes',
					'label' => esc_html( __( 'Relevant documentation', 'cornell-governance' ) ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-textarea', 'cornell-governance-notes-notes' ),
					'default' => '',
					'meta_box' => 'Info',
					'wysiwyg_settings' => array(
						'teeny' => true,
					),
					/* translators: A link to a markdown cheat sheet */
					'instructions' => esc_html( sprintf( __( 'This field uses Markdown. <a href="%s" target="cheatsheet">Learn how to write basic Markdown.</a>', 'cornell-governance' ), 'https://www.markdownguide.org/cheat-sheet/' ) ),
				);

				$cap = Plugin::instance()->get_capability();
				if ( ! Helpers::user_can( 0,  $cap ) ) {
					$this->is_readonly = true;
				}

				parent::__construct( $atts );
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
				$Parsedown = new \ParsedownExtra();
				$value = $Parsedown->text($value);

				return sprintf( '
				<div class="%1$s" id="%4$s">
	<strong class="text-label">%2$s</strong>
	<div class="input-value">%3$s</div>
</div>',
					implode( ' ', $this->classes ),
					$this->label,
					stripslashes( $value ),
					$this->id
				);
			}
		}
	}
}