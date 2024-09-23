<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails\Templates {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Compliant' ) ) {
		class Compliant extends Base {
			/**
			 * @var Compliant $instance holds the single instance of this class
			 * @access private
			 */
			private static Compliant $instance;

			/**
			 * Creates the Template object
			 *
			 * @param array $template_data *
			 *
			 * @access protected
			 * @since  0.1
			 */
			protected function __construct( array $template_data ) {
				$this->template_data = $template_data;
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @param array $template_data the data to be processed
			 *
			 * @access  public
			 * @return  Compliant
			 * @since   0.1
			 */
			public static function instance( array $template_data = array() ): Compliant {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className( $template_data );
				} else {
					self::$instance->template_data = $template_data;
				}

				return self::$instance;
			}


			/**
			 * @inheritDoc
			 */
			protected function get_template_vars(): array {
				$fmt = new \NumberFormatter( 'en_US', \NumberFormatter::SPELLOUT );

				$prompt_times = array(
					'initial'   => get_option( 'cornell-governance-initial-prompt-time', 60 ),
					'secondary' => get_option( 'cornell-governance-secondary-prompt-time', 30 ),
					'tertiary'  => get_option( 'cornell-governance-tertiary-prompt-time', 7 ),
				);

				$prompt_words = array(
					'initial'      => $fmt->format( $prompt_times['initial'] ),
					'secondary'    => $fmt->format( $prompt_times['secondary'] ),
					'tertiary'     => $fmt->format( $prompt_times['tertiary'] ),
					'initial-uc'   => ucfirst( $fmt->format( $prompt_times['initial'] ) ),
					'secondary-uc' => ucfirst( $fmt->format( $prompt_times['secondary'] ) ),
					'tertiary-uc'  => ucfirst( $fmt->format( $prompt_times['tertiary'] ) ),
				);

				return apply_filters( 'cornell/governance/emails/report-data', array_merge( array(
					'prompt-times'    => $prompt_times,
					'prompt-words'    => $prompt_words,
				), $this->template_data ), get_class( $this ) );
			}
		}
	}
}