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

	if ( ! class_exists( '\Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly\Audiences_Fieldset_Message' ) ) {
		Final class Audiences_Fieldset_Message extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Audiences_Fieldset_Message {
			/**
			 * @var Audiences_Fieldset_Message $instance holds the single instance of this class
			 * @access private
			 */
			protected static Audiences_Fieldset_Message $instance;

			function __construct() {
				parent::__construct();

				$this->id = $this->id . '-readonly';

				$this->is_readonly = true;

				$this->text = esc_html( __( 'Verify that these are still the correct target audiences.', 'cornell-governance' ) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Audiences_Fieldset_Message
			 * @since   0.1
			 */
			public static function instance(): Audiences_Fieldset_Message {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}