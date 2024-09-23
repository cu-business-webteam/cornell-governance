<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Field_Types {
	if ( ! class_exists( 'Message' ) ) {
		abstract class Message extends Base {
			/**
			 * @var string $type the input type
			 */
			protected string $type = 'message';
			/**
			 * @var string $text the content of the message
			 */
			protected string $text='';

			/**
			 * Builds the HTML for the input
			 *
			 * @return string
			 */
			public function get_input(): string {
				$template = '<div class="%1$s" id="%4$s">';
				if ( ! empty( $this->label ) ) {
					$template .= '<p class="cornell-governance-message-title">%2$s</p>';
				}
				if ( ! empty( $this->text ) ) {
					$template .= '<div class="cornell-governance-message-text">%3$s</div>';
				}
				$template .= '</div>';
				return sprintf( $template,
					implode( ' ', $this->classes ),
					$this->label,
					$this->text,
					$this->id
				);
			}

			/**
			 * Validate the value of the input and prepare it for the DB
			 *
			 * @param mixed $value the current value of the field
			 *
			 * @access public
			 * @since  0.1
			 * @return mixed the sanitized value
			 */
			public function validate( $value ) {
				return $value;
			}
		}
	}
}