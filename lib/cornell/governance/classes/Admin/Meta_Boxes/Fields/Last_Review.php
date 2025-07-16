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

	if ( ! class_exists( 'Last_Review' ) ) {
		abstract class Last_Review extends Message {
			function __construct() {
				$next_review   = Info::instance()->get_next_review();
				$last_reviewed = Info::instance()->meta['last-review'];

				$atts = array(
					'id'       => 'cornell-governance-page-info-last-reviewed',
					/* translators: The placeholder is a formatted date showing when the piece of content was reviewed most recently */
					'label'    => sprintf( __( 'Last Reviewed: %s', 'cornell/governance' ), $last_reviewed > 0 ? date( get_option( 'date_format' ), $last_reviewed ) : __( 'Not yet reviewed', 'cornell/governance' ) ),
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
						$this->text = __( 'Please complete the governance information to begin the review cycle', 'cornell/governance' );
					} else {
						$this->text = sprintf( __( 'Please <a href="%s">request a consultation</a> to set up a review', 'cornell/governance' ), Plugin::instance()->build_change_form_url() );
					}
				} else {
					/* translators: The #1 placeholder is a formatted date indicating when the next governance review is due on this piece of content; the #2 placeholder is the word "is" or the word "was" depending on when the review was due */
					$this->text = sprintf(
						__( 'Your next review %2$s due by %1$s <!-- ' . PHP_EOL . ' %3$s ' . PHP_EOL . ' %4$s ' . PHP_EOL . ' -->', 'cornell/governance' ),
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