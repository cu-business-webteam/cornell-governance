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

	if ( ! class_exists( 'Steward' ) ) {
		abstract class Steward extends Message {
			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-info-steward',
					'label' => __( 'Primary Page Steward', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-select', 'cornell-governance-steward', 'two-thirds' ),
					'default' => '',
					'meta_box' => 'Info',
				);

				$cap = Plugin::instance()->get_capability();
				if ( ! Helpers::user_can( 0,  $cap ) ) {
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
		}
	}
}