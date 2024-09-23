<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Message;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Request_Changes' ) ) {
		class Request_Changes extends Message {
			/**
			 * @var Request_Changes $instance holds the single instance of this class
			 * @access private
			 */
			protected static Request_Changes $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-request-changes',
					'label' => sprintf( '<a href="%1$s">%2$s</a>', Plugin::instance()->build_change_form_url(), Plugin::instance()->get_change_form_var( 'link_text' ) ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-message', 'cornell-governance-request-changes' ),
					'default' => '',
					'meta_box' => 'Info',
				);

				parent::__construct( $atts );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Request_Changes
			 * @since   0.1
			 */
			public static function instance(): Request_Changes {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}