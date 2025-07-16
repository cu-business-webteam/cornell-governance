<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Message;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Focus_Areas_Fieldset_Message' ) ) {
		Final class Focus_Areas_Fieldset_Message extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Focus_Areas_Fieldset_Message {
			/**
			 * @var Focus_Areas_Fieldset_Message $instance holds the single instance of this class
			 * @access private
			 */
			protected static Focus_Areas_Fieldset_Message $instance;

			function __construct() {
				parent::__construct();

				$this->id = $this->id . '-readonly';

				$this->is_readonly = true;

				$this->text = __( 'Ensure the content of the page meets goals and supports the target audiences’ needs.', 'cornell/governance' );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Focus_Areas_Fieldset_Message
			 * @since   0.1
			 */
			public static function instance(): Focus_Areas_Fieldset_Message {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}