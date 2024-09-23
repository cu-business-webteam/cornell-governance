<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Textarea;
	use Cornell\Governance\Admin\Meta_Boxes\Revisions;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Commit_Message' ) ) {
		class Commit_Message extends Textarea {
			/**
			 * @var Commit_Message $instance holds the single instance of this class
			 * @access private
			 */
			protected static Commit_Message $instance;

			function __construct() {
				$atts = array(
					'id' => 'cornell-governance-page-revisions-commit-message',
					'label' => __( 'What changes are you making to this page?', 'cornell/governance' ),
					'classes' => array( 'cornell-governance-field', 'cornell-governance-textarea', 'cornell-governance-commit-message' ),
					'default' => '',
					'meta_box' => 'Revisions',
				);

				add_filter( 'cornell/governance/textarea/value', array( $this, 'blank_textarea' ), 10, 2 );

				parent::__construct( $atts );

				if ( ! isset( $this->atts ) || ! is_array( $this->atts ) ) {
					$this->atts = array();
				}

				$this->atts['rows'] = 3;
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Commit_Message
			 * @since   0.1
			 */
			public static function instance(): Commit_Message {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Output the Commit Message on the revision screen
			 *
			 * @param string $revision_field the existing value of the field
			 * @param string $field The current revision field
			 * @param \WP_Post $compare_from The revision post object to compare to or from
			 * @param string $context The context of whether the current revision is the old or the new one. Values are 'to' or 'from'
			 *
			 * @access public
			 * @since  0.1
			 * @return string the updated value of the field
			 */
			public function revision_field( string $revision_field, string $field, \WP_Post $compare_from, string $context ): string {
				$data = get_metadata( 'post', $compare_from->ID, Revisions::instance()->get_meta_key(), true );

				if ( ! is_array( $data ) || ! array_key_exists( 'commit-message', $data ) ) {
					return '';
				}

				return $data['commit-message'];
			}

			/**
			 * Blank out the textarea value for this field
			 *
			 * @param string $value the existing value for the field
			 * @param string $field_id the field HTML ID
			 *
			 * @access public
			 * @since  0.1
			 * @return string the blank value
			 */
			public function blank_textarea( string $value, string $field_id='' ): string {
				if ( 'cornell-governance-page-revisions-commit-message' !== $field_id ) {
					return $value;
				}
				return '';
			}
		}
	}
}