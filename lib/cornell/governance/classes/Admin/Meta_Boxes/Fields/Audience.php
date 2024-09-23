<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Select;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Audience' ) ) {
		abstract class Audience extends Select {
			function __construct( array $atts = array() ) {
				if ( array_key_exists( 'classes', $atts ) ) {
					$atts['classes'] = array_merge( $atts['classes'], array(
						'cornell-governance-field',
						'cornell-governance-select',
						'cornell-governance-one-half'
					) );
				}

				$cap = Plugin::instance()->get_capability();
				if ( ! current_user_can( $cap ) ) {
					$this->is_readonly = true;
				}

				parent::__construct( $atts );
			}

			/**
			 * Gather the terms within the Audience taxonomy
			 *
			 * @access protected
			 * @since  0.1
			 * @return array the list of Audiences
			 */
			protected function get_audiences(): array {
				return get_terms( array(
					'taxonomy' => 'audience',
					'hide_empty' => false,
					'orderby' => 'name',
				) );
			}

			/**
			 * Gathers the options for this select element
			 *
			 * @return array the array of options
			 */
			public function get_options(): array {
				$options = array( '' => __( '-- Please select an audience --', 'cornell-governance' ) );
				foreach ( $this->get_audiences() as $audience ) {
					$options[$audience->slug] = $audience->name;
				}

				return $options;
			}
		}
	}
}