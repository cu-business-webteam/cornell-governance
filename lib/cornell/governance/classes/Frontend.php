<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance {

	use Handlebars\Handlebars;

	if ( ! class_exists( '\Cornell\Governance\Frontend' ) ) {
		class Frontend {
			/**
			 * @var Frontend $instance holds the single instance of this class
			 * @access private
			 */
			private static Frontend $instance;

			/**
			 * Creates the Frontend object
			 *
			 * @access private
			 * @since  0.5.8
			 */
			private function __construct() {
				add_action( 'wp_print_footer_scripts', array( $this, 'do_compliance_widget' ) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Frontend
			 * @since  0.5.8
			 */
			public static function instance(): Frontend {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Determine whether the widget is enabled or not
			 *
			 * @access private
			 * @return bool whether it should be active
			 * @since  0.5.9
			 */
			private function is_enabled(): bool {
				$enabled = Plugin::instance()->get_frontend_compliance_active();

				if ( ! $enabled || is_admin() ) {
					return false;
				}

				return true;
			}

			/**
			 * Output the necessary code to generate the compliance widget for the page
			 *
			 * @access public
			 * @return void
			 * @since  0.5.8
			 */
			public function do_compliance_widget() {
				if ( ! $this->is_enabled() ) {
					return;
				}

				$vars = $this->get_data();

				Helpers::log( 'Frontend Widget Vars: ' . print_r( $vars, true ) );

				if ( $vars['compliant'] ) {
					return;
				}

				$template = $this->get_template();

				$partialsDir = Helpers::plugins_path( '/lib/cornell/governance/classes/Frontend/Templates' );

				$partialsLoader = new \Handlebars\Loader\FilesystemLoader(
					$partialsDir,
					[
						"extension" => 'handlebars',
					]
				);

				$handlebars = new \Handlebars\Handlebars();

				echo $handlebars->render( $template, $vars );
			}

			/**
			 * Build and return the data for the Handlebars template
			 *
			 * @access private
			 * @return array the template variables
			 * @since  0.5.8
			 */
			private function get_data(): array {
				global $post;
				if ( is_numeric( $post ) ) {
					$post = get_post( $post );
				}

				if ( ! current_user_can( 'edit_post', $post->ID ) ) {
					return array( 'compliant' => true );
				}

				$meta = get_post_meta( $post->ID, Plugin::INFO_META_KEY, true );
				if ( ! is_array( $meta ) ) {
					return array( 'compliant' => true );
				}
				if ( ! array_key_exists( 'initial-setup', $meta ) ) {
					return array( 'compliant' => true );
				}
				$last_reviewed = array_key_exists( 'last-review', $meta ) ? $meta['last-review'] : false;
				if ( empty( $last_reviewed ) ) {
					$last_reviewed = strtotime( '-1 year' );
				}

				$next_review = Helpers::calculate_next_review_date( $last_reviewed, $meta['review-cycle'] );

				$compliance_time = 60;

				$due_date      = \DateTime::createFromFormat( 'U', $next_review );
				$now_date      = new \DateTime();
				try {
					$compare = new \DateInterval( 'P' . $compliance_time . 'D' );
				} catch ( \Exception $e ) {
					Helpers::log( 'Compliance time error: ' . $e->getMessage() );
					$compare = null;
					return array();
				}
				try {
					$secondcompare = new \DateInterval( 'P7D' );
				} catch ( \Exception $e ) {
					Helpers::log( 'Compliance time error: ' . $e->getMessage() );
					$secondcompare = null;
					return array();
				}

				$overdue   = ( $now_date >= $due_date );
				$due       = ( $now_date->add( $compare ) >= $due_date );
				$almostdue = ( $now_date->add( $secondcompare ) >= $due_date );

				$data = array(
					'compliant'         => true,
					'overdue'           => false,
					'due'               => false,
					'almostdue'         => false,
					'compliance-status' => 'compliant',
				);

				$data['post_id']       = $post->ID;
				$data['post_title']    = $post->post_title;
				$data['last-reviewed'] = $last_reviewed;
				$data['edit-link']     = get_edit_post_link( $post->ID, 'link' );

				if ( $overdue ) {
					$data['compliant']         = false;
					$data['overdue']           = true;
					$data['compliance-status'] = 'overdue';
				} else if ( $due ) {
					$data['compliant']         = false;
					$data['due']               = true;
					$data['compliance-status'] = 'due';
				} else if ( $almostdue ) {
					$data['compliant']         = false;
					$data['almostdue']         = true;
					$data['compliance-status'] = 'almostdue';
				}

				return apply_filters( 'cornell/governance/frontend/template/compliance-widget/data', $data );
			}

			/**
			 * Attempt to retrieve the appropriate Handlebars template for the Compliance widget
			 *
			 * @access private
			 * @return string the template location
			 * @since  0.5.8
			 */
			private function get_template(): string {
				$file = '';

				// Look for the appropriate template file in the theme
				$test_child_theme_file_name = get_stylesheet_directory() . '/cornell-governance/templates/frontend/compliance-widget.handlebars';
				// Look for the appropriate template in a possible parent them
				$test_parent_theme_file_name = get_template_directory() . '/cornell-governance/templates/frontend/compliance-widget.handlebars';
				// Look for the appropriate template file in this plugin
				$test_plugin_file_name = Helpers::plugins_path( '/lib/cornell/governance/classes/Frontend/Templates/compliance-widget.handlebars' );

				if ( file_exists( $test_child_theme_file_name ) ) {
					$file = $test_child_theme_file_name;
				} else if ( file_exists( $test_parent_theme_file_name ) ) {
					$file = $test_parent_theme_file_name;
				} else if ( file_exists( $test_plugin_file_name ) ) {
					$file = $test_plugin_file_name;
				}

				$file = apply_filters( 'cornell/governance/frontend/template/compliance-widget/template-file', $file );;

				if ( empty( $file ) ) {
					return '';
				}

				ob_start();
				include $file;

				return ob_get_clean();
			}
		}
	}
}