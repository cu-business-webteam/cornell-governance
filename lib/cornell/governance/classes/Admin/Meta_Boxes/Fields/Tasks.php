<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes\Fields {

	use Cornell\Governance\Admin\Fields\Default_Tasks;
	use Cornell\Governance\Admin\Meta_Boxes\Field_Types\Repeater;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Tasks' ) ) {
		class Tasks extends Repeater {
			/**
			 * @var Tasks $instance holds the single instance of this class
			 * @access private
			 */
			protected static Tasks $instance;

			function __construct() {
				$atts = array(
					'id'       => 'cornell-governance-page-info-tasks',
					'label'    => __( 'On-Page Content Review Tasks', 'cornell/governance' ),
					'classes'  => array(
						'cornell-governance-field',
						'cornell-governance-repeater',
						'cornell-governance-tasks'
					),
					'default'  => array( '' ),
					'type'     => 'text',
					'meta_box' => 'Info',
				);

				$cap = Plugin::instance()->get_capability();
				if ( ! current_user_can( $cap ) ) {
					$this->is_readonly = true;
				}

				parent::__construct( $atts );

				$this->add_text    = __( 'Add New Task', 'cornell/governance' );
				$this->remove_text = __( 'Remove This Task', 'cornell/governance' );
				$this->short_name = __( 'Task', 'cornell/governance' );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Tasks
			 * @since   0.1
			 */
			public static function instance(): Tasks {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Build the input
			 *
			 * @access public
			 * @return string the HTML for the input
			 * @since  2023.05
			 */
			public function get_input(): string {
				if ( ! $this->is_readonly ) {
					return parent::get_input();
				} else {
					$options    = array();

					foreach ( $this->get_options() as $val => $label ) {
						$options[] = sprintf( '
<label for="%2$s_%1$s"%6$s>
	<input type="checkbox" name="%2$s[%1$s]" id="%2$s_%1$s" value="%1$s" %3$s%5$s/> 
	%4$s
</label>',
							$val,
							$this->id,
							in_array( $val, $this->get_completed() ) ? ' checked' : '',
							$label,
							'',
							in_array( $val, $this->get_completed() ) ? ' class="done"' : ''
						);
					}

					return sprintf( '<fieldset class="%1$s">
	<legend>%3$s</legend>
	<div class="%2$s">
		%4$s
	</div>
</fieldset>',
						implode( ' ', $this->classes ),
						$this->id,
						$this->label,
						implode( "\n\r", $options )
					);
				}
			}

			/**
			 * Retrieve a list of completed tasks
			 *
			 * @access public
			 * @return array the list of completed tasks
			 * @since 2023.05
			 */
			public function get_completed(): array {
				$completed = Info::instance()->get_meta_value( 'completed-tasks' );
				if ( ! is_array( $completed ) ) {
					return array();
				}

				return $completed;
			}

			/**
			 * Retrieve the default tasks set within the plugin settings
			 *
			 * @access protected
			 * @since  2023.05
			 * @return array the list of options
			 */
			protected function get_defaults(): array {
				$options = array();

				$defaults = @array_filter( apply_filters( 'cornell/governance/default-tasks', Default_Tasks::instance()->get_input_value() ) );

				foreach ( $defaults as $item ) {
					$options[ sanitize_title( $item ) ] = $item;
				}

				return $options;
			}

			/**
			 * Build and return the options array for this element
			 *
			 * @access protected
			 * @return array the list of options
			 * @since  2023.05
			 */
			protected function get_options(): array {
				$options = array();

				if ( $this->is_readonly ) {
					$options = $this->get_defaults();
				}

				$values = $this->get_input_value();
				foreach ( $values as $value ) {
					$options[ sanitize_title( $value ) ] = $value;
				}

				return $options;
			}
		}
	}
}