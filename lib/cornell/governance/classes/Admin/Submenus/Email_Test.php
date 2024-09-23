<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Submenus {

	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	class Email_Test extends Base {
		/**
		 * @var string $message holds any message that needs to be printed at the top of the screen
		 * @access protected
		 */
		protected string $message = '';
		/**
		 * @var Email_Test $instance holds the single instance of this class
		 * @access private
		 */
		private static Email_Test $instance;

		/**
		 * Creates the Email_Test object
		 *
		 * @access private
		 * @since  0.1
		 */
		function __construct() {
			if ( ! is_admin() ) {
				return;
			}

			parent::__construct( array(
				'title'       => __( 'Cornell Governance: Email Testing', 'cornell/governance' ),
				'menu_name'   => __( 'Plugin Email Testing', 'cornell/governance' ),
				'slug'        => 'cornell-governance-email-test',
				'description' => __( 'Test the configuration and sending of plugin email messages', 'cornell/governance' ),
			) );

			if ( isset( $_GET['send_email'] ) ) {
				$this->send_test_mail();
			}
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Email_Test
		 * @since   0.1
		 */
		public static function instance(): Email_Test {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Set the object properties
		 *
		 * @param array $attributes the properties to assign
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function set_properties( array $attributes ) {
			parent::set_properties( $attributes );

			$this->cap = 'delete_plugins';
		}

		/**
		 * Outputs the content of the Page List
		 *
		 * @access protected
		 * @return void
		 * @since  0.1
		 */
		protected function display() {
			printf( '<div class="wrap"><h2>%s</h2><div class="cornell-governance-email-testing">', $this->title );

			printf( '<p>%s</p>', __( 'On this page, you can test the email configuration for the plugin. You can send test versions of any of the various email messages that are automatically dispatched by this plugin.', 'cornell/governance' ) );

			if ( ! empty( $this->message ) ) {
				printf( '<p class="admin-notice">%s</p>', $this->message );
			}

			$this->do_debug_display();

			printf( '<h3>%s</h3>', __( 'Email Overrides', true ) );

			$emails = array(
				'Initial_Prompt' => __( 'Initial Prompt', 'cornell/governance' ),
				'Secondary_Prompt' => __( 'Second Prompt', 'cornell/governance' ),
				'Tertiary_Prompt' => __( 'Third Prompt', 'cornell/governance' ),
				'Overdue' => __( 'Overdue Warning', 'cornell/governance' ),
			);

			echo '<form>';
			printf( '<input type="hidden" name="page" value="%s"/>', $this->slug );
			wp_nonce_field( 'cornell/governance/send-test-mail', 'cornell_governance_send_test_mail' );

			printf( '<p><label>%s</label><br/><input type="email" name="email_address"/></p>', __( 'Send the email report to the following address:', 'cornell/governance' ) );

			$authors = $this->get_authors();

			if ( count( $authors ) ) {
				echo '<fieldset>';

				_e( '<legend>Author Emails</legend>', 'cornell/governance' );

				$options = array();
				foreach ( $authors as $author ) {
					$options[$author->ID] = sprintf( '<option value="%d">%s</option>', $author->ID, $author->user_email );
				}

				printf( '<p><label>%s</label><br/><select name="author">%s</select></p>', __( 'Select the author for which the report should be generated:', 'cornell/governance' ), implode( '', $options ) );

				echo '<ul class="email-prompt-list">';

				foreach ( $emails as $class => $label ) {
					printf( '<li><input type="submit" name="send_email[%1$s][%2$s]" value="%3$s" class="button button-secondary" /></li>', 'author', $class, $label );
				}

				echo '</ul>';

				echo '</fieldset>';
			}

			array_shift( $emails );

			$supervisors = $this->get_supervisors();
			if ( count( $supervisors ) ) {
				echo '<fieldset>';
				_e( '<legend>Secondary Emails</legend>', 'cornell/governance' );

				$options = array();
				foreach ( $supervisors as $supervisor ) {
					$options[$supervisor] = sprintf( '<option value="%s">%s</option>', $supervisor, $supervisor );
				}

				printf( '<p><label>%s</label><br/><select name="supervisor">%s</select></p>', __( 'Select the supervisor for which the report should be generated:', 'cornell/governance' ), implode( '', $options ) );

				echo '<ul class="email-prompt-list">';

				foreach ( $emails as $class => $label ) {
					printf( '<li><input type="submit" name="send_email[%1$s][%2$s]" value="%3$s" class="button button-secondary" /></li>', 'supervisor', $class, $label );
				}

				echo '</ul>';
				echo '</fieldset>';
			}

			array_shift( $emails );

			$liaisons = $this->get_liaisons();

			if ( count( $liaisons ) ) {
				echo '<fieldset>';
				_e( '<legend>Liaison Emails</legend>', 'cornell/governance' );

				$options = array();
				foreach ( $liaisons as $liaison ) {
					$options[$liaison] = sprintf( '<option value="%s">%s</option>', $liaison, $liaison );
				}

				printf( '<p><label>%s</label><br/><select name="liaison">%s</select></p>', __( 'Select the liaison for which the report should be generated:', 'cornell/governance' ), implode( '', $options ) );

				echo '<ul class="email-prompt-list">';

				foreach ( $emails as $class => $label ) {
					printf( '<li><input type="submit" name="send_email[%1$s][%2$s]" value="%3$s" class="button button-secondary" /></li>', 'liaison', $class, $label );
				}

				echo '</ul>';
				echo '</fieldset>';
			}

			printf( '<input type="submit" name="send_email[all]" value="%1$s" class="button button-primary" />', __( 'Send all emails', 'cornell/governance' ) );

			echo '</form>';

			print( '</div></div>' );
		}

		/**
		 * Output some debug information
		 *
		 * @access private
		 * @since  2024.06.25
		 * @return void
		 */
		private function do_debug_display() {
			printf( '<h3>%s</h3>', __( 'Debug Information', 'cornell/governance' ) );
			printf( '<p>%s</p>', __( 'The debug constants are currently set to:', 'cornell/governance' ) );
			print( '<ul>' );

			ob_start();
			defined( 'WP_DEBUG' ) ? var_dump( WP_DEBUG ) : print('false');
			$val = ob_get_clean();
			printf( '<li><code>WP_DEBUG</code>: %s</li>', $val );

			ob_start();
			defined( 'CORNELL_DEBUG' ) ? var_dump( CORNELL_DEBUG ) : print('false');
			$val = ob_get_clean();
			printf( '<li><code>CORNELL_DEBUG</code>: %s</li>', $val );

			ob_start();
			defined( 'CORNELL_GOVERNANCE_EMAIL_TO' ) ? var_dump( CORNELL_GOVERNANCE_EMAIL_TO ) : print('false');
			$val = ob_get_clean();
			printf( '<li><code>CORNELL_GOVERNANCE_EMAIL_TO</code>: %s</li>', $val );

			ob_start();
			defined( 'CORNELL_GOVERNANCE_EMAIL_CC' ) ? var_dump( CORNELL_GOVERNANCE_EMAIL_CC ) : print('false');
			$val = ob_get_clean();
			printf( '<li><code>CORNELL_GOVERNANCE_EMAIL_CC</code>: %s</li>', $val );

			ob_start();
			defined( 'CORNELL_GOVERNANCE_EMAIL_BCC' ) ? var_dump( CORNELL_GOVERNANCE_EMAIL_BCC ) : print('false');
			$val = ob_get_clean();
			printf( '<li><code>CORNELL_GOVERNANCE_EMAIL_BCC</code>: %s</li>', $val );

			print( '</ul>' );

			print( '<hr/>' );
		}

		/**
		 * Retrieve a list of all page authors
		 *
		 * @access protected
		 * @since  0.1
		 * @return array the list of all page authors
		 */
		protected function get_authors(): array {
			return get_users( array( 'capability' => 'publish_posts' ) );
		}

		/**
		 * Execute a query
		 *
		 * @param array $args additional arguments to send to the query
		 *
		 * @access protected
		 * @since  0.1
		 * @return \WP_Query the query
		 */
		protected function do_query( array $args=array() ): \WP_Query {
			$args = array_merge( array(
				'order' => 'ASC',
				'orderby' => 'author',
				'post_type' => Plugin::instance()->get_post_types(),
				'meta_query' => array(
					array(
						'key' => 'cornell/governance/information',
						'compare' => 'EXISTS',
					),
				),
			), $args );

			return new \WP_Query( $args );
		}

		/**
		 * Retrieve a list of supervisors
		 *
		 * @access protected
		 * @since  0.1
		 * @return array the list of all supervisors
		 */
		protected function get_supervisors(): array {
			$supervisors = array();

			$q = $this->do_query();
			if ( $q->have_posts() ) : while ( $q->have_posts() ) : $q->the_post();
				$data = get_post_meta( get_the_ID(), 'cornell/governance/information', true );
				$supervisors[$data['supervisor']] = $data['supervisor'];
			endwhile; endif;

			wp_reset_postdata();

			asort( $supervisors, SORT_STRING );
			return $supervisors;
		}

		/**
		 * Retrieve a list of the liaisons
		 *
		 * @access protected
		 * @since  0.1
		 * @return array the list of liaisons
		 */
		protected function get_liaisons(): array {
			$liaisons = array();

			$q = $this->do_query();
			if ( $q->have_posts() ) : while ( $q->have_posts() ) : $q->the_post();
				$data = get_post_meta( get_the_ID(), 'cornell/governance/information', true );
				$liaisons[$data['liaison']] = $data['liaison'];
			endwhile; endif;

			wp_reset_postdata();

			asort( $liaisons, SORT_STRING );
			return $liaisons;
		}

		/**
		 * Send the test email message
		 *
		 * @access protected
		 * @since  0.1
		 * @return void
		 */
		protected function send_test_mail() {
			if ( ! wp_verify_nonce( $_GET['cornell_governance_send_test_mail'], 'cornell/governance/send-test-mail' ) ) {
				$this->message = __( 'Could not send the test email for some reason', 'cornell/governance' );
				return;
			}

			$audience = '';

			if ( array_key_exists( 'author', $_GET['send_email'] ) ) {
				$audience = 'author';
			} else if ( array_key_exists( 'supervisor', $_GET['send_email'] ) ) {
				$audience = 'supervisor';
			} else if ( array_key_exists( 'liaison', $_GET['send_email'] ) ) {
				$audience = 'liaison';
			} else if ( array_key_exists( 'all', $_GET['send_email'] ) ) {
				$audience = 'all';
			}

			if ( empty( $audience ) ) {
				$this->message = __( 'It does not appear that an email type was selected', 'cornell/governance' );
				return;
			}

			if ( 'all' === $audience ) {
				$_GET['send_email'] = array(
					'author' => array(
						'Initial_Prompt' => 'First Prompt',
						'Secondary_Prompt' => 'Second Prompt',
						'Tertiary_Prompt' => 'Third Prompt',
						'Overdue' => 'Overdue Warning',
					),
					'supervisor' => array(
						'Secondary_Prompt' => 'Second Prompt',
						'Tertiary_Prompt' => 'Third Prompt',
						'Overdue' => 'Overdue Warning',
					),
					'liaison' => array(
						'Tertiary_Prompt' => 'Third Prompt',
						'Overdue' => 'Overdue Warning',
					),
				);
			}

			$this->message = '';

			foreach ( $_GET['send_email'] as $audience => $item ) {
				$classes = array_keys( $item );
				foreach ( $classes as $class ) {
					if ( 'supervisor' === $audience ) {
						$classname = "\Cornell\Governance\Emails\Supervisor\\" . $class;
					} else if ( 'liaison' === $audience ) {
						$classname = "\Cornell\Governance\Emails\Liaison\\" . $class;
					} else {
						$classname = "\Cornell\Governance\Emails\\General\\" . $class;
					}

					$user      = $_GET[ $audience ];

					$list = $this->get_page_list( $user, $audience );

					$data = null;
					if ( isset( $list[ $audience ][ $class ][ $user ] ) ) {
						$data = $list[ $audience ][ $class ][ $user ];
					}

					if ( empty( $data ) ) {
						$this->message .= __( 'There was no data to send for this report', 'cornell/governance' );
						$this->message .= sprintf( '<pre><code>%1$s</code></pre>', print_r( $_GET, true ) );
						$this->message .= sprintf( '<pre><code>%2$s %3$s %4$s %3$s %1$s</code></pre>', print_r( $list, true ), $audience, PHP_EOL, $user );

						continue;
					}

					$email = $classname::instance();
					$email->set_pages( $data );
					if ( is_numeric( $user ) ) {
						$author = get_user_by( 'id', $user );
						$user   = $author->user_email;
					}

					$user = is_email( $user );
					if ( false === $user ) {
						$this->message .= __( 'For some reason, the email address does not appear to be valid', 'cornell/governance' );

						continue;
					}

					if ( isset( $_GET['email_address'] ) && is_email( $_GET['email_address'] ) ) {
						$email->set_vars( array(
							'to' => is_email( $_GET['email_address'] ),
						) );
					} else {
						$email->set_vars( array(
							'to' => $user
						) );
					}

					$test = $email->send_mail();
					if ( $test ) {
						$this->message .= __( 'The email appears to have been sent successfully', 'cornell/governance' );
					} else {
						$this->message .= sprintf( '<pre><code>%s</code></pre>', print_r( $email, true ) );
						$this->message .= __( 'There was an unknown error sending the email', 'cornell/governance' );
					}
				}
			}

			if ( empty( $this->message ) ) {
				$this->message = __( 'We do not appear to have done anything with the email for some reason', 'cornell/governance' );
			}

			return;
		}

		/**
		 * Retrieve a list of pages to be included in this test message
		 *
		 * @param int|string $user the user to retrieve
		 * @param string $audience the audience for the report
		 *
		 * @access protected
		 * @since  0.1
		 * @return array the list of pages
		 */
		protected function get_page_list( $user, string $audience ): array {
			$reviews = array(
				'Initial_Prompt' => array(),
				'Secondary_Prompt' => array(),
				'Tertiary_Prompt' => array(),
				'Overdue' => array(),
				'Compliant' => array(),
			);

			$supervisors = array(
				'Secondary_Prompt' => array(),
				'Tertiary_Prompt' => array(),
				'Overdue' => array(),
			);
			$liaisons = array(
				'Tertiary_Prompt' => array(),
				'Overdue' => array(),
			);

			$args = array();

			if ( is_numeric( $user ) ) {
				$args['author'] = $user;
			}

			$now = time();

			$q = $this->do_query( $args );
			if ( $q->have_posts() ) : while ( $q->have_posts() ) : $q->the_post();
				global $post;
				$data = get_post_meta( get_the_ID(), 'cornell/governance/information', true );

				if ( ! array_key_exists( 'last-review', $data ) || ! array_key_exists( 'review-cycle', $data ) ) {
					continue;
				}

				$due = Helpers::calculate_next_review_date( $data['last-review'], $data['review-cycle'] );
				$post_id = get_the_ID();

				if ( $due <= $now ) {
					$reviews['Overdue'][ $post->post_author ][ $post_id ] = $due;
					$supervisors['Overdue'][ $data['supervisor'] ][ $post_id ] = $due;
					$liaisons['Overdue'][ $data['liaison'] ][ $post_id ] = $due;
				} else if ( strtotime( '+ 60 days' ) < $due ) {
					$reviews['Compliant'][ $post->post_author ][ $post_id ] = $due;
				} else if ( strtotime( '+ 30 days' ) < $due ) {
					$reviews['Initial_Prompt'][ $post->post_author ][ $post_id ] = $due;
				} else if ( strtotime( '+ 7 days' ) < $due ) {
					$reviews['Secondary_Prompt'][ $post->post_author ][ $post_id ] = $due;
					$supervisors['Secondary_Prompt'][ $data['supervisor'] ][ $post_id ] = $due;
				} else {
					$reviews['Tertiary_Prompt'][ $post->post_author ][ $post_id ] = $due;
					$supervisors['Tertiary_Prompt'][ $data['supervisor'] ][ $post_id ] = $due;
					$liaisons['Tertiary_Prompt'][ $data['liaison'] ][ $post_id ] = $due;
				}
			endwhile; endif;

			wp_reset_postdata();

			return array(
				'author' => $reviews,
				'supervisor' => $supervisors,
				'liaison' => $liaisons,
			);
		}

		/**
		 * Adds the screen options to the page
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function add_options() {
		}

		/**
		 * Output the submenu page
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function do_submenu_page() {
			$this->display();
		}
	}
}