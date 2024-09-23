<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Input;

	if ( ! class_exists( 'Info_Timestamp' ) ) {
		class Info_Timestamp extends Input {
			/**
			 * @var Info_Timestamp $instance holds the single instance of this class
			 * @access private
			 */
			protected static Info_Timestamp $instance;
			/**
			 * @var string $original_label the label by itself without the value injected
			 */
			protected string $original_label = '';

			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-timestamp',
					'label'    => __( 'Information last updated', 'cornell/governance' ),
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-email',
						'cornell-governance-timestamp'
					),
					'default'  => '',
					'type'     => 'hidden',
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );

				$this->atts = array(
					'data-original-label' => $this->label,
				);
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Info_Timestamp
			 * @since   0.1
			 */
			public static function instance(): Info_Timestamp {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Builds the HTML for the input
			 *
			 * @return string
			 */
			public function get_input(): string {
				$value                = $this->get_input_value();
				$this->original_label = $this->label;
				$this->label          = sprintf( '%s: %s', $this->original_label, $value );

				$attributes = '';
				foreach ( $this->atts as $name => $val ) {
					$attributes .= ' ' . $name . '="' . esc_attr( $val ) . '"';
				}

				$rt = sprintf( '<p class="%1$s">
	<label for="%2$s">%3$s</label>
	<input type="%4$s" name="%2$s" id="%2$s" value="%5$s"%6$s%7$s/>
</p>',
					implode( ' ', $this->classes ),
					$this->id,
					$this->label,
					$this->type,
					$value,
					$attributes,
					$this->is_readonly ? ' readonly' : ''
				);

				$this->label = $this->original_label;

				return $rt;
			}
		}
	}
}