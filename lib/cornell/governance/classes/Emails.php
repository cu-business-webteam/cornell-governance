<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance {

	use Cornell\Governance\Admin\Fields\Initial_Prompt;
	use Cornell\Governance\Admin\Fields\Secondary_Prompt;
	use Cornell\Governance\Admin\Fields\Tertiary_Prompt;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Emails\Prompt;

	if ( ! class_exists( 'Emails' ) ) {
		class Emails {
			/**
			 * @var Emails $instance holds the single instance of this class
			 * @access private
			 */
			private static Emails $instance;
			/**
			 * Holds the list of authors
			 */
			protected array $authors;
			/**
			 * Holds the list of supervisors
			 */
			protected array $supervisors;
			/**
			 * Holds the list of liaisons
			 */
			protected array $liaisons;
			/**
			 * Holds the full list of pages on this site
			 */
			protected array $pages;
			/**
			 * Holds a list of folks that should receive some prompt email
			 */
			protected array $recipients;
			/**
			 * @var array $monthly Holds a list of messages to be sent monthly
			 * @access protected
			 */
			protected array $monthly = array();
			/**
			 * @var array $weekly Holds a list of messages to be sent weekly
			 * @access protected
			 */
			protected array $weekly = array();
			/**
			 * @var array $daily Holds a list of messages to be sent daily
			 * @access protected
			 */
			protected array $daily = array();
			/**
			 * @var int $limit limit the number of messages sent in a single session
			 * @access protected
			 */
			protected int $limit = 0;
			/**
			 * @var array $keys the option keys being retrieved/stored for the lists of pages/emails
			 * @access protected
			 */
			protected array $keys = array();

			/**
			 * Creates the Emails object
			 *
			 * @access private
			 * @since  0.1
			 */
			private function __construct() {
				if ( ! is_admin() && ! isset( $_GET['cornell/governance/run-email-cron'] ) ) {
					return;
				}

				$this->get_pages();
				$this->get_authors();
				$this->get_supervisors();
				$this->get_liaisons();

				$this->prep_recipients();

				if ( isset( $_GET['cornell/governance/run-email-cron'] ) ) {
					$this->scheduled_emails();
				}
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Emails
			 * @since   0.1
			 */
			public static function instance(): Emails {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Retrieve a full list of pages to be sent in monthly reports
			 *
			 * @access protected
			 * @return array the list of pages to be included in monthly reports
			 * @since  0.1
			 */
			protected function get_monthly_page_list(): array {
				$did_send = $this->get_page_list( 'monthly' );
				if ( count( $did_send ) ) {
					if ( array_key_exists( 'sent-all', $did_send ) ) {
						return $did_send;
					}

					return $did_send;
				}

				$page_list = $did_send;

				if ( empty( $page_list ) ) {
					$page_list = array(
						'sent-all' => date( 'c' ),
					);
				}

				update_option( $this->keys['monthly'], $page_list );

				return $page_list;
			}

			/**
			 * Retrieve a full list of pages to be sent in weekly reports
			 *
			 * @access protected
			 * @return array the list of pages to be included in weekly reports
			 * @since  0.1
			 */
			protected function get_weekly_page_list(): array {
				$did_send = $this->get_page_list( 'weekly' );
				if ( count( $did_send ) ) {
					if ( array_key_exists( 'sent-all', $did_send ) ) {
						return $did_send;
					}

					return $did_send;
				}

				$page_list = $did_send;

				if ( empty( $page_list ) ) {
					$page_list = array(
						'sent-all' => date( 'c' ),
					);
				}

				update_option( $this->keys['weekly'], $page_list );

				return $page_list;
			}

			/**
			 * Retrieve a full list of pages to be sent in daily reports
			 *
			 * @access protected
			 * @return array the list of pages to be included in daily reports
			 * @since  0.1
			 */
			protected function get_daily_page_list(): array {
				$did_send = $this->get_page_list( 'daily' );
				if ( count( $did_send ) ) {
					return $did_send;
				}

				$page_list = $did_send;

				if ( empty( $page_list ) ) {
					$page_list = array(
						'sent-all' => date( 'c' ),
					);
				}

				update_option( $this->keys['daily'], $page_list );

				return $page_list;
			}

			/**
			 * Retrieve a full list of pages that need to be reviewed
			 *
			 * @param string $key the report frequency being retrieved
			 *
			 * @access protected
			 * @return array the list of pages to be reviewed
			 * @since  0.1
			 */
			protected function get_page_list( string $key = 'all' ): array {
				if ( 'all' !== $key ) {
					$test = get_option( $this->keys[ $key ], array() );

					if ( count( $test ) ) {
						return $test;
					}
				}

				$args = array(
					'order'      => 'ASC',
					'orderby'    => 'author',
					'post_type'  => Plugin::instance()->get_post_types(),
					'meta_query' => array(
						array(
							'key'     => 'cornell/governance/information',
							'compare' => 'EXISTS',
						),
					),
				);

				$reviews = array(
					'initial'   => array(),
					'secondary' => array(),
					'tertiary'  => array(),
					'due'       => array(),
					'overdue'   => array(),
					'compliant' => array(),
				);

				$supervisors = array(
					'secondary' => array(),
					'tertiary'  => array(),
					'due'       => array(),
					'overdue'   => array(),
					'compliant' => array(),
				);
				$liaisons    = array(
					'tertiary'  => array(),
					'due'       => array(),
					'overdue'   => array(),
					'compliant' => array(),
				);

				$now = time();

				$q = new \WP_Query( $args );
				if ( $q->have_posts() ) : while ( $q->have_posts() ) : $q->the_post();
					global $post;

					$data = get_post_meta( get_the_ID(), 'cornell/governance/information', true );

					if ( /*! array_key_exists( 'last-review', $data ) || */ ! array_key_exists( 'review-cycle', $data ) ) {
						continue;
					}

					if ( ! array_key_exists( 'last-review', $data ) ) {
						$data['last-review'] = null;
					}

					$due     = Helpers::calculate_next_review_date( $data['last-review'], $data['review-cycle'] );
					$post_id = get_the_ID();

					if ( date( "Y-m-d", $due ) == date( "Y-m-d", $now ) ) {
						// Due today
						$reviews['due'][ $post->post_author ][ $post_id ]      = $due;
						$supervisors['due'][ $data['supervisor'] ][ $post_id ] = $due;
						$liaisons['due'][ $data['liaison'] ][ $post_id ]       = $due;
					} else if ( $due <= $now ) {
						// Overdue
						$reviews['overdue'][ $post->post_author ][ $post_id ]      = $due;
						$supervisors['overdue'][ $data['supervisor'] ][ $post_id ] = $due;
						$liaisons['overdue'][ $data['liaison'] ][ $post_id ]       = $due;
					} else if ( strtotime( '+ 60 days' ) < $due ) {
						// Compliant
						$reviews['compliant'][ $post->post_author ][ $post_id ]  = $due;
						$reviews['compliant'][ $data['supervisor'] ][ $post_id ] = $due;
						$reviews['compliant'][ $data['liaison'] ][ $post_id ]    = $due;
					} else if ( strtotime( '+ 30 days' ) < $due ) {
						// Due in the next 60 days
						$reviews['initial'][ $post->post_author ][ $post_id ] = $due;
					} else if ( strtotime( '+ 7 days' ) < $due ) {
						// Due in the next 30 days
						$reviews['secondary'][ $post->post_author ][ $post_id ]      = $due;
						//$supervisors['secondary'][ $data['supervisor'] ][ $post_id ] = $due;
					} else {
						// Due this week
						$reviews['tertiary'][ $post->post_author ][ $post_id ]      = $due;
						$supervisors['tertiary'][ $data['supervisor'] ][ $post_id ] = $due;
						$liaisons['tertiary'][ $data['liaison'] ][ $post_id ]       = $due;
					}
				endwhile; endif;

				wp_reset_postdata();

				switch ( $key ) {
					case 'daily' :
						$messages = array(
							'authors'     => array(
								'tertiary' => $reviews['tertiary'],
								'due'      => $reviews['due'],
								'overdue'  => $reviews['overdue'],
							),
							'supervisors' => array(
								'tertiary' => $reviews['tertiary'],
								'due'      => $reviews['due'],
								'overdue'  => $supervisors['overdue'],
							),
							'liaisons'    => array(
								'tertiary' => $reviews['tertiary'],
								'due'      => $reviews['due'],
								'overdue'  => $liaisons['overdue'],
							),
						);
						break;
					case 'weekly' :
						$messages = array(
							'authors'     => array(
								'tertiary' => $reviews['tertiary'],
							),
							'supervisors' => array(
								'tertiary' => $reviews['tertiary'],
							),
						);
						break;
					case 'monthly' :
						$messages = array(
							'authors'     => array(
								'initial'   => $reviews['initial'],
								'secondary' => $reviews['secondary'],
							),
							'supervisors' => array(
								'secondary' => $supervisors['secondary'],
							),
						);
						break;
					default :
						$messages = array(
							'authors'     => $reviews,
							'supervisors' => $supervisors,
							'liaisons'    => $liaisons,
						);
						break;
				}

				$messages = Helpers::ArrayCleaner( $messages );

				if ( empty( $messages ) ) {
					$messages = array( 'sent-all', date( 'c' ) );
				}

				return $messages;
			}

			/**
			 * Retrieve a list of all content managed through this plugin
			 *
			 * @access protected
			 * @return array the list of content
			 * @since  0.1
			 */
			protected function get_pages(): array {
				global $wpdb;
				$query   = $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key=%s", Info::instance()->get_meta_key() );
				$results = $wpdb->get_results( $query );
				if ( is_wp_error( $results ) || ! is_array( $results ) ) {
					return array();
				}

				$this->pages = array();

				foreach ( $results as $result ) {
					$this->pages[ $result->post_id ] = maybe_unserialize( $result->meta_value );
				}

				return $this->pages;
			}

			/**
			 * Retrieve a list of all authors on this site
			 *
			 * @access protected
			 * @return array the list of authors
			 * @since  0.1
			 */
			protected function get_authors(): array {
				if ( empty( $this->pages ) ) {
					$this->pages = $this->get_pages();
				}

				$this->authors = array();

				foreach ( $this->pages as $id => $page ) {
					$post                 = get_post( $id );
					$this->authors[ $id ] = get_user_by( 'id', $post->post_author );
				}

				return $this->authors;
			}

			/**
			 * Retrieves a list of supervisors responsible for pages
			 *
			 * @access protected
			 * @return array the list of supervisors
			 * @since  0.1
			 */
			protected function get_supervisors(): array {
				if ( empty( $this->pages ) ) {
					$this->get_pages();
				}

				$this->supervisors = array();

				foreach ( $this->pages as $id => $page ) {
					$this->supervisors[ $id ] = is_email( $page['supervisor'] );
				}

				$this->supervisors = array_filter( $this->supervisors );

				return $this->supervisors;
			}

			/**
			 * Retrieve a list of liaisons responsible for governed content
			 *
			 * @access protected
			 * @return array the list of liaisons
			 * @since  0.1
			 */
			protected function get_liaisons(): array {
				if ( empty( $this->pages ) ) {
					$this->get_pages();
				}

				$this->liaisons = array();

				foreach ( $this->pages as $id => $page ) {
					$this->liaisons[ $id ] = is_email( $page['liaison'] );
				}

				$this->liaisons = array_filter( $this->liaisons );

				return $this->liaisons;
			}

			/**
			 * Prepare the various lists of recipients and what emails they should receive
			 *
			 * @access protected
			 * @return void
			 * @since  0.1
			 */
			protected function prep_recipients() {
				$recipients = get_option( 'cornell/governance/emails-to-send', array() );
				if ( ! empty( $recipients ) ) {
					if ( is_numeric( $recipients ) ) {
						$last_sent = strtotime( date( "Y-m-d", $recipients ) );
						$today     = strtotime( date( "Y-m-d" ) );
						if ( $last_sent >= $today ) {
							// The last time we sent emails was today
							return;
						}
					} else {
						$this->recipients = $recipients;
						$this->filter_recipients_lists();

						return;
					}
				}

				$this->recipients = array(
					'compliant' => array(
						'authors'     => array(),
						'supervisors' => array(),
						'liaisons'    => array(),
					),
					'initial'   => array(
						'authors' => array(),
					),
					'secondary' => array(
						'authors'     => array(),
						'supervisors' => array(),
					),
					'tertiary'  => array(
						'authors'     => array(),
						'supervisors' => array(),
						'liaisons'    => array(),
					),
					'overdue'   => array(
						'authors'     => array(),
						'supervisors' => array(),
						'liaisons'    => array(),
					),
				);

				foreach ( $this->pages as $id => $page ) {
					$due       = Helpers::calculate_next_review_date( $page['last-review'], $page['review-cycle'] );
					$now       = time();
					$initial   = get_option( 'cornell-governance-initial-prompt-time', 60 );
					$secondary = get_option( 'cornell-governance-secondary-prompt-time', 30 );
					$tertiary  = get_option( 'cornell-governance-tertiary-prompt-time', 7 );

					$page['review-due'] = $due;

					$post = get_post( $id );

					if ( $due <= $now ) {
						$this->recipients['overdue']['authors'][ $post->post_author ][ $id ]      = $page;
						$this->recipients['overdue']['supervisors'][ $page['supervisor'] ][ $id ] = $page;
						$this->recipients['overdue']['liaisons'][ $page['liaison'] ][ $id ]       = $page;
					} else if ( strtotime( '+ ' . $initial . ' days' ) < $due ) {
						$this->recipients['compliant']['authors'][ $post->post_author ][ $id ]      = $page;
						$this->recipients['compliant']['supervisors'][ $page['supervisor'] ][ $id ] = $page;
						$this->recipients['compliant']['liaisons'][ $page['liaison'] ][ $id ]       = $page;
					} else if ( strtotime( '+ ' . $secondary . ' days' ) < $due ) {
						$this->recipients['initial']['authors'][ $post->post_author ][ $id ] = $page;
					} else if ( strtotime( '+ ' . $tertiary . ' days' ) < $due ) {
						$this->recipients['secondary']['authors'][ $post->post_author ][ $id ]      = $page;
						$this->recipients['secondary']['supervisors'][ $page['supervisor'] ][ $id ] = $page;
					} else {
						$this->recipients['tertiary']['authors'][ $post->post_author ][ $id ]      = $page;
						$this->recipients['tertiary']['supervisors'][ $page['supervisor'] ][ $id ] = $page;
						$this->recipients['tertiary']['liaisons'][ $page['liaison'] ][ $id ]       = $page;
					}
				}

				$this->filter_recipients_lists();

				update_option( 'cornell/governance/emails-to-send', $this->recipients );
				update_option( 'cornell/governance/emails-sent', array() );
			}

			/**
			 * Remove empty items from the recipients array
			 *
			 * @access private
			 * @return void
			 * @since  0.1
			 */
			private function filter_recipients_lists() {
				if ( array_key_exists( 'compliant', $this->recipients ) ) {
					unset( $this->recipients['compliant'] );
				}

				foreach ( $this->recipients as $prompt => $type ) {
					foreach ( $type as $key => $people ) {
						$this->recipients[ $prompt ][ $key ] = array_filter( $people );
					}

					$this->recipients[ $prompt ] = array_filter( $type );
				}

				$this->recipients = array_filter( $this->recipients );
			}

			/**
			 * Prepare the lists of pages to be reviewed
			 *
			 * @param array $list the list of pages to be reviewed
			 *
			 * @access private
			 * @return array the list of pages
			 * @since  0.1
			 */
			private function get_page_lists( array $list ): array {
				$messages = array();
				foreach ( $list as $post => $emails ) {
					$id          = str_replace( 'post-', '', $post );
					$post_object = get_post( $id );
					foreach ( $emails as $email ) {
						if ( ! array_key_exists( $email, $messages ) ) {
							$messages[ $email ] = array();
						}
						$messages[ $email ][ $id ] = clone $post_object;
					}
				}

				return $messages;
			}

			/**
			 * Send the email messages
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function send_unscheduled_messages(): void {
				$recipients  = array();
				$prompt_keys = array(
					'overdue',
					'initial',
					'secondary',
					'tertiary'
				);

				$recipient_keys = array(
					'authors',
					'supervisors',
					'liaisons',
				);

				$prompt = $recipient_type = null;

				foreach ( $prompt_keys as $prompt_key ) {
					if ( count( $recipients ) >= 1 ) {
						continue;
					}

					$prompt = $prompt_key;

					if ( array_key_exists( $prompt_key, $this->recipients ) ) {
						foreach ( $recipient_keys as $recipient_key ) {
							if ( count( $recipients ) >= 1 ) {
								continue;
							}

							$recipient_type = $recipient_key;

							if ( array_key_exists( $recipient_key, $this->recipients[ $prompt_key ] ) ) {
								$i = 0;
								foreach ( $this->recipients[ $prompt_key ][ $recipient_key ] as $author => $pages ) {
									if ( $i >= 9 ) {
										continue;
									}

									$recipients[ $author ] = $this->shift_and_return( $this->recipients[ $prompt_key ][ $recipient_key ], $author );
									$i ++;
								}
							} else {
								do_action( 'qm/debug', 'Could not locate {key} in {list}', array(
									'key'  => $recipient_key,
									'list' => $this->recipients[ $prompt_key ],
								) );
							}
						}
					} else {
						do_action( 'qm/debug', 'Could not locate {key} in {list}', array(
							'key'  => $prompt_key,
							'list' => $this->recipients
						) );
					}
				}

				$this->filter_recipients_lists();

				$subject = __( 'Your web pages due for review soon', 'cornell/governance' );
				$class   = '';

				switch ( $prompt ) {
					case 'initial' :
						$class   = '\Cornell\Governance\Emails\General\Initial_Prompt';
						$subject = __( 'Your web pages due for review in less than ' . get_option( 'cornell-governance-initial-prompt-time', 60 ) . ' days', 'cornell/governance' );
						break;
					case 'secondary' :
						$class   = '\Cornell\Governance\Emails\General\Secondary_Prompt';
						$subject = __( 'Your web pages due for review in less than ' . get_option( 'cornell-governance-secondary-prompt-time', 30 ) . ' days', 'cornell/governance' );
						break;
					case 'tertiary' :
						$class   = '\Cornell\Governance\Emails\General\Tertiary_Prompt';
						$subject = __( 'Your web pages due for review in less than ' . get_option( 'cornell-governance-tertiary-prompt-time', 7 ) . ' days', 'cornell/governance' );
						break;
					case 'overdue' :
						$class   = '\Cornell\Governance\Emails\General\Tertiary_Prompt';
						$subject = __( 'Your web pages that are overdue for review', 'cornell/governance' );
						break;
				}

				/*print('<div><pre><code>');
				var_dump( $recipients );
				var_dump( $prompt );
				var_dump( $recipient_type );
				print( '</code></pre></div>' );*/
				$sent = get_option( 'cornell/governance/emails-sent', array() );

				foreach ( $recipients as $recipient => $pages ) {
					if ( empty( $class ) ) {
						continue;
					}

					$email = $class::instance();

					if ( is_numeric( $recipient ) ) {
						$user    = get_user_by( 'id', $recipient );
						$address = $user->display_name . ' <' . $user->user_email . '>';
					} else {
						$address = is_email( $recipient );
					}

					$email->set_email_to( $address );
					//$email->set_email_subject( $subject );
					$email->set_pages( $pages );

					$email->send_mail();
					$sent[ $prompt_key ][ $recipient ] = array_keys( $pages );


					//$this->sample_email( $email );
				}

				update_option( 'cornell/governance/emails-sent', $sent );
				if ( ! empty( $this->recipients ) ) {
					update_option( 'cornell/governance/emails-to-send', $this->recipients );
				} else {
					update_option( 'cornell/governance/emails-to-send', time() );
				}

				return;
			}

			/**
			 * Remove and return an item from an associative array
			 *
			 * @param array &$array the array from which to remove the item
			 * @param string|int $key the key to remove from the array
			 *
			 * @access private
			 * @return mixed the item from the array
			 * @since  0.1
			 */
			private function shift_and_return( array &$array, $key ) {
				$rt = $array[ $key ];
				unset( $array[ $key ] );

				return $rt;
			}

			/**
			 * For debugging purposes:
			 */

			/**
			 * List the recipients we gathered
			 */
			public function list_recipients() {
				echo '<div class="recipients-list">';

				foreach ( $this->recipients as $key => $prompt ) {
					if ( empty( $prompt ) ) {
						continue;
					}

					printf( '<h3>%s</h3>', ucfirst( $key ) );

					foreach ( $prompt as $type => $data ) {
						if ( empty( $data ) ) {
							continue;
						}

						printf( '<h4>%s</h4>', ucfirst( $type ) );

						echo '<table>';
						echo '<thead>';
						echo '<tr>';

						echo '<th>Person</th>';
						echo '<th>Pages</th>';

						echo '</tr>';
						echo '</thead>';
						echo '<tbody>';

						foreach ( $data as $author_id => $pages ) {
							if ( is_numeric( $author_id ) ) {
								$author      = get_user_by( 'id', $author_id );
								$author_name = $author->display_name;
							} else {
								$author_name = $author_id;
							}

							echo '<tr>';
							printf( '<td>%s</td>', $author_name );
							echo '<td><ul>';
							foreach ( $pages as $page_id => $page ) {
								printf( '<li>%d: %s</li>', $page_id, Helpers::format_date( $page['review-due'] ) );
							}
							echo '</ul></td>';
							echo '</tr>';
						}

						echo '</tbody>';
						echo '</table>';
					}
				}

				echo '<div>';
				$this->send_unscheduled_messages();
				echo '</div>';

				echo '</div>';
			}

			/**
			 * Output a sample email message based on the info provided
			 *
			 * @param Prompt $email the email object being sampled
			 *
			 * @access protected
			 * @return void
			 * @since  0.1
			 */
			protected function sample_email( Prompt $email ) {
				echo '<div class="page-governance-sample-email">';
				echo $email->sample_email();
				echo '</div>';
			}

			/**
			 * Send the email messages
			 *
			 * @param string $key the report frequency being processed
			 *
			 * @access private
			 * @return void
			 * @since  0.1
			 */
			private function send_messages( string $key ): void {
				if ( ! in_array( $key, array( 'monthly', 'weekly', 'daily' ) ) ) {
					return;
				}

				$i = 0;

				if ( array_key_exists( 'sent-all', $this->{$key} ) ) {
					if ( isset( $_GET['cornell/governance/debug'] ) ) {
						echo '<p>' . __( 'Sent all of the ' . $key . ' messages already' ) . '</p>';
					}

					return;
				}

				if ( empty( $this->{$key} ) ) {
					if ( isset( $_GET['cornell/governance/debug'] ) ) {
						echo '<p>' . __( 'Sent all of the ' . $key . ' messages already' ) . '</p>';
					}
					update_option( $this->keys[ $key ], array( 'sent-all' => date( 'c' ) ) );
				}

				foreach ( $this->{$key} as $audience => $reports ) {
					// Authors, Supervisors or Liaisons
					if ( empty( $reports ) ) {
						if ( isset( $_GET['cornell/governance/debug'] ) ) {
							echo '<p>' . __( 'There are no reports to send for ' . $audience ) . '</p>';
						}
						continue;
					}
					foreach ( $reports as $prompt => $user ) {
						// initial, secondary, tertiary, overdue, compliant
						if ( empty( $user ) ) {
							if ( isset( $_GET['cornell/governance/debug'] ) ) {
								echo '<p>' . __( 'There are no reports to send for ' . $user ) . '</p>';
							}
							continue;
						}

						foreach ( $user as $ID => $data ) {
							// user ID or email address
							$email = $this->prepare_message( $prompt, $audience, $ID, $data );
							unset( $this->{$key}[ $audience ][ $prompt ][ $ID ] );
							$i ++;

							if ( $i >= $this->limit ) {
								$this->{$key} = Helpers::ArrayCleaner( $this->{$key} );
								if ( empty( $this->{$key} ) ) {
									$this->{$key} = array( 'sent-all', date( 'c' ) );
								}
								update_option( $this->keys[ $key ], $this->{$key} );

								return;
							}
						}
					}
				}

				update_option( $this->keys[ $key ], array( 'sent-all' => date( 'c' ) ) );
			}

			/**
			 * Prepare and send an email message to be sent to the appropriate user
			 *
			 * @param string $type the type of prompt being sent
			 * @param string $audience the type of user being sent the email message
			 * @param int|string $user the user ID or email address to send the email to
			 * @param array $data the information to be processed for the message
			 *
			 * @access private
			 * @return bool whether a message was prepped and sent
			 * @since  0.1
			 */
			private function prepare_message( string $type, string $audience, $user, array $data ): bool {
				$email   = null;
				$subject = null;
				switch ( $audience ) {
					case 'authors' :
					{
						switch ( $type ) {
							case 'tertiary' :
								$email = Emails\General\Tertiary_Prompt::instance();
								break;
							case 'secondary' :
								$email = Emails\General\Secondary_Prompt::instance();
								break;
							case 'initial' :
								$email = Emails\General\Initial_Prompt::instance();
								break;
							case 'overdue' :
								$email = Emails\General\Overdue::instance();
								break;
						}
						break;
					}
					case 'supervisors' :
					{
						switch ( $type ) {
							case 'secondary' :
								$email = Emails\Supervisor\Secondary_Prompt::instance();
								break;
							case 'tertiary' :
								$email = Emails\Supervisor\Tertiary_Prompt::instance();
								break;
							case 'overdue' :
								$email = Emails\Supervisor\Overdue::instance();
								break;
						}
						break;
					}
					case 'liaisons' :
					{
						switch ( $type ) {
							case 'tertiary' :
								$email = Emails\Liaison\Tertiary_Prompt::instance();
								break;
							case 'overdue' :
								$email = Emails\Liaison\Overdue::instance();
								break;
						}
						break;
					}
				}

				$email->set_pages( $data );

				$user = $this->get_email( $user );

				if ( empty( $user ) ) {
					return false;
				}

				$email->set_vars( array(
					'to' => $user
				) );

				return $email->send_mail();
			}

			/**
			 * Set up the "to" address
			 *
			 * @param int|string $user the user being evaluated
			 *
			 * @access protected
			 * @return string|boolean the formatted email address to use
			 * @since  0.1
			 */
			protected function get_email( $user ) {
				/*if ( defined( 'CORNELL_DEBUG' ) && CORNELL_DEBUG && defined( 'CORNELL_GOVERNANCE_EMAIL_TO' ) ) {
					return is_email( CORNELL_GOVERNANCE_EMAIL_TO );
				}*/

				if ( is_numeric( $user ) ) {
					$author = get_user_by( 'id', $user );

					$name = '';
					$user = $author->user_email;

					if ( ! empty( $author->user_firstname ) && ! empty( $author->user_lastname ) ) {
						$name = $author->user_firstname . ' ' . $author->user_lastname;
					} else if ( ! empty( $author->display_name ) ) {
						$name = $author->display_name;
					} else if ( ! empty( $author->user_nicename ) ) {
						$name = $author->user_nicename;
					} else {
						$name = $author->user_login;
					}

					if ( ! empty( $name ) ) {
						if ( defined( 'CORNELL_DEBUG' ) && CORNELL_DEBUG && defined( 'CORNELL_GOVERNANCE_EMAIL_TO' ) ) {
							return sprintf( '%1$s <%2$s>', $name, is_email( CORNELL_GOVERNANCE_EMAIL_TO ) );
						} else if ( is_email( $user ) ) {
							return sprintf( '%1$s <%2$s>', $name, is_email( $user ) );
						}
					}
				}

				if ( defined( 'CORNELL_DEBUG' ) && CORNELL_DEBUG && defined( 'CORNELL_GOVERNANCE_EMAIL_TO' ) ) {
					return sprintf( '%1$s <%2$s>', $user, is_email( CORNELL_GOVERNANCE_EMAIL_TO ) );
				} else {
					return is_email( $user );
				}
			}

			/**
			 * Send out scheduled emails
			 *
			 * @access protected
			 * @return void
			 * @since  0.4.0
			 */
			protected function scheduled_emails() {
				date_default_timezone_set( get_option( 'timezone_string', 'UTC' ) );

				$this->keys = array(
					'monthly' => sprintf( 'cornell/governance/emails-to-send/monthly/%s', date( "Y-m" ) ),
					'weekly'  => sprintf( 'cornell/governance/emails-to-send/weekly/%s', date( "Y-W" ) ),
					'daily'   => sprintf( 'cornell/governance/emails-to-send/daily/%s', date( "Y-m-d" ) ),
				);

				$this->limit = apply_filters( 'cornell/governance/emails/limit', 25 );
				$this->daily = $this->get_daily_page_list();
				if ( isset( $_GET['cornell/governance/debug'] ) ) {
					echo '<pre><code>';
					var_dump( $this->daily );
					echo '</code></pre>';
				}
				$this->send_messages( 'daily' );

				$this->weekly = $this->get_weekly_page_list();
				if ( isset( $_GET['cornell/governance/debug'] ) ) {
					echo '<pre><code>';
					var_dump( $this->weekly );
					echo '</code></pre>';
				}
				$this->send_messages( 'weekly' );

				$this->monthly = $this->get_monthly_page_list();
				if ( isset( $_GET['cornell/governance/debug'] ) ) {
					echo '<pre><code>';
					var_dump( $this->monthly );
					echo '</code></pre>';
				}
				$this->send_messages( 'monthly' );
			}
		}
	}
}