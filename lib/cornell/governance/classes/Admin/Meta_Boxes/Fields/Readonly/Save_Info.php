<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Button;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Save_Info' ) ) {
		class Save_Info extends \Cornell\Governance\Admin\Meta_Boxes\Fields\Save_Info {
			/**
			 * @var Save_Info $instance holds the single instance of this class
			 * @access private
			 */
			protected static Save_Info $instance;

			function __construct() {
				parent::__construct();

				$this->label = __( 'Confirm Page Review', 'cornell/governance' );
				$this->is_readonly = true;
				$this->id = 'cornell-governance-page-info-save-readonly';

				if ( in_array( 'cornell-governance-save-governance-settings', $this->classes ) ) {
					foreach ( $this->classes as $k => $v ) {
						if ( 'cornell-governance-save-governance-settings' === $v ) {
							unset( $this->classes[$k] );
						}
					}
				}

				if ( ! in_array( 'cornell-governance-confirm-page-review', $this->classes ) ) {
					$this->classes[] = 'cornell-governance-confirm-page-review';
				}
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Save_Info
			 * @since   0.1
			 */
			public static function instance(): Save_Info {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}