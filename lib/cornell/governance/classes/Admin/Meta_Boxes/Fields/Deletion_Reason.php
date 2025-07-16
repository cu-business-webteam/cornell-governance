<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Textarea;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Deletion_Reason' ) ) {
		abstract class Deletion_Reason extends Textarea {
			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-deletion-reason',
					'label' => __( 'Reason for the Request', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-textarea', 'cornell-governance-deletion-reason' ),
					'default' => '',
					'meta_box' => 'Info',
				);

				$cap = Plugin::instance()->get_capability();
				if ( ! Helpers::user_can( 0,  $cap ) ) {
					$this->is_readonly = true;
				}

				parent::__construct( $atts );
			}

			/**
			 * Retrieve the value of the input
			 */
			public function get_input_value() {
				$key = 'mark-for-deletion';
				Helpers::log( print_r( Info::instance()->meta, true ), 'info' );
				if ( array_key_exists( $key, Info::instance()->meta ) && ! is_null( Info::instance()->meta[ $key ] ) ) {
					Helpers::log( 'Returning the meta data value for the "Deletion Reason" field', 'info' );
					if ( array_key_exists( 'reason', Info::instance()->meta[ $key ] ) ) {
						return Info::instance()->meta[ $key ]['reason'];
					}
				} else if ( isset( $_REQUEST[ $this->id ] ) ) {
					Helpers::log( 'Returning the form data value for the "Deletion Reason" field', 'info' );
					return $_REQUEST[ $this->id ];
				}

				Helpers::log( 'Did not find any data for the Deletion Reason field', 'info' );

				return $this->default;
			}

		}
	}
}