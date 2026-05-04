<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Input;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Helpers;
	use DateTimeZone;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Deletion_Timestamp' ) ) {
		class Deletion_Timestamp extends Input {
			/**
			 * @var Deletion_Timestamp $instance holds the single instance of this class
			 * @access private
			 */
			protected static Deletion_Timestamp $instance;
			/**
			 * @var string $original_label the label by itself without the value injected
			 */
			protected string $original_label = '';

			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-deletion-timestamp',
					'label'    => esc_html( __( 'Deletion request last updated', 'cornell-governance' ) ),
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-input',
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
			 * @return  Deletion_Timestamp
			 * @since   0.1
			 */
			public static function instance(): Deletion_Timestamp {
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
				$label_value          = Helpers::format_date_time( Helpers::get_date_time( $value ) );

				$attributes = '';
				foreach ( $this->atts as $name => $val ) {
					$attributes .= ' ' . $name . '="' . esc_attr( $val ) . '"';
				}

				$meta      = Info::instance()->get_meta_value( 'mark-for-deletion' );
				$requestor = array_key_exists( 'requestor', $meta ) ? $meta['requestor'] : '';
				if ( ! empty( $requestor ) ) {
					$user = get_user( $requestor );
				} else {
					$user = (object) array( 'user_email' => '' );
				}

				$rt = sprintf( '<p class="%1$s">
	<label for="%2$s">%3$s By %8$s</label>
	<input type="%4$s" name="%2$s" id="%2$s" value="%5$s"%6$s%7$s/>
</p>',
					implode( ' ', $this->classes ),
					$this->id,
					str_replace( $value, $label_value, $this->label ),
					$this->type,
					$value,
					$attributes,
					$this->is_readonly ? ' readonly' : '',
					$user->user_email
				);

				$this->label = $this->original_label;

				return $rt;
			}

			/**
			 * Retrieve the value of the input
			 */
			public function get_input_value() {
				$key = 'mark-for-deletion';
				if ( array_key_exists( $key, Info::instance()->meta ) && ! is_null( Info::instance()->meta[ $key ] ) ) {
					Helpers::log( 'Returning the meta data value for the "Deletion Timestamp" field', 'info' );
					Helpers::log( print_r( Info::instance()->meta, true ), 'info' );
					if ( array_key_exists( 'timestamp', Info::instance()->meta[ $key ] ) ) {
						return Info::instance()->meta[ $key ]['timestamp'];
					}
				} else if ( isset( $_REQUEST[ $this->id ] ) ) {
					Helpers::log( 'Returning the form data value for the "Deletion Timestamp" field', 'info' );

					return $_REQUEST[ $this->id ];
				}

				Helpers::log( 'Did not find any data for the Deletion Timestamp field', 'info' );

				return $this->default;
			}
		}
	}
}