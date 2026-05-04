<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Confirm;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Mark_For_Deletion' ) ) {
		abstract class Mark_For_Deletion extends Confirm {
			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-mark-for-deletion',
					'label' => esc_html( __( 'This content is no longer necessary, and should be deleted', 'cornell-governance' ) ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-confirm', 'cornell-governance-mark-for-deletion' ),
					'default' => '',
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );
			}

			/**
			 * Retrieve and return the HTML for the input
			 */
			public function get_input(): string {
				$val = $this->get_input_value();
				if ( 1 === $val ) {
					$checked = ' checked="checked"';
				} else {
					$checked = '';
				}

				return sprintf( '<p class="%1$s">
	<input type="%4$s" name="%2$s" id="%2$s" value="1"%5$s/>
	<label for="%2$s">%3$s</label>
</p>',
					implode( ' ', $this->classes ),
					$this->id,
					$this->label,
					$this->type,
					$checked
				);
			}

			/**
			 * Retrieve the value of the input
			 */
			public function get_input_value() {
				$key = 'mark-for-deletion';
				if ( array_key_exists( $key, Info::instance()->meta ) && ! is_null( Info::instance()->meta[ $key ] ) ) {
					Helpers::log( 'Returning the meta data value for the "Mark for Deletion" field', 'info' );
					Helpers::log( print_r( Info::instance()->meta, true ), 'info' );
					if ( array_key_exists( 'marked', Info::instance()->meta[ $key ] ) ) {
						return Info::instance()->meta[ $key ]['marked'];
					}
				} else if ( isset( $_REQUEST[ $this->id ] ) ) {
					Helpers::log( 'Returning the form data value for the "Mark for Deletion" field', 'info' );
					return $_REQUEST[ $this->id ];
				}

				Helpers::log( 'Did not find any data for the Mark for Deletion field', 'info' );

				return $this->default;
			}

		}
	}
}