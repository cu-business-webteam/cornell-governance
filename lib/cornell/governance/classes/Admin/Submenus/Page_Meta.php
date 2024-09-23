<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Submenus {

	use Cornell\Governance\Admin\Admin;
	use Cornell\Governance\Admin\Meta_Boxes;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Review_Cycle;

	class Page_Meta extends Base {
		/**
		 * @var Page_Meta $instance holds the single instance of this class
		 * @access private
		 */
		private static Page_Meta $instance;
		/**
		 * @var array $info_meta the Info meta data for this page
		 */
		protected array $info_meta;
		/**
		 * @var array $notes_meta the Notes meta data for this page
		 */
		protected array $notes_meta;

		/**
		 * Creates the Menu object
		 *
		 * @access private
		 * @since  0.1
		 */
		function __construct() {
			if ( ! is_admin() ) {
				return;
			}

			parent::__construct( array(
				'title'           => __( 'Cornell Governance: Content Governance Meta', 'cornell/governance' ),
				'menu_name'       => __( 'Page Meta', 'cornell/governance' ),
				'slug'            => 'cornell-governance-page-meta',
				'per_page_option' => 'cornell/governance/steward-dashboard/items_per_page',
				'description'     => __( 'Governance information for a specific page or piece of content.', 'cornell/governance' ),
			) );
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Page_Meta
		 * @since   0.1
		 */
		public static function instance(): Page_Meta {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Set the current user ID, so that report data will be limited to that user
		 *
		 * @param int $user the current value of the user ID (most likely 0)
		 *
		 * @access public
		 * @return int the user ID
		 * @since  0.1
		 */
		public function current_user( int $user ): int {
			return get_current_user_id();
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
			foreach ( $attributes as $key => $attribute ) {
				switch ( $key ) {
					case 'page' :
						$this->page = $attribute;
						break;
					default :
						break;
				}
			}

			$this->cap = 'edit_pages';
		}

		/**
		 * Outputs the content of the Page List
		 *
		 * @access protected
		 * @return void
		 * @since  0.1
		 */
		protected function display() {
			add_filter( 'cornell/governance/reports/current-user', array( $this, 'current_user' ) );

			if ( ! isset( $_GET['post'] ) || empty( $_GET['post'] ) ) {
				$this->no_page_selected();
			} else if ( ! current_user_can( 'edit_post', $_GET['post'] ) ) {
				$this->no_permissions();
			} else {
				printf( '<div class="wrap"><h2>%s</h2>', $this->title . ': ' . get_the_title( $_GET['post'] ) );
				print( '<div class="page-meta-report-container">' );
				$this->display_info();

				print( '<div class="governance-box info-meta">' );
				printf( '<h3>%s</h3>', __( 'Additional Details', 'cornell/governance' ) );

				$this->display_notes();
				$this->display_revisions();
				print( '</div>' );

				print( '</div>' );
			}

			remove_filter( 'cornell/governance/reports/current-user', array( $this, 'current_user' ) );
		}

		/**
		 * Output an error message indicating that no content was selected for this report
		 *
		 * @access protected
		 * @since  2023.04
		 * @return void
		 */
		protected function no_page_selected() {
			$link = admin_url('admin.php');
			$link = add_query_arg( array( 'page' => $this->page ), $link );
			wp_die( sprintf( __( 'There is no content selected, so there is no information to display. Please <a href="%s">return to the governance reports</a> and select a page to view.', 'cornell/governance' ), $link ) );
		}

		/**
		 * Output an error message indicating that the current user does not have permission to view the selected page
		 *
		 * @access protected
		 * @since  2023.04
		 * @return void
		 */
		protected function no_permissions() {
			$link = admin_url('admin.php');
			$link = add_query_arg( array( 'page' => $this->page ), $link );
			wp_die( sprintf( __( 'You do not appear to have permission to manage the selected piece of content. Please <a href="%s">return to the governance reports</a> and select a page to view.', 'cornell/governance' ), $link ) );
		}

		/**
		 * Retrieve and output the Info meta data for this page
		 *
		 * @access protected
		 * @since  2023.04
		 * @return void
		 */
		protected function display_info() {
			if ( ! current_user_can( 'edit_post', $_GET['post'] ) ) {
				return;
			}
			$this->info_meta = Info::instance()->meta;

			print( '<div class="governance-box info-meta">' );
			printf( '<h3>%s</h3>', __( 'General Governance Information', 'cornell/governance' ) );
			$this->display_responsibility_section();
			$this->display_audience_section();
			$this->display_details_section();
			$this->display_review_section();
			print( '</div>' );
		}

		/**
		 * Output the Responsibility section of the Info meta box
		 *
		 * @access protected
		 * @since  2023.04
		 * @return void
		 */
		protected function display_responsibility_section() {
			print( '<details class="responsibility page-meta-section">' );
			printf( '<summary>%s</summary>', __( 'Responsibility', 'cornell/governance' ) );
			$list = array();
			$list['steward'] = array(
				'label' => __( 'Primary Page Steward', 'cornell/governance' ),
				'value' => $this->info_meta['steward']
			);
			$list['supervisor'] = array(
				'label' => __( 'Office or supervisor email address', 'cornell/governance' ),
				'value' => $this->info_meta['supervisor'],
			);
			$list['liaison'] = array(
				'label' => sprintf( __( '%s Liaison', 'cornell/governance' ), Plugin::instance()->get_managing_office() ),
				'value' => $this->info_meta['liaison'],
			);
			print( '<dl class="responsibility-list">' );
			foreach ( $list as $item ) {
				printf( '<dt>%1$s</dt><dd><a href="mailto:%2$s">%2$s</a></dd>', $item['label'], $item['value'] );
			}
			print( '</dl>' );
			print( '</details>' );
		}

		/**
		 * Output the Audience section of this governance information
		 *
		 * @access protected
		 * @since  2023.04
		 * @return void
		 */
		protected function display_audience_section() {
			print( '<details class="audiences page-meta-section">' );
			printf( '<summary>%s</summary>', __( 'Content Audiences', 'cornell/governance' ) );
			print( '<dl class="audience-list">' );
			printf( '<dt>%1$s</dt><dd>%2$s</dd>', __( 'Primary Audience', 'cornell/governance' ), $this->info_meta['primary-audience'] );
			printf( '<dt>%1$s</dt><dd>%2$s</dd>', __( 'Secondary Audience', 'cornell/governance' ), $this->info_meta['secondary-audience'] );
			print( '</dl>' );
			print( '</details>' );
		}

		/**
		 * Output the Details section of the governance information
		 *
		 * @access protected
		 * @since  2023.04
		 * @return void
		 */
		protected function display_details_section() {
			print( '<details class="details page-meta-section">' );
			printf( '<summary>%s</summary>', __( 'Page Details', 'cornell/governance' ) );
			print( '<dl class="audience-list">' );
			printf( '<dt>%1$s</dt><dd>%2$s</dd>', __( 'Page Goal', 'cornell/governance' ), $this->info_meta['goals'] );
			printf( '<dt>%1$s</dt><dd>%2$s</dd>', __( 'What problem are we trying to solve for the user?', 'cornell/governance' ), $this->info_meta['problem'] );
			printf( '<dt>%1$s</dt><dd>%2$s</dd>', __( 'On-page tasks', 'cornell/governance' ), sprintf( '<ol><li>%s</li></ol>', implode( '</li><li>', $this->info_meta['tasks'] ) ) );
			print( '</dl>' );
			print( '</details>' );
		}

		/**
		 * Output the Review information for this page
		 *
		 * @access protected
		 * @since  2023.04
		 * @return void
		 */
		protected function display_review_section() {
			print( '<details class="review page-meta-section">' );
			printf( '<summary>%s</summary>', __( 'Review', 'cornell/governance' ) );
			print( '<dl class="review-information">' );

			$value_text = array();
			foreach ( Review_Cycle::instance()->get_options() as $value => $label ) {
				if ( intval( $this->info_meta['review-cycle'] ) === intval( $value ) ) {
					$value_text[] = $label;
				}
			}
			printf( '<dt>%1$s</dt><dd>%2$s</dd>', __( 'Review Cycle', 'cornell/governance' ), implode( ' ', $value_text ) );

			$last_reviewed = $this->info_meta['last-review'];
			$next_review = Helpers::calculate_next_review_date( $last_reviewed, $this->info_meta['review-cycle'] );

			printf( '<dt>%1$s</dt><dd>%2$s</dd>', __( 'Last Reviewed', 'cornell/governance' ), Helpers::format_date( $last_reviewed ) );
			printf( '<dt>%1$s</dt><dd>%2$s</dd>', __( 'Next Review Due', 'cornell/governance' ), Helpers::format_date( $next_review ) );
			print( '</dl>' );
			print( '</details>' );
		}

		/**
		 * Retrieve and output the Notes meta data for this page
		 *
		 * @access protected
		 * @since  2023.04
		 * @return void
		 */
		protected function display_notes() {
			if ( ! current_user_can( Plugin::instance()->get_capability() ) ) {
				return;
			}

			$this->notes_meta = Meta_Boxes\Notes::instance()->meta;

			printf( '<details class="notes page-meta-section"><summary>%s</summary>', __( 'Page Notes', 'cornell/governance' ) );
			printf( '<div class="notes-details">%s</div>', $this->notes_meta['notes'] );
			print( '</details>' );
		}

		/**
		 * Retrieve and output the Revisions commit messages for this page
		 *
		 * @access protected
		 * @since  2023.04
		 * @return void
		 */
		protected function display_revisions() {
			if ( ! current_user_can( 'edit_post', $_GET['post'] ) ) {
				return;
			}

			$messages = Meta_Boxes\Notes::instance()->get_commit_messages( $_GET['post'] );
			$output = '';
			if ( count( $messages ) > 0 ) {
				$output .= '<ol class="commit-messages">';
				$output .= sprintf( '<li>%s</li>', implode( '</li><li>', $messages ) );
				$output .= '</ol>';
				$output .= '</details>';
			} else {
				$output .= sprintf( '<p>%s</p>', __( 'There are no recent commit messages to display for this page', 'cornell/governance' ) );
			}

			print( '<details class="commit-messages page-meta-section">' );
			printf( '<summary>%s</summary>', __( 'Recent Commit Messages', 'cornell/governance' ) );
			echo $output;
			print( '</div>' );
		}

		/**
		 * Adds the screen options to the page
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function add_options() {
			$option = 'per_page';
			$args   = array(
				'label'   => __( 'Pages', 'cornell/governance' ),
				'default' => 50,
				'option'  => $this->per_page_option,
			);
			add_screen_option( $option, $args );

			$this->table = new Tables\Steward_Page_List();
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