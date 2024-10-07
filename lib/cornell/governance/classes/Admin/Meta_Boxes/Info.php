<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes {

	use Cornell\Governance\Admin\Admin;
	use Cornell\Governance\Admin\Fields\Initial_Prompt;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Completed_Review;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Completed_Review_Instructions;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Goals;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Info_Timestamp;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Last_Review;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Liaison;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Mark_For_Deletion;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Mark_For_Deletion_Instructions;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Mark_For_Deletion_Tooltip;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Primary_Audience;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Problem;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Request_Changes;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Review_Cycle;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Review_Cycle_Instructions;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Save_Info;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Save_Info_Instructions;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Secondary_Audience;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Steward;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Steward_Tooltip;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Supervisor;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Tasks;
	use Cornell\Governance\Emails\General\Compliant;
	use Cornell\Governance\Emails\General\Deletion;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( 'Info' ) ) {
		class Info extends Base {
			/**
			 * @var Info $instance holds the single instance of this class
			 * @access private
			 */
			protected static Info $instance;

			function __construct() {
				parent::__construct( array(
					'id'       => 'cornell-governance-page-info',
					'title'    => __( 'Page Governance Information', 'cornell/governance' ),
					'context'  => 'advanced',
					'priority' => 'high',
					'fields'   => array(
						'goals'              => 'Goals',
						'last-reviewed'      => 'Last_Review',
						'steward'            => 'Steward',
						'primary-audience'   => 'Primary_Audience',
						'problem'            => 'Problem',
						'review-cycle'       => 'Review_Cycle',
						'secondary-audience' => 'Secondary_Audience',
						'supervisor'         => 'Supervisor',
						'tasks'              => 'Tasks',
						'liaison'            => 'Liaison',
						'timestamp'          => 'Info_Timestamp',
					),
					'meta_key' => 'cornell/governance/information',
				) );

				$this->get_meta_data();

				$this->maybe_unhook_metabox();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Info
			 * @since   0.1
			 */
			public static function instance(): Info {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Determine whether we should remove this metabox for any reason
			 *
			 * @access protected
			 * @since  0.4.8
			 * @return void
			 */
			protected function maybe_unhook_metabox() {
				if ( empty( $this->meta['goals'] ) && ! current_user_can( Plugin::instance()->get_capability() ) ) {
					$this->unhook_metabox();
				}

				$post_id = 0;

				if ( isset( $_GET['post'] ) ) {
					$post_id = $_GET['post'];
				} else if ( isset( $GLOBALS['post'] ) ) {
					if ( is_numeric( $GLOBALS['post'] ) ) {
						$post_id = $GLOBALS['post'];
					} else {
						$post_id = $GLOBALS['post']->ID;
					}
				}

				if ( ! empty( $post_id ) ) {
					$post = get_post( $post_id );
					$author = $post->post_author;
					$current = get_current_user_id();

					if ( ! current_user_can( Plugin::instance()->get_capability() ) && intval( $author ) !== intval( $current ) ) {
						$this->unhook_metabox();
					}
				}
			}

			/**
			 * Retrieve the values of the current post meta
			 *
			 * @access protected
			 * @return void
			 * @since  0.1
			 */
			protected function get_meta_data(): void {
				if ( isset( $_GET['post'] ) ) {
					$info = get_post_meta( $_GET['post'], $this->meta_key, true );
				} else if ( isset( $GLOBALS['post'] ) ) {
					if ( is_numeric( $GLOBALS['post'] ) ) {
						$info = get_post_meta( $GLOBALS['post'], $this->meta_key );
					} else {
						$info = get_post_meta( $GLOBALS['post']->ID, $this->meta_key );
					}
				} else {
					$info                = array();
					$info['last-review'] = 0;
				}

				if ( false === $info ) {
					$this->meta                = array();
					$this->meta['last-review'] = 0;

					return;
				} else if ( empty( $info ) ) {
					$this->meta = array();
				} else if ( is_array( $info ) ) {
					$this->meta = $info;
				}

				if ( ! array_key_exists( 'last-review', $this->meta ) ) {
					$this->meta['last-review'] = 0;
				}
			}

			/**
			 * Build the content of the meta box
			 *
			 * @access protected
			 * @return string
			 * @throws \Exception
			 * @since  0.1
			 */
			protected function get_meta_box(): string {
				$post_id = 0;
				if ( isset( $_REQUEST['post'] ) ) {
					$post_id = $_REQUEST['post'];
				} else if ( isset( $GLOBALS['post'] ) ) {
					if ( is_numeric( $GLOBALS['post'] ) ) {
						$post_id = $GLOBALS['post'];
					} else if ( is_a( $GLOBALS['post'], '\WP_Post' ) ) {
						$post_id = $GLOBALS['post']->ID;
					}
				}

				$output = '';

				if ( empty( $post_id ) ) {
					return __( '<p class="note">You will not be able to set up governance information until you have saved this piece of content for the first time</p>', 'cornell/governance' );
					/*} else {
						$output .= '<p>The current post ID appears to be: ' . $post_id . '</p>';*/
				}

				wp_localize_script( 'cornell-governance-admin', 'CornellGovernanceAdminAJAX', array(
					'info'  => array(
						'ajax_url'    => admin_url( 'admin-ajax.php' ),
						'ajax_action' => 'cornell_governance_save_meta',
					),
					'notes' => array(
						'ajax_url'    => admin_url( 'admin-ajax.php' ),
						'ajax_action' => 'cornell_governance_save_meta',
					),
				) );

				$post = get_post( $post_id );

				$due             = \DateTime::createFromFormat( 'U', $this->get_next_review() );
				$now             = new \DateTime();
				$compliance_time = Initial_Prompt::instance()->get_input_value();
				$interval        = new \DateInterval( 'P' . $compliance_time . 'D' );

				$output .= wp_nonce_field( $this->id, $this->id . '-nonce', true, false );
				$output .= sprintf( '<input type="hidden" name="cornell-governance-info-post-id" value="%d"/>', $post_id );
				$output .= sprintf( '<input type="hidden" name="cornell-governance-action" value="%s"/>', 'info' );

				/**
				 * This is a "liaison"-owned page, so all liaisons should have access to review it
				 */
				$liaison_owned = false;
				if ( Helpers::user_can( 0, Plugin::instance()->get_capability() ) && author_can( $post_id, Plugin::instance()->get_capability() ) && get_current_user_id() !== $post->post_author ) {
					$liaison_owned = true;
				}

				if ( $liaison_owned ) {
					$output .= '<div class="tabs">
<div role="tablist" aria-label="' . __( 'Role Switching', 'cornell/governance' ) . '">
<button
      role="tab"
      aria-selected="true"
      aria-controls="panel-1"
      id="tab-1"
      tabindex="0">
      ' . __( 'Liaison Role', 'cornell/governance' ) . '
    </button>
    <button
      role="tab"
      aria-selected="false"
      aria-controls="panel-2"
      id="tab-2"
      tabindex="-1">
      ' . __( 'Steward Role', 'cornell/governance' ) . '
    </button>
    </div>
    <div id="panel-1" aria-labelledby="tab-1" role="tabpanel" tabindex="0">';
				}

				$output .= $this->get_form_content( $post );

				if ( $liaison_owned ) {
					$output .= '</div>';
				}

				if ( $liaison_owned ) {
					$user   = wp_get_current_user();
					$author = $post->post_author;

					wp_set_current_user( Helpers::get_sample_author() );

					$output .= '<div id="panel-2" role="tabpanel" tabindex="0" aria-labelledby="tab-2" hidden>';
					$output .= $this->get_form_content( $post_id, true );
					$output .= '</div>';
					$output .= '</div>';

					wp_set_current_user( $user );
				}

				$classes = array( 'cornell-governance-metabox' );
				if ( ! Helpers::user_can( 0, Plugin::instance()->get_capability() ) ) {
					$classes[] = 'read-only';
				}

				$output .= sprintf( '<div class="field-note timestamp-container">%s</div>', Info_Timestamp::instance()->get_input() );

				return sprintf( '<div class="%1$s">%2$s</div>', implode( ' ', $classes ), $output );
			}

			/**
			 * Build the main content of the form that may be repeated for liaisons
			 *
			 * @param int|\WP_Post $post the post being managed
			 * @param bool $read_only whether we should use the default classes or readonly versions
			 *
			 * @access private
			 * @return string the form content
			 * @throws \Exception
			 * @since 1.0.26
			 */
			private function get_form_content( $post, bool $read_only = false ): string {
				$namespace = __NAMESPACE__ . '\Fields';
				if ( $read_only ) {
					$namespace .= '\Readonly';
				} else {
					$namespace .= '\Writable';
				}
				$namespace .= '\\';

				$output = '';

				if ( is_a( $post, '\WP_Post' ) ) {
					$post_id = $post->ID;
				} else if ( is_numeric( $post ) ) {
					$post_id = $post;
					$post    = get_post( $post_id );
				}

				$due             = \DateTime::createFromFormat( 'U', $this->get_next_review() );
				$now             = new \DateTime();
				$compliance_time = Initial_Prompt::instance()->get_input_value();
				$interval        = new \DateInterval( 'P' . $compliance_time . 'D' );

				$class  = $namespace . 'Goals';
				$output .= $class::instance()->get_input();

				$class  = $namespace . 'Problem';
				$output .= $class::instance()->get_input();

				$class     = $namespace . 'Primary_Audience';
				$audiences = $class::instance()->get_input();

				$class     = $namespace . 'Secondary_Audience';
				$audiences .= $class::instance()->get_input();

				$output .= sprintf( '<div class="cornell-governance-grid one-one">%s</div>', $audiences );

				$class   = $namespace . 'Steward';
				$steward = $class::instance()->get_input();

				$class   = $namespace . 'Steward_Tooltip';
				$steward .= $class::instance()->get_input();

				$steward        = sprintf( '<div class="cornell-governance-grid two-one">%s</div>', $steward );
				$responsibility = $steward;

				$class          = $namespace . 'Supervisor';
				$responsibility .= $class::instance()->get_input();

				$class          = $namespace . 'Liaison';
				$responsibility .= $class::instance()->get_input();

				$responsibility = sprintf( '<fieldset class="one-third"><legend>%1$s</legend>%2$s</fieldset>', __( 'Responsibility', 'cornell/governance' ), $responsibility );

				$class  = $namespace . 'Review_Cycle';
				$review = $class::instance()->get_input();

				/*$review         .= Review_Cycle_Instructions::instance()->get_input();*/

				$review .= $this->build_compliance_fieldset( $read_only );

				if ( Helpers::user_can( 0, Plugin::instance()->get_capability() ) || $due->sub( $interval ) <= $now ) {
					$class  = $namespace . 'Tasks';
					$review .= $class::instance()->get_input();
				} else {
					$class  = $namespace . 'Tasks';
					$review .= $class::instance()->get_plain_list();
				}

				$review = sprintf( '<fieldset class="two-thirds"><legend>%1$s</legend>%2$s</fieldset>', __( 'Governance Review', 'cornell/governance' ), $review );
				$output .= sprintf( '<div class="cornell-governance-grid one-two">%s</div>', $responsibility . $review );

				$confirmation = '';
				$request      = '';

				if ( ! Helpers::user_can( 0, Plugin::instance()->get_capability() ) || $read_only ) {
					if ( true === Plugin::instance()->get_change_form_var( 'active' ) ) {
						$request .= Request_Changes::instance()->get_input();
					}

					$class = $namespace . 'Mark_For_Deletion_Instructions';
					$confirmation .= $class::instance()->get_input();

					$class = $namespace . 'Mark_For_Deletion';
					$confirmation .= $class::instance()->get_input();

					$class = $namespace . 'Completed_Review_Instructions';
					$confirmation .= $class::instance()->get_input();

					$class = $namespace . 'Completed_Review';
					$confirmation .= $class::instance()->get_input();

					$class = $namespace . 'Save_Info';
					$confirmation .= $class::instance()->get_input();

					$output .= sprintf( '<fieldset><legend>%1$s</legend>%2$s</fieldset>', __( 'Governance Review', 'cornell/governance' ), $confirmation );
				} else {
					$output .= '<div class="cornell-governance-save-box">';

					$class = $namespace . 'Save_Info_Instructions';
					$output .= $class::instance()->get_input();

					$class = $namespace . 'Save_Info';
					$output .= $class::instance()->get_input();
					$output .= '</div>';
				}

				if ( ! empty( $request ) ) {
					$output .= $request;
				}

				return $output;
			}

			/**
			 * Build a fieldset with compliance status and review dates
			 *
			 * @param bool $read_only whether we should use the default classes or readonly versions
			 *
			 * @access private
			 * @return string the fieldset HTML
			 * @throws \Exception
			 * @since  2023.05
			 */
			private function build_compliance_fieldset( bool $read_only = false ): string {
				$next_review   = $this->get_next_review();
				$last_reviewed = $this->meta['last-review'];
				$initial_setup = ( is_array( $this->meta ) && array_key_exists( 'initial-setup', $this->meta ) ) ? $this->meta['initial-setup'] : array();

				if ( empty( $initial_setup ) ) {
					if ( Helpers::user_can( 0, Plugin::instance()->get_capability() ) ) {
						$rt = sprintf( '<input type="hidden" name="cornell-governance-page-info-initial-setup[time]" value="%d"/>', time() );
						$rt .= sprintf( '<input type="hidden" name="cornell-governance-page-info-initial-setup[user]" value="%d"/>', get_current_user_id() );

						return $rt;
					}

					return '';
				}

				$compliance_time = Initial_Prompt::instance()->get_input_value();

				$due_date = \DateTime::createFromFormat( 'U', $next_review );
				$now_date = new \DateTime();
				$compare  = new \DateInterval( 'P' . $compliance_time . 'D' );

				$overdue = ( $now_date >= $due_date );
				$due     = ( $now_date->add( $compare ) >= $due_date );

				$legend = $due ? __( 'This page is due for review', 'cornell/governance' ) : __( 'This page is in compliance', 'cornell/governance' );
				if ( $overdue ) {
					$legend = __( 'This page is out of compliance', 'cornell/governance' );
				}
				$classes = array( 'calendar-icon' );
				if ( $overdue ) {
					$classes[] = 'overdue';
				} else if ( $due ) {
					$classes[] = 'due';
				} else {
					$classes[] = 'compliant';
				}
				$time = array(
					'datetime' => date( "Y-m-d", $next_review ),
					'weekday'  => date( "D", $next_review ),
					'month'    => date( "F", $next_review ),
					'day'      => date( "j", $next_review ),
				);

				$icon = sprintf( '<div class="%1$s" aria-hidden="true">
  <time datetime="%2$s" class="icon">
    <span class="weekday">%3$s</span>
    <span class="month">%4$s</span>
    <span class="day">%5$s</span>
  </time>
  <span class="compliance-icon"></span>
</div>',
					implode( ' ', $classes ),
					$time['datetime'],
					$time['weekday'],
					$time['month'],
					$time['day']
				);

				$namespace = __NAMESPACE__ . '\Fields';
				if ( $read_only ) {
					$namespace .= '\Readonly';
				} else {
					$namespace .= '\Writable';
				}
				$namespace .= '\\';

				$class = $namespace . 'Last_Review';

				return sprintf(
					'
<fieldset class="%4$s">
	<legend>%1$s</legend>
	<div class="cornell-governance-grid one-two">
		<div class="one-third">%2$s</div>
		<div class="two-thirds">%3$s</div>
	</div>
</fieldset>',
					$legend,
					$icon,
					$class::instance()->get_input(),
					$read_only ? 'compliance-status-fieldset-readonly compliance-status-fieldset' : 'compliance-status-fieldset'
				);
			}

			/**
			 * Determine the next review date based on last review and review cycle
			 *
			 * @access public
			 * @return int the next review timestamp
			 * @since  0.1
			 */
			public function get_next_review(): int {
				if ( array_key_exists( 'last-review', $this->meta ) ) {
					$last_reviewed = $this->meta['last-review'];
				} else {
					$last_reviewed = 0;
				}

				if ( array_key_exists( 'review-cycle', $this->meta ) ) {
					$cycle = $this->meta['review-cycle'];
				} else {
					$cycle = Fields\Writable\Review_Cycle::instance()->get_input_value();
				}

				return Helpers::calculate_next_review_date( $last_reviewed, $cycle );
			}

			/**
			 * Update the lists of last review and next review
			 *
			 * @param int $post_id the ID of the post being updated
			 *
			 * @access protected
			 * @return void
			 * @since  0.1
			 */
			protected function update_email_lists( int $post_id ): void {
				$this->update_last_reviewed_list( $post_id );

				$this->update_next_review_list( $post_id );
			}

			/**
			 * Update the last-reviewed option
			 *
			 * @param int $post_id the ID of the post being updated
			 *
			 * @access private
			 * @return void
			 * @since  0.1
			 */
			private function update_last_reviewed_list( int $post_id ): void {
				if ( ! array_key_exists( 'last-review', $this->meta ) ) {
					return;
				}

				$last_reviews = get_option( 'cornell/governance/last-reviews', array() );
				if ( ! is_array( $last_reviews ) ) {
					$last_reviews = array();
				}

				$last_reviews[ 'post-' . $post_id ] = array(
					'last-review' => $this->meta['last-review'],
					'emails'      => array(
						'steward'    => $this->meta['steward'],
						'supervisor' => $this->meta['supervisor'],
						'liaison'    => $this->meta['liaison'],
					),
				);

				update_option( 'cornell/governance/last-reviews', $last_reviews );
			}

			/**
			 * Update the next-review option
			 *
			 * @param int $post_id the ID of the post being updated
			 *
			 * @access private
			 * @return void
			 * @since  0.1
			 */
			private function update_next_review_list( int $post_id ): void {
				if ( ! array_key_exists( 'last-review', $this->meta ) ) {
					return;
				}

				$next_reviews = get_option( 'cornell/governance/next-reviews', array() );
				if ( ! is_array( $next_reviews ) ) {
					$next_reviews = array();
				}

				foreach ( $next_reviews as $month => $info ) {
					if ( array_key_exists( 'post-' . $post_id, $info ) ) {
						unset( $next_reviews[ $month ][ 'post-' . $post_id ] );
					}

					if ( empty( $next_reviews[ $month ] ) ) {
						unset( $next_reviews[ $month ] );
					}
				}

				$this_review = $this->get_next_review();
				$review_date = getdate( $this_review );

				$next_reviews[ $review_date['year'] . '-' . $review_date['mon'] ][ 'post-' . $post_id ] = array(
					'last-review' => $this->meta['last-review'],
					'emails'      => array(
						'steward'    => $this->meta['steward'],
						'supervisor' => $this->meta['supervisor'],
						'liaison'    => $this->meta['liaison'],
					),
				);

				array_filter( $next_reviews );
				update_option( 'cornell/governance/next-reviews', $next_reviews );
			}

			/**
			 * Perform the AJAX action
			 *
			 * @return void
			 * @throws \Exception
			 */
			public function ajax_save() {
				if ( array_key_exists( 'save-action', $_POST ) ) {
					return $this->do_ajax_save_action();
				}

				$post_id = intval( $_POST['cornell-governance-info-post-id'] );
				if ( empty( $post_id ) ) {
					return;
				}

				$meta = get_post_meta( $post_id, $this->meta_key, true );
				if ( ! is_array( $meta ) ) {
					$meta = array();
				}

				if (
					array_key_exists( 'cornell-governance-page-info-save', $_POST ) && $_POST['cornell-governance-page-info-save'] == __( 'Confirm Page Review', 'cornell/governance')
					||
					array_key_exists( 'cornell-governance-page-info-save-readonly', $_POST ) && $_POST['cornell-governance-page-info-save-readonly'] == __( 'Confirm Page Review', 'cornell/governance')
				) {
					Helpers::log( 'We are preparing to confirm a new page review, and will bail out when we are done' );

					$last_review = array_key_exists( 'last-review', $meta ) ? $meta['last-review'] : 0;
					$due         = Helpers::calculate_next_review_date( $last_review, $meta['review-cycle'] );
					$next_review = Helpers::calculate_next_review_date( time(), $meta['review-cycle'] );

					if (
						array_key_exists( 'cornell-governance-page-completed-review', $_POST ) && 1 == $_POST['cornell-governance-page-completed-review']
						||
						array_key_exists( 'cornell-governance-page-completed-review-readonly', $_POST ) && 1 == $_POST['cornell-governance-page-completed-review-readonly']
					) {
						$meta['last-review']     = time();
						$meta['completed-tasks'] = array();

						update_post_meta( $post_id, $this->meta_key, $meta );

						$this->dispatch_compliance_email( $due, $post_id, $next_review );
					}


					if (
						array_key_exists( 'cornell-governance-page-info-mark-for-deletion', $_POST ) && 1 == $_POST['cornell-governance-page-info-mark-for-deletion']
						||
						array_key_exists( 'cornell-governance-page-info-mark-for-deletion-readonly', $_POST ) && 1 == $_POST['cornell-governance-page-info-mark-for-deletion-readonly']
					) {
						if ( ! array_key_exists( 'mark-for-deletion', $meta ) && 1 == $meta['mark-for-deletion'] ) {
							$meta['mark-for-deletion'] = 1;

							update_post_meta( $post_id, $this->meta_key, $meta );

							$this->dispatch_deletion_email( $due, $post_id, $next_review );
						}
					} else if ( array_key_exists( 'mark-for-deletion', $meta ) ) {
						Helpers::log( 'It appears that we are unmarking this page for deletion' );
						$meta['mark-for-deletion'] = 0;
						update_post_meta( $post_id, $this->meta_key, $meta );
					}

					$this->meta = get_post_meta( $post_id, $this->meta_key, true );

					$this->meta['compliance-fieldset'] = array(
						'liaison' => $this->build_compliance_fieldset( false ),
						'steward' => $this->build_compliance_fieldset( true ),
					);

					$this->meta['task-list-checkboxes'] = \Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly\Tasks::instance()->get_plain_list();

					wp_send_json_success( $this->meta, 200 );

					return;
				}

				Helpers::log( 'We are preparing to save updated Governance Information for this page with an ID of ' . $post_id );

				$post = get_post( $post_id );

				$this->meta = $this->save( $post_id, $post, true );

				if ( is_wp_error( $this->meta ) ) {
					Helpers::log('There was an error on saving this information: ' . $this->meta->get_error_message() );
					wp_send_json_error( $this->meta, 500 );
				} else {
					$this->update_email_lists( $post_id );

					$this->meta['task-list-checkboxes'] = \Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly\Tasks::instance()->get_input();

					Helpers::log('The following information should have been saved: ' . print_r( $this->meta, true ) );

					wp_send_json_success( $this->meta, 200 );
				}
			}

			/**
			 * If a specific save action was specified, perform that
			 *
			 * @access protected
			 * @return void
			 * @since  2023.05
			 */
			protected function do_ajax_save_action() {
				$post_id = intval( $_POST['cornell-governance-info-post-id'] );
				if ( empty( $post_id ) ) {
					return;
				}

				$meta = get_post_meta( $post_id, $this->meta_key, true );
				if ( ! is_array( $meta ) ) {
					$meta = array();
				}

				if ( 'completed-tasks' === $_POST['save-action'] ) {
					if ( array_key_exists( 'cornell-governance-page-info-tasks-readonly', $_POST ) ) {
						$meta['completed-tasks'] = $_POST['cornell-governance-page-info-tasks-readonly'];
					} else {
						$meta['completed-tasks'] = $_POST['cornell-governance-page-info-tasks'];
					}
				}

				update_post_meta( $post_id, $this->meta_key, $meta );

				$this->meta = $meta;

				wp_send_json_success( $this->meta, 200 );
			}

			/**
			 * Dispatch "Compliant" email messages as necessary
			 *
			 * @param int $due the timestamp for the date on which the review was due
			 * @param int $post_id the ID of the post being updated
			 * @param int $next_review the timestamp for the next review
			 *
			 * @access protected
			 * @return void
			 * @since  0.4.4
			 */
			protected function dispatch_compliance_email( int $due, int $post_id, int $next_review ) {
				$post = get_post( $post_id, ARRAY_A );

				$meta                    = get_post_meta( $post_id, 'cornell/governance/information' );
				$vars['supervisor']      = $meta['supervisor'];
				$vars['liaison']         = $meta['liaison'];
				$vars['steward-email']   = get_the_author_meta( 'email', $post['post_author'] );
				$vars['steward-name']    = get_the_author_meta( 'display_name', $post['post_author'] );
				$vars['last-review']     = Helpers::format_date( $due );
				$vars['next-review']     = Helpers::format_date( $next_review );
				$vars['permalink']       = get_the_permalink( $post_id );
				$vars['managing-office'] = get_option( 'cornell-governance-managing-office', __( 'MarCom', 'cornell/governance' ) );
				$vars['post']            = $post;

				$steward = Compliant::instance();
				$steward->set_template_vars( $vars );
				$steward->set_email_to( $vars['steward-name'] . ' <' . $vars['steward-email'] . '>' );

				if ( $due <= strtotime( '+7 days' ) ) {
					$emails = array();
					if ( is_email( $vars['supervisor'] ) ) {
						if ( is_email( CORNELL_GOVERNANCE_EMAIL_TO ) && CORNELL_DEBUG ) {
							$emails[] = 'Cc: ' . is_email( $vars['supervisor'] ) . ' <' . CORNELL_GOVERNANCE_EMAIL_TO;
						} else {
							$emails[] = 'Cc: ' . is_email( $vars['supervisor'] );
						}
					}
					if ( is_email( $vars['liaison'] ) ) {
						if ( is_email( CORNELL_GOVERNANCE_EMAIL_TO ) && CORNELL_DEBUG ) {
							$emails[] = 'Cc: ' . is_email( $vars['supervisor'] ) . ' <' . CORNELL_GOVERNANCE_EMAIL_TO;
						} else {
							$emails[] = 'Cc: ' . is_email( $vars['liaison'] );
						}
					}
					if ( count( $emails ) ) {
						$steward->set_headers( $emails );
					}
				}

				$steward->send_mail();
			}

			/**
			 * Notify Liaisons that a page has been marked for deletion
			 *
			 * @param int $due the timestamp for the date on which the review was due
			 * @param int $post_id the ID of the post being updated
			 * @param int $next_review the timestamp for the next review
			 *
			 * @access protected
			 * @return void
			 * @since  1.0.26
			 */
			protected function dispatch_deletion_email( int $due, int $post_id, int $next_review ) {
				$post = get_post( $post_id, ARRAY_A );

				$meta                    = get_post_meta( $post_id, 'cornell/governance/information' );
				$vars['supervisor']      = $meta['supervisor'];
				$vars['liaison']         = $meta['liaison'];
				$vars['steward-email']   = get_the_author_meta( 'email', $post['post_author'] );
				$vars['steward-name']    = get_the_author_meta( 'display_name', $post['post_author'] );
				$vars['last-review']     = Helpers::format_date( $due );
				$vars['next-review']     = Helpers::format_date( $next_review );
				$vars['permalink']       = get_the_permalink( $post_id );
				$vars['managing-office'] = get_option( 'cornell-governance-managing-office', __( 'MarCom', 'cornell/governance' ) );
				$vars['post']            = $post;

				$deletion = Deletion::instance();
				$deletion->set_template_vars( $vars );
				$deletion->set_email_to( $vars['steward-name'] . ' <' . $vars['steward-email'] . '>' );

				$emails = array();
				if ( is_email( $vars['supervisor'] ) ) {
					if ( is_email( CORNELL_GOVERNANCE_EMAIL_TO ) && CORNELL_DEBUG ) {
						$emails[] = 'Cc: ' . is_email( $vars['supervisor'] ) . ' <' . CORNELL_GOVERNANCE_EMAIL_TO;
					} else {
						$emails[] = 'Cc: ' . is_email( $vars['supervisor'] );
					}
				}
				if ( is_email( $vars['liaison'] ) ) {
					if ( is_email( CORNELL_GOVERNANCE_EMAIL_TO ) && CORNELL_DEBUG ) {
						$emails[] = 'Cc: ' . is_email( $vars['supervisor'] ) . ' <' . CORNELL_GOVERNANCE_EMAIL_TO;
					} else {
						$emails[] = 'Cc: ' . is_email( $vars['liaison'] );
					}
				}
				if ( count( $emails ) ) {
					$deletion->set_headers( $emails );
				}

				$deletion->send_mail();
			}
		}
	}
}