<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Writable {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Message;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Review_Requirements_Message' ) ) {
		Final class Review_Requirements_Message extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Review_Requirements_Message {
			/**
			 * @var Review_Requirements_Message $instance holds the single instance of this class
			 * @access private
			 */
			protected static Review_Requirements_Message $instance;

			function __construct() {
				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Review_Requirements_Message
			 * @since   0.1
			 */
			public static function instance(): Review_Requirements_Message {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}