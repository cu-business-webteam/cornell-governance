<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Message;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Commit_Message_Message' ) ) {
		class Commit_Message_Message extends Message {
			/**
			 * @var Commit_Message_Message $instance holds the single instance of this class
			 * @access private
			 */
			protected static Commit_Message_Message $instance;

			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-revisions-commit-message-microcopy',
					'label'    => '',
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-message',
						'cornell-governance-commit-message-microcopy'
					),
					'meta_box' => 'Revisions',
				);

				parent::__construct( $atts );

				$this->text = esc_html( __( 'Use this field to document content changes you\'ve made, e.g. \'update instructor image\' or \'changed page tile\'.', 'cornell-governance' ) );
			}


			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Commit_Message_Message
			 * @since   0.1
			 */
			public static function instance(): Commit_Message_Message {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}