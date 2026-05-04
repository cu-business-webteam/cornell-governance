<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Message;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Last_Review' ) ) {
		abstract class Last_Review extends Message {
			function __construct() {
				$next_review   = Info::instance()->get_next_review();
				$last_reviewed = Info::instance()->meta['last-review'];

				$atts = array(
					'id'       => 'cornell-governance-page-info-last-reviewed',
					/* translators: The placeholder is a formatted date showing when the piece of content was reviewed most recently */
					'label'    => esc_html( sprintf( __( 'Last Reviewed: %s', 'cornell-governance' ), $last_reviewed > 0 ? date( get_option( 'date_format' ), $last_reviewed ) : __( 'Not yet reviewed', 'cornell-governance' ) ) ),
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-message',
						'cornell-governance-last-reviewed'
					),
					'meta_box' => 'Info',
				);

				$cap = Plugin::instance()->get_capability();
				if ( ! Helpers::user_can( 0,  $cap ) ) {
					$this->is_readonly = true;
				}

				parent::__construct( $atts );

				if ( $last_reviewed <= 0 ) {
					if ( Helpers::user_can( 0,  Plugin::instance()->get_capability() ) ) {
						$this->text = esc_html( __( 'Please complete the governance information to begin the review cycle', 'cornell-governance' ) );
					} else {
						/* translators: the link to the change request form as set in this plugin's settings */
						$this->text = esc_html( sprintf( __( 'Please <a href="%s">request a consultation</a> to set up a review', 'cornell-governance' ), Plugin::instance()->build_change_form_url() ) );
					}
				} else {
					$this->text = sprintf(
						/* translators: The #1 placeholder is a formatted date indicating when the next governance review is due on this piece of content; the #2 placeholder is the word "is" or the word "was" depending on when the review was due */
						__( 'Your next review %2$s due by %1$s <!-- ', 'cornell-governance' ) . PHP_EOL . ' %3$s ' . PHP_EOL . ' %4$s ' . PHP_EOL . ' -->',
						date( get_option( 'date_format' ), $next_review ),
						$next_review < time() ? 'was' : 'is',
						$next_review,
						date( 'c', $next_review )
					);
				}
			}
		}
	}
}