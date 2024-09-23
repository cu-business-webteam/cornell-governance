<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Fields {

	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Default_Tasks' ) ) {
		class Default_Tasks extends Base {
			/**
			 * @var bool $did_sanitize determines whether we've already sanitized the field value or not, since
			 *      WordPress tends to run this callback twice
			 */
			protected static bool $did_sanitize = false;
			/**
			 * @var Default_Tasks $instance holds the single instance of this class
			 * @access private
			 */
			protected static Default_Tasks $instance;
			/**
			 * @var string $add_text the text that should be used for the "Add New" button
			 */
			protected string $add_text;
			/**
			 * @var string $remove_text the text that should be used for the "Remove" button
			 */
			protected string $remove_text;
			/**
			 * @var string $description additional help text that should be displayed in the field
			 */
			protected string $description;
			/**
			 * @var string $short_name the text that should prefix the item number for each individual field label
			 */
			protected string $short_name;
			/**
			 * @var boolean $sortable whether this list should be sortable or not
			 */
			protected bool $sortable = false;

			/**
			 * Construct this input
			 */
			protected function __construct() {
				parent::__construct( array(
					'id'      => 'default-tasks',
					'title'   => __( 'Global Tasks', 'cornell/governance' ),
					'page'    => 'cornell-governance',
					'section' => 'cornell-governance-settings',
					'class'   => 'cornell-governance-admin-field cornell-governance-admin-repeater',
					'default' => $this->get_default_content(),
				) );

				$this->add_text    = __( 'Add New Task', 'cornell/governance' );
				$this->remove_text = __( 'Remove This Task', 'cornell/governance' );
				$this->description = __( 'If there are on-page content review tasks that should be included on every page, add them here:', 'cornell/governance' );
				$this->short_name  = __( 'Task', 'cornell/governance' );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Default_Tasks
			 * @since   0.1
			 */
			public static function instance(): Default_Tasks {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Build the default content for this field
			 *
			 * @access private
			 * @return array the default content
			 * @since  0.1
			 */
			private function get_default_content(): array {
				return array();
			}

			/**
			 * Build the input
			 *
			 * @access protected
			 * @return string
			 * @since  0.1
			 */
			protected function get_input(): string {
				$value = $this->get_input_value();
				if ( empty( $value ) ) {
					$value = array( '' );
				}

				$fields = array();

				$id = $this->page . '-' . $this->id;

				$i = 1;
				foreach ( $value as $item ) {
					$fields[ $i ] = sprintf( '<li class="repeater-field" data-number="%5$d">
	<label for="%1$s_%5$d">
		%2$s %5$d
	</label>
	<div class="repeater-input-container">
		<input type="%3$s" name="%4$s[%5$d]" id="%1$s_%5$d" value="%6$s"/>
	</div>
</li>',
						$id,
						$this->short_name,
						$this->type,
						$id,
						$i,
						$item
					);

					$i ++;
				}

				$list_classes = array(
					'repeater-field-set',
				);

				if ( $this->sortable ) {
					$list_classes[] = 'sortable';
				}

				return sprintf( '<fieldset class="%1$s">
	<legend data-shortname="%4$s">
		%2$s
	</legend>
	<ol class="%8$s" data-add-text="%5$s" data-remove-text="%6$s" data-root-id="%7$s">
		%3$s
	</ol>
</fieldset>',
					$this->class,
					empty( $this->description ) ? $this->title : $this->description,
					implode( ' ', $fields ),
					empty( $this->short_name ) ? esc_attr( $this->title ) : esc_attr( $this->short_name ),
					$this->add_text,
					$this->remove_text,
					$id,
					implode( ' ', $list_classes )
				);
			}

			/**
			 * Validate the value of this input
			 *
			 * @param mixed $value the new value to be validated
			 *
			 * @return mixed the sanitized/validated value for the field
			 * @access public
			 * @since  0.1
			 */
			public function validate_field( $value ) {
				if ( self::$did_sanitize ) {
					return $value;
				}

				self::$did_sanitize = true;

				return array_map( 'sanitize_text_field', $value );
			}
		}
	}
}