<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Emails {

	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Prompt' ) ) {
		abstract class Prompt extends Base {
			/**
			 * Holds the name of the class being instantiated
			 */
            protected string $classname = '';

			/**
			 * Construct our object
			 */
			protected function __construct() {
				parent::__construct();
			}

			/**
			 * Builds the list of pages to be included in the email message
			 *
			 * @access protected
			 * @since  0.1
			 * @return string the list of pages
			 */
			protected function get_page_list(): string {
				$rt = '<table cellpadding="1" cellspacing="0" width="100%" style="width: 100%; border: 1px solid #000; background: #e2e2e2; color: #000; border-collapse: collapse">';
				$rt .= '<tr>';
				$rt .= sprintf( '<td width="70%" style="border: 1px solid #000">%s</td>', __( 'Page', 'cornell/governance' ) );
				$rt .= sprintf( '<td>%s</td>', __( 'Due Date', 'cornell/governance' ) );
				$rt .= '</tr>';

				foreach ( $this->pages as $id => $page ) {
					$link = sprintf( '<a href="%s">%s</a>', get_edit_post_link( $id ), get_the_title( $id ) );
					$due = Helpers::format_date( Helpers::calculate_next_review_date( $page['last-review'], $page['review-cycle'] ) );

					$rt .= sprintf( '<tr><td>%s</td><td>%s</td></tr>', $link, $due );
				}

				$rt .= '</table>';

				return $rt;
			}

			/**
			 * Set up and echo an HTML sample of the email that would be sent
			 *
			 * @access public
			 * @since  0.1
			 * @return string the HTML for the sample email message
			 */
			public function sample_email(): string {
				$rt = sprintf( '<p><strong>To:</strong> %s', $this->get_email_to() );
				$rt .= sprintf( '<p><strong>Subject:</strong> %s', $this->get_email_subject() );
				$rt .= '<div><h4>Message:</h4>';
				$rt .= $this->get_html_body();
				$rt .= '</div>';

				return $rt;
			}

			/**
			 * @inheritDoc
			 */
			protected function get_default_html_body() {
				$site_name = get_option( 'blogname', '' );
				$page_list_table = $this->get_page_list();

                $classname = get_called_class();

                $days = 0;
				$intro = __( 'The following pages are due for review within the next %d days', 'cornell/governance' );
                if ( strstr( $classname, 'Tertiary_Prompt' ) ) {
                    $days = 7;
                } else if ( strstr( $classname, 'Secondary_Prompt' ) ) {
                    $days = 30;
                } else if ( strstr( $classname, 'Initial_Prompt' ) ) {
                    $days = 60;
                } else {
                    $intro = __( '<font color="red"><b>The following pages are overdue for review, and must be remediated immediately.</b></font>', 'cornell/governance' );
                }

				ob_start();
				?>
				<?php
				if ( empty( $site_name ) || empty( $page_list_table ) ) {
					return false;
				}
				?>
				<p>
					<?php _e( 'Greetings!', 'cornell/governance' ) ?>
				</p>
				<p>
					<?php printf( __( 'You have upcoming page review tasks to complete for the %s website.', 'cornell/governance' ), $site_name ) ?>
				</p>
                <p>
                    <?php printf( $intro, $days ); ?>
                </p>

				<?php
				echo $page_list_table;
				?>

				<p>
					<?php _e( 'Thank you for your attention to this matter.', 'cornell/governance' ) ?>
				</p>

				<?php
				return ob_get_clean();
			}

			/**
			 * @inheritDoc
			 */
			protected function get_headers(): array {
				return $this->headers;
			}

			/**
			 * @inheritDoc
			 */
			protected function get_email_to(): string {
				return $this->to;
			}

			/**
			 * @inheritDoc
			 */
			protected function get_email_subject(): string {
				return $this->subject;
			}
		}
	}
}