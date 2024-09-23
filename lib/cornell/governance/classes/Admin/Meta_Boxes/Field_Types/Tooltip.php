<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Field_Types {
	if ( ! class_exists( 'Tooltip' ) ) {
		abstract class Tooltip extends Base {
			/**
			 * @var string $type the input type
			 */
			protected string $type = 'tooltip';
			/**
			 * @var array $atts the extra attributes for the input
			 */
			protected array $atts = array();
			/**
			 * @var string $content the content that appears in the tooltip
			 */
			protected string $content;

			/**
			 * Construct our tooltip object
			 *
			 * @param array $atts the attributes to assign to this object
			 *
			 * @access protected
			 */
			protected function __construct( array $atts = array() ) {
				parent::__construct( $atts );

				$this->content = $this->get_content();
			}

			/**
			 * Builds the HTML for the input
			 *
			 * @return string
			 */
			public function get_input(): string {
				if ( empty ( $this->content ) ) {
					return '';
				}

				$classes = array(
					'container' => array_merge(
						array(
							'governance-tooltip-container',
						),
						$this->classes
					),
					'opener'    => array(
						'governance-tooltip-opener',
					),
					'tooltip'   => array(
						'governance-tooltip',
					),
				);

				$format = '<div class="%1$s" data-purpose="tooltip-container">';
				$format .= '<button class="%2$s" aria-describedby="%3$s">%4$s</button>';
				$format .= '<div role="tooltip" id="%3$s" class="%5$s"><button class="tooltip-closer" aria-label="%7$s" rel="%3$s"></button><div class="tooltip-content">%6$s</div></div>';
				$format .= '</div>';

				return sprintf( $format,
					implode( ' ', $classes['container'] ),
					implode( ' ', $classes['opener'] ),
					$this->id,
					$this->label,
					implode( ' ', $classes['tooltip'] ),
					$this->content,
					__( 'Close this tooltip', 'cornell/governance' )
				);
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
				return sanitize_textarea_field( $value );
			}

			/**
			 * Retrieve the content of the tooltip dialog
			 *
			 * @access protected
			 * @since  2023.05
			 * @return string the HTML content of the tooltip dialog
			 */
			abstract protected function get_content(): string;
		}
	}
}