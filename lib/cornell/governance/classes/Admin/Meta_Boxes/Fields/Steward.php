<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Message;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Steward' ) ) {
		class Steward extends Message {
			/**
			 * @var Steward $instance holds the single instance of this class
			 * @access private
			 */
			protected static Steward $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-steward',
					'label' => __( 'Primary Page Steward', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-select', 'cornell-governance-steward', 'two-thirds' ),
					'default' => '',
					'meta_box' => 'Info',
				);

				$cap = Plugin::instance()->get_capability();
				if ( ! current_user_can( $cap ) ) {
					$this->is_readonly = true;
				}

				parent::__construct( $atts );

				$author = get_post_field( 'post_author', $_REQUEST['post'] );

				$displayname = get_the_author_meta( 'display_name', $author );
				if ( empty( $displayname ) ) {
					$displayname = get_the_author_meta( 'nickname', $author );
				}
				if ( empty( $displayname ) ) {
					$displayname = get_the_author_meta( 'first_name', $author ) . ' ' . get_the_author_meta( 'last_name', $author );
				}
				if ( empty( $displayname ) ) {
					$displayname = get_the_author_meta( 'user_login', $author );
				}

				$email = get_the_author_meta( 'email', $author );

				$this->text = sprintf( '%s &lt;%s&gt;', $displayname, $email );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Steward
			 * @since   0.1
			 */
			public static function instance(): Steward {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}