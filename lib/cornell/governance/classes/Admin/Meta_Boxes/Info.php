<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Meta_Boxes {

	use Cornell\Governance\Admin\Admin;
	use Cornell\Governance\Admin\Fields\Initial_Prompt;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Edit_Notes_Button;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Deletion_Timestamp;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Info_Timestamp;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Notes_Timestamp;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Request_Changes;
	use Cornell\Governance\Admin\Meta_Boxes\Fields\Save_Notes;
	use Cornell\Governance\Config;
	use Cornell\Governance\Emails\General\Compliant;
	use Cornell\Governance\Emails\General\Deletion;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;
	use Cornell\Governance\Wayback\Retrieve;

	if ( ! class_exists( 'Info' ) ) {
		class Info extends Base {
			/**
			 * @var Info $instance holds the single instance of this class
			 * @access private
			 */
			protected static Info $instance;

			/**
			 * @var int|\WP_User $real_user holds the user ID of the real current WP user
			 * @access private
			 */
			private $real_user = 0;

			/**
			 * @var int|\WP_User $test_author holds the user ID of the current WP users (real or sample)
			 * @access private
			 */
			private $test_author = 0;

			/**
			 * @var bool $liaison_owned whether a Liaison is the author of this page or not
			 * @access private
			 */
			private bool $liaison_owned = false;

			/**
			 * @var array $tab_handles
			 * @access private
			 */
			private array $tab_handles = array();

			/**
			 * @var int $tab_index
			 * @access private
			 */
			private static $tab_index = 1;

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
					'meta_key' => Plugin::INFO_META_KEY,
				) );

				$this->real_user   = wp_get_current_user();
				$this->test_author = wp_get_current_user();

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
			 * Retrieve and return the action property of the current screen object
			 *
			 * @access private
			 * @return string
			 * @since  0.6.2
			 */
			private function get_current_screen_action(): string {
				if ( is_a( $this->current_screen, 'WP_Screen' ) ) {
					return $this->current_screen->action;
				}

				return '';
			}

			/**
			 * Retrieve and return the appropriate namespace for input fields
			 *
			 * @param bool $read_only whether the fields should be readonly or not
			 *
			 * @access protected
			 * @return string the namespace
			 * @since  0.4.9
			 */
			protected function get_field_namespace( bool $read_only = false ): string {
				$namespace = __NAMESPACE__ . '\Fields';
				if ( $read_only ) {
					$namespace .= '\Readonly';
				} else {
					$namespace .= '\Writable';
				}
				$namespace .= '\\';

				return $namespace;
			}

			/**
			 * Determine whether we should remove this metabox for any reason
			 *
			 * @access protected
			 * @return void
			 * @since  0.4.8
			 */
			protected function maybe_unhook_metabox() {
				if ( empty( $this->meta['goals'] ) && ! current_user_can( Plugin::instance()->get_capability() ) ) {
					$this->unhook_metabox();
				}

				$post_id = Helpers::get_current_post_id();

				if ( ! empty( $post_id ) ) {
					if ( ! Helpers::can_edit_governance() && ! Helpers::can_review_page( $post_id ) && ! Helpers::can_edit_page( $post_id ) ) {
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
				$post_id = Helpers::get_current_post_id();

				if ( ! empty( $post_id ) ) {
					$info = get_post_meta( $post_id, $this->meta_key, true );
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

				$this->meta = shortcode_atts( $this->get_default_meta(), $this->meta );
			}

			/**
			 * Retrieve and return the empty default array of meta data
			 *
			 * @access protected
			 * @return array the empty default array of data
			 * @since  0.4.9
			 */
			protected function get_default_meta(): array {
				return array(
					'goals'              => null,
					'primary-audience'   => null,
					'problem'            => null,
					'review-cycle'       => null,
					'secondary-audience' => null,
					'supervisor'         => null,
					'tasks'              =>
						array(),
					'liaison'            => null,
					'last-review'        => null,
					'completed-tasks'    =>
						array(),
					'timestamp'          => null,
					'initial-setup'      =>
						array(),
					'mark-for-deletion'  => array(),
				);
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
				$post_id = Helpers::get_current_post_id();

				Helpers::log( 'The post ID of the current post appears to be: ' . $post_id, 'info' );

				$output = '';

				if ( empty( $post_id ) || 'add' === $this->get_current_screen_action() ) {
					return __( '<p class="note">You will not be able to set up governance information until you have saved this piece of content for the first time</p>', 'cornell/governance' );
					/*} else {
						$output .= '<p>The current post ID appears to be: ' . $post_id . '</p>';*/
				}

				wp_localize_script( 'cornell-governance-admin', 'CornellGovernanceAdminAJAX', array(
					'info'     => array(
						'ajax_url'    => admin_url( 'admin-ajax.php' ),
						'ajax_action' => 'cornell_governance_save_meta',
					),
					'notes'    => array(
						'ajax_url'    => admin_url( 'admin-ajax.php' ),
						'ajax_action' => 'cornell_governance_save_meta',
					),
					'deletion' => array(
						'ajax_url'    => admin_url( 'admin-ajax.php' ),
						'ajax_action' => 'cornell_governance_save_meta',
					)
				) );

				$post = get_post( $post_id );

				$due             = \DateTime::createFromFormat( 'U', $this->get_next_review() );
				$now             = new \DateTime();
				$compliance_time = Initial_Prompt::instance()->get_input_value();
				$interval        = new \DateInterval( 'P' . $compliance_time . 'D' );

				/**
				 * This is a "liaison"-owned page, so all liaisons should have access to review it
				 */
				$this->liaison_owned = false;
				if ( Helpers::user_can( 0, Plugin::instance()->get_capability() ) ) {
					$this->liaison_owned = true;
				}

				$output .= '<div class="tabs">';
				$output .= $this->get_tab_handles();
				$output .= sprintf( '<div id="panel-%1$d" aria-labelledby="tab-%1$d" role="tabpanel" class="cornell-governance-tabpanel" tabindex="0">', $this::$tab_index );

				$class  = Helpers::user_can( 0, Plugin::instance()->get_capability() ) ? 'cornell-governance-metabox-tab-liaison' : 'cornell-governance-metabox-tab-steward';
				$output .= sprintf( '<div class="cornell-governance-metabox-tab %1$s" id="%1$s">', $class );

				$output .= $this->get_form_content( $post );

				$output .= sprintf( '<div class="field-note timestamp-container">%s</div>', Info_Timestamp::instance()->get_input() );

				$output .= '</div><!--/ .cornell-governance-metabox-tab -->';

				$output .= '</div><!--/ #panel-1 -->';

				$this::$tab_index ++;

				if ( $this->liaison_owned ) {
					$user   = wp_get_current_user();
					$author = $post->post_author;

					wp_set_current_user( Helpers::get_sample_author() );

					$output .= sprintf( '<div id="panel-%1$d" role="tabpanel" class="cornell-governance-tabpanel" tabindex="0" aria-labelledby="tab-%1$d" hidden>', $this::$tab_index );

					$class  = 'cornell-governance-metabox-tab-steward';
					$output .= sprintf( '<div class="cornell-governance-metabox-tab %1$s" id="%1$s">', $class );

					$output .= $this->get_form_content( $post_id, true );

					$output .= sprintf( '<div class="field-note timestamp-container">%s</div>', Info_Timestamp::instance()->get_input() );

					$output .= '</div><!--/ .cornell-governance-metabox-tab -->';

					$output .= '</div><!--/ #panel-2 -->';

					$this::$tab_index ++;

					wp_set_current_user( $user );
				}

				$classes = array( 'cornell-governance-metabox' );
				if ( ! Helpers::user_can( 0, Plugin::instance()->get_capability() ) ) {
					$classes[] = 'read-only';
				}

				$output .= $this->get_documentation_tab();

				$output .= $this->get_revisions_tab();

				if ( Plugin::instance()->get_archive_settings( 'active' ) ) {

					$output .= $this->get_archive_tab();

				}

				if ( Plugin::instance()->get_mark_for_deletion_active() ) {

					$output .= $this->get_deletion_tab();

				}

				if ( ! empty( Admin::instance()->get_help_documentation() ) ) {

					$output .= $this->get_help_documentation_tab();

				}

				do_action( 'cornell/governance/metabox/after-tabs' );

				$output .= '</div><!--/ .tabs -->';

				return sprintf( '<div class="%1$s">%2$s</div>', implode( ' ', $classes ), $output );
			}

			/**
			 * Build and return the tab handles for the meta box
			 *
			 * @access protected
			 * @return string the set of tab handles
			 * @since  0.5.1
			 */
			protected function get_tab_handles(): string {
				$tablist = array(
					__( 'Steward View', 'cornell/governance' ),
					__( 'Page Changes', 'cornell/governance' ),
				);

				if ( Plugin::instance()->get_archive_settings( 'active' ) ) {
					$tablist[] = __( 'Archive.org Snapshots', 'cornell/governance' );
				}

				if ( Plugin::instance()->get_mark_for_deletion_active() ) {
					$tablist[] = __( 'Request Page Deletion', 'cornell/governance' );
				}

				if ( $this->liaison_owned ) {
					array_unshift( $tablist, __( 'Liaison View', 'cornell/governance' ) );
				} else if ( Helpers::user_can( 0, Plugin::instance()->get_capability() ) ) {
					$tablist[0] = __( 'Liaison View', 'cornell/governance' );
				}

				if ( ! empty( Admin::instance()->get_help_documentation() ) ) {
					$tablist[] = __( 'Get Help', 'cornell/governance' );
				}

				$this->tab_handles = apply_filters( 'cornell/governance/metabox/tab-handles', $tablist );

				$handles = array();

				for ( $i = 0; $i < count( $tablist ); $i ++ ) {
					$selected  = $i === 0 ? 'true' : 'false';
					$handles[] = sprintf( '<button
      role="tab"
      aria-selected="%3$s"
      aria-controls="panel-%1$d"
      id="tab-%1$d"
      tabindex="0">
      %2$s
    </button>', ( $i + 1 ), $tablist[ $i ], $selected );
				}

				return sprintf(
					'<div role="tablist" aria-label="%s">%s</div>',
					__( 'Role Switching', 'cornell/governance' ),
					implode( '', $handles )
				);
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
				$namespace = $this->get_field_namespace( $read_only );

				$post_id = $post;
				if ( ! is_numeric( $post_id ) ) {
					$post_id = $post->ID;
				}

				$output = '';
				$output .= wp_nonce_field( ( $read_only ? $this->id . '-readonly' : $this->id ), ( $read_only ? $this->id . '-readonly' : $this->id ) . '-nonce', true, false );
				$output .= sprintf( '<input type="hidden" name="cornell-governance-info-post-id" value="%d"/>', $post_id );
				$output .= sprintf( '<input type="hidden" name="cornell-governance-action" value="%s"/>', 'info' );
				$output .= sprintf( '<input type="hidden" name="cornell-governance-current-user" value="%d"/>', get_current_user_id() );

				if ( is_a( $post, '\WP_Post' ) ) {
					$post_id = $post->ID;
				} else if ( is_numeric( $post ) ) {
					$post_id = $post;
					$post    = get_post( $post_id );
				}

				list( 'due' => $due, 'now' => $now, 'compliance_time' => $compliance_time, 'interval' => $interval ) = $this->get_interval_times();

				$output .= $this->fieldset_open( 'cornell-governance-fieldset', __( 'Focus Areas', 'cornell/governance' ) );

				$classes = array(
					'Focus_Areas_Fieldset_Message',
					'Goals',
					'Problem',
				);

				foreach ( $classes as $class ) {
					$name   = $namespace . $class;
					$output .= $name::instance()->get_input();
				}

				$output .= $this->fieldset_close();

				$output .= $this->fieldset_open( 'cornell-governance-fieldset cornell-governance-fieldset-audiences', __( 'Audiences', 'cornell/governance' ) );

				$classes = array(
					'Audiences_Fieldset_Message',
					'Primary_Audience',
					'Secondary_Audience',
				);

				foreach ( $classes as $class ) {
					$name   = $namespace . $class;
					$output .= $name::instance()->get_input();
				}

				$output .= $this->fieldset_close();

				$output .= $this->fieldset_open( 'cornell-governance-fieldset cornell-governance-fieldset-responsibilities', __( 'Responsibilities', 'cornell/governance' ) );

				$classes = array(
					'Page_Responsibilities_Fieldset_Message',
					'Steward',
					'Steward_Tooltip',
					'Supervisor',
					'Liaison',
				);

				foreach ( $classes as $class ) {
					$name   = $namespace . $class;
					$output .= $name::instance()->get_input();
				}

				$output .= $this->fieldset_close();

				$output .= $this->fieldset_open( 'cornell-governance-fieldset cornell-governance-fieldset-review-requirements', __( 'Review Requirements', 'cornell/governance' ) );

				$classes = array(
					'Review_Requirements_Message',
					'Review_Cycle',
				);

				foreach ( $classes as $class ) {
					$name   = $namespace . $class;
					$output .= $name::instance()->get_input();
				}

				$output .= $this->build_compliance_fieldset( $read_only );

				$output .= $this->get_appropriate_tasklist( $read_only );

				$output .= $this->fieldset_close();

				$confirmation = '';
				$request      = '';

				if ( Helpers::can_edit_governance() && ! $read_only ) {
					$output .= '<div class="cornell-governance-save-box">';

					$class  = $namespace . 'Save_Info_Instructions';
					$output .= $class::instance()->get_input();

					$class  = $namespace . 'Save_Info';
					$output .= $class::instance()->get_input();
					$output .= '</div>';
				} else {
					if ( true === Plugin::instance()->get_change_form_var( 'active' ) ) {
						$request .= Request_Changes::instance()->get_input();
					}

					$class        = $namespace . 'Completed_Review_Instructions';
					$confirmation .= $class::instance()->get_input();

					$class        = $namespace . 'Completed_Review';
					$confirmation .= $class::instance()->get_input();

					$class        = $namespace . 'Save_Info';
					$confirmation .= $class::instance()->get_input();

					$output .= $this->fieldset_open( 'cornell-governance-fieldset cornell-governance-fieldset-confirm-review', __( 'Review Confirmation', 'cornell/governance' ) );
					$output .= $confirmation;
					$output .= $this->fieldset_close();
				}

				if ( ! empty( $request ) ) {
					$output .= $request;
				}

				return $output;
			}

			/**
			 * Retrieve and return a list of time-related elements for use in these methods
			 *
			 * @access protected
			 * @return array
			 * @since  0.4.9
			 */
			protected function get_interval_times(): array {
				$due             = \DateTime::createFromFormat( 'U', $this->get_next_review() );
				$now             = new \DateTime();
				$compliance_time = Initial_Prompt::instance()->get_input_value();
				$interval        = new \DateInterval( 'P' . $compliance_time . 'D' );

				return array(
					'due'             => $due,
					'now'             => $now,
					'compliance_time' => $compliance_time,
					'interval'        => $interval,
				);
			}

			/**
			 * Decide which type of list to generate for the task list and return it
			 *
			 * @param bool $read_only whether the field should be readonly or not
			 *
			 * @access public
			 * @return string the task list
			 * @since  0.4.9
			 */
			public function get_appropriate_tasklist( bool $read_only = false ): string {
				$namespace = $this->get_field_namespace( $read_only );

				list( 'due' => $due, 'now' => $now, 'compliance_time' => $compliance_time, 'interval' => $interval ) = $this->get_interval_times();

				Helpers::log( print_r( $due, true ) );

				$review = '';

				$class = $namespace . 'Tasks';

				if ( $due->sub( $interval ) <= $now ) {
					Helpers::log( 'There are review tasks due, so we might generate checkboxes' );

					if ( Helpers::can_review_page() ) {
						Helpers::log( 'The user can review the page, so we will hopefully generate checkboxes' );
						$review .= $class::instance()->get_input();
					} else if ( $this->liaison_owned ) {
						Helpers::log( 'The page is owned by this liaison, so we will hopefully generate checkboxes' );
						$review .= $class::instance()->get_input();
					} else {
						Helpers::log( 'We determined the user is not allowed to edit tasks, so we are generating a plain list' );
						$review .= $class::instance()->get_plain_list();
					}
				} else if ( Helpers::can_edit_governance() && ! $read_only ) {
					Helpers::log( 'We determined the user is allowed to edit governance information, so we should be generating checkboxes' );
					$review .= $class::instance()->get_input();
				} else {
					Helpers::log( 'We determined the user is not allowed to edit tasks, so we are generating a plain list' );
					$review .= $class::instance()->get_plain_list();
				}

				return $review;
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
				$initial_setup = ( is_array( $this->meta ) && array_key_exists( 'initial-setup', $this->meta ) ) ? $this->meta['initial-setup'] : array();

				if ( empty( $initial_setup ) ) {
					if ( Helpers::user_can( 0, Plugin::instance()->get_capability() ) ) {
						$rt = sprintf( '<input type="hidden" name="cornell-governance-page-info-initial-setup[time]" value="%d"/>', time() );
						$rt .= sprintf( '<input type="hidden" name="cornell-governance-page-info-initial-setup[user]" value="%d"/>', get_current_user_id() );

						return $rt;
					}

					return '';
				}

				list(
					'legend' => $legend,
					'overdue' => $overdue,
					'due' => $due,
					'next_review' => $next_review
					) = Helpers::get_compliance_status( $this->meta );

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
					sprintf( __( 'Current Page Status: %s', 'cornell/governance' ), $legend ),
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
					$this->do_ajax_save_action();

					return;
				}

				$post_id = intval( $_POST['cornell-governance-info-post-id'] );
				if ( empty( $post_id ) ) {
					return;
				}

				$meta = get_post_meta( $post_id, $this->meta_key, true );
				if ( ! is_array( $meta ) ) {
					$meta = array();
				}

				// Steward has completed page review
				if (
					array_key_exists( 'cornell-governance-page-info-save', $_POST ) && $_POST['cornell-governance-page-info-save'] == __( 'Confirm Page Review', 'cornell/governance' )
					||
					array_key_exists( 'cornell-governance-page-info-save-readonly', $_POST ) && $_POST['cornell-governance-page-info-save-readonly'] == __( 'Confirm Page Review', 'cornell/governance' )
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

					$this->meta = get_post_meta( $post_id, $this->meta_key, true );

					$this->meta['compliance-fieldset'] = array(
						'liaison' => $this->build_compliance_fieldset( false ),
						'steward' => $this->build_compliance_fieldset( true ),
					);

					$this->meta['task-list-checkboxes'] = $this->get_appropriate_tasklist( true );

					wp_send_json_success( $this->meta, 200 );

					return;
				}

				Helpers::log( 'We are preparing to save updated Governance Information for this page with an ID of ' . $post_id );

				$post = get_post( $post_id );

				$this->meta = $this->save( $post_id, $post, true );

				$fields = array(
					'goals'              => 'Goals',
					'liaison'            => 'Liaison',
					'primary-audience'   => 'Primary_Audience',
					'problem'            => 'Problem',
					'review-cycle'       => 'Review_Cycle',
					'secondary-audience' => 'Secondary_Audience',
					'supervisor'         => 'Supervisor',
				);

				foreach ( $fields as $field => $classname ) {
					if ( array_key_exists( $field, $this->meta ) ) {
						$class = $this->get_field_namespace( true );
						$class = $class . $classname;

						$this->meta[ $field . '-formatted' ] = stripslashes( $class::instance()->get_input() );
					}
				}

				if ( is_wp_error( $this->meta ) ) {
					Helpers::log( 'There was an error on saving this information: ' . $this->meta->get_error_message() );
					wp_send_json_error( $this->meta, 500 );
				} else {
					$this->update_email_lists( $post_id );

					if ( isset( $_POST['cornell-governance-current-user'] ) && is_numeric( $_POST['cornell-governance-current-user'] ) ) {
						$c = get_current_user_id();
						wp_set_current_user( $_POST['cornell-governance-current-user'] );
					}

					$this->meta['task-list-checkboxes'] = $this->get_appropriate_tasklist( true );

					if ( isset( $c ) ) {
						wp_set_current_user( $c );
					}

					$this->meta['compliance-fieldset'] = array(
						'liaison' => $this->build_compliance_fieldset( false ),
						'steward' => $this->build_compliance_fieldset( true ),
					);

					Helpers::log( 'The following information should have been saved: ' . print_r( $this->meta, true ) );

					wp_send_json_success( $this->meta, 200 );
				}
			}

			public function ajax_save_delete() {
				$post_id = intval( $_POST['cornell-governance-deletion-post-id'] );
				if ( empty( $post_id ) ) {
					Helpers::log( 'There did not appear to be a post ID associated with this request', 'warning' );
					wp_send_json_error( array( 'error' => 'There was no post ID associated with the request' ), 400 );

					return;
				}

				$this->meta = get_post_meta( $post_id, $this->meta_key, true );
				if ( ! is_array( $this->meta ) ) {
					$this->meta = array();
				}

				$last_review = array_key_exists( 'last-review', $this->meta ) ? $this->meta['last-review'] : 0;
				$due         = Helpers::calculate_next_review_date( $last_review, $this->meta['review-cycle'] );
				$next_review = Helpers::calculate_next_review_date( time(), $this->meta['review-cycle'] );

				if (
					array_key_exists( 'cornell-governance-page-info-mark-for-deletion', $_POST ) && 1 == $_POST['cornell-governance-page-info-mark-for-deletion']
					||
					array_key_exists( 'cornell-governance-page-info-mark-for-deletion-readonly', $_POST ) && 1 == $_POST['cornell-governance-page-info-mark-for-deletion-readonly']
				) {
					Helpers::log( 'We are marking a page for deletion and dispatching an email message', 'info' );
					$this->meta['mark-for-deletion'] = array(
						'marked'    => empty( $_POST['cornell-governance-page-info-mark-for-deletion'] ) ? 0 : 1,
						'timestamp' => time(),
						'requestor' => get_current_user_id(),
						'reason'    => empty( $_POST['cornell-governance-page-info-deletion-reason'] ) ? '' : $_POST['cornell-governance-page-info-deletion-reason'],
					);

					update_post_meta( $post_id, $this->meta_key, $this->meta );

					$this->dispatch_deletion_email( $due, $post_id, $next_review );

					wp_send_json_success( $this->meta, 200 );
				} else if ( array_key_exists( 'mark-for-deletion', $this->meta ) ) {
					Helpers::log( 'It appears that we are unmarking this page for deletion', 'info' );
					$this->meta['mark-for-deletion'] = array(
						'marked'    => empty( $_POST['cornell-governance-page-info-mark-for-deletion'] ) ? 0 : 1,
						'timestamp' => time(),
						'requestor' => get_current_user_id(),
						'reason'    => empty( $_POST['cornell-governance-page-info-deletion-reason'] ) ? '' : $_POST['cornell-governance-page-info-deletion-reason'],
					);
					update_post_meta( $post_id, $this->meta_key, $this->meta );

					wp_send_json_success( $this->meta, 200 );
				}

				return;
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
			 * Generate the common email variables from the post meta
			 *
			 * @param int $post_id the ID of the post being queried
			 * @param int $due the timestamp for the date on which the review was due
			 * @param int $next_review the timestamp for the next review
			 *
			 * @access private
			 * @return array the array of common variables
			 * @since  0.6.2
			 */
			private function get_common_email_vars( int $post_id, int $due, int $next_review ): array {
				$post = get_post( $post_id, ARRAY_A );
				$vars = array();

				$meta                    = get_post_meta( $post_id, Plugin::INFO_META_KEY, true );
				$vars['supervisor']      = $meta['supervisor'];
				$vars['liaison']         = $meta['liaison'];
				$vars['steward-email']   = get_the_author_meta( 'email', $post['post_author'] );
				$vars['steward-name']    = get_the_author_meta( 'display_name', $post['post_author'] );
				$vars['last-review']     = Helpers::format_date( $due );
				$vars['next-review']     = Helpers::format_date( $next_review );
				$vars['permalink']       = get_the_permalink( $post_id );
				$vars['managing-office'] = get_option( 'cornell-governance-managing-office', __( 'MarCom', 'cornell/governance' ) );
				$vars['post']            = $post;
				$vars['meta']            = $meta;

				return $vars;
			}

			/**
			 * Gather the email addresses to which to send messages
			 *
			 * @param array $vars the template/email variables
			 *
			 * @access private
			 * @return array the email addresses
			 * @since  0.6.2
			 */
			private function get_dispatch_email_addresses( array $vars ): array {
				$emails = array();
				if ( is_email( $vars['supervisor'] ) ) {
					if ( is_email( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_TO' ) ) && Config::instance()->get_var( 'CORNELL_DEBUG' ) ) {
						$emails[] = 'Cc: ' . is_email( $vars['supervisor'] ) . ' <' . Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_TO' ) . '>';
					} else {
						$emails[] = 'Cc: ' . is_email( $vars['supervisor'] );
					}
				}
				if ( is_email( $vars['liaison'] ) ) {
					if ( is_email( Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_TO' ) ) && Config::instance()->get_var( 'CORNELL_DEBUG' ) ) {
						$emails[] = 'Cc: ' . is_email( $vars['liaison'] ) . ' <' . Config::instance()->get_var( 'CORNELL_GOVERNANCE_EMAIL_TO' ) . '>';
					} else {
						$emails[] = 'Cc: ' . is_email( $vars['liaison'] );
					}
				}

				return $emails;
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
				$vars = $this->get_common_email_vars( $post_id, $due, $next_review );

				$steward = Compliant::instance();
				$steward->set_template_vars( $vars );
				$steward->set_email_to( $vars['steward-name'] . ' <' . $vars['steward-email'] . '>' );

				if ( $due <= strtotime( '+7 days' ) ) {
					$emails = $this->get_dispatch_email_addresses( $vars );
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
				$vars = $this->get_common_email_vars( $post_id, $due, $next_review );

				$deletion = Deletion::instance();
				$deletion->set_template_vars( $vars );
				$deletion->set_email_to( $vars['steward-name'] . ' <' . $vars['steward-email'] . '>' );

				$emails = $this->get_dispatch_email_addresses( $vars );
				if ( count( $emails ) ) {
					$deletion->set_headers( $emails );
				}

				$deletion->send_mail();
			}

			/**
			 * Build the Documentation tab for this meta box
			 *
			 * @access protected
			 * @return string the tab interface for the Documentation
			 * @since  0.5.1
			 */
			protected function get_documentation_tab(): string {
				$tabindex = $this::$tab_index;
				$output   = sprintf( '<div id="panel-%1$d" role="tabpanel" class="cornell-governance-tabpanel" tabindex="0" aria-labelledby="tab-%1$d" hidden>', $tabindex );

				$output .= $this->get_documentation_fields();

				$output .= $this->get_revisions_fields();

				$output .= sprintf( '</div><!--/ #panel-%1$d -->', $tabindex );

				$this::$tab_index ++;

				return $output;
			}

			/**
			 * Build and return the Documentation fields for the meta box
			 *
			 * @access protected
			 * @return string the built HTML
			 * @since  0.5.1
			 */
			protected function get_documentation_fields(): string {
				$cap     = Plugin::instance()->get_capability();
				$post_id = Helpers::get_current_post_id();
				$output  = '';

				$ro_notes = \Cornell\Governance\Admin\Meta_Boxes\Fields\Readonly\Notes_Notes::instance();
				$w_notes  = \Cornell\Governance\Admin\Meta_Boxes\Fields\Writable\Notes_Notes::instance();

				$output .= $this->fieldset_open( 'cornell-governance-fieldset cornell-governance-notes-container', __( 'Page Notes', 'cornell/governance' ) );

				$output .= '<p class="cornell-governance-note">' . __( 'A record that captures context, rationale, and stakeholder input behind strategic changes. Used by liaisons to document meetings or decisions with stakeholders.', 'cornell/governance' ) . '</p>';

				if ( current_user_can( $cap ) ) {
					$output .= '<div class="cornell-governance-viewable-field">';
					$output .= $ro_notes->get_input();

					$output .= sprintf( '<div class="field-note timestamp-container">%s</div>', Notes_Timestamp::instance()->get_input() );

					$output .= Edit_Notes_Button::instance()->get_input();
					$output .= '</div>';

					$output .= '<div class="cornell-governance-writable-field">';
					$output .= $w_notes->get_input();

					$output .= wp_nonce_field( 'cornell-governance-page-notes', 'cornell-governance-page-notes' . '-nonce', true, false );
					$output .= sprintf( '<input type="hidden" name="cornell-governance-notes-post-id" value="%d"/>', $post_id );
					$output .= sprintf( '<input type="hidden" name="cornell-governance-action" value="%s"/>', 'notes' );

					$output .= sprintf( '<div class="field-note timestamp-container">%s</div>', Notes_Timestamp::instance()->get_input() );

					$output .= Save_Notes::instance()->get_input();

					$output .= '</div>';

				} else {
					$output .= $ro_notes->get_input();
					$output .= sprintf( '<div class="field-note timestamp-container">%s</div>', Notes_Timestamp::instance()->get_input() );
				}

				$output .= $this->fieldset_close();


				return sprintf( '<div id="%3$s" class="%1$s">%2$s</div><!--/ .cornell-governance-metabox-tab -->', 'cornell-governance-metabox-tab cornell-governance-metabox-tab-notes', $output, 'cornell-governance-page-notes' );
			}

			/**
			 * Build and return the tab for the "Content Updates" area of the meta box
			 *
			 * @access protected
			 * @return string the built HTML
			 * @since  0.5.1
			 */
			protected function get_revisions_tab(): string {
				return '';

				$tabindex = $this::$tab_index;

				$output = sprintf( '<div id="panel-%1$d" role="tabpanel" class="cornell-governance-tabpanel" tabindex="0" aria-labelledby="tab-%1$d" hidden>', $tabindex );



				$output .= sprintf( '</div><!--/ #panel-%1$d -->', $tabindex );

				$this::$tab_index ++;

				return $output;
			}

			/**
			 * Build and return the fields for the Revisions/Content Updates area
			 *
			 * @access protected
			 * @return string the built HTML
			 * @since  0.5.1
			 */
			protected function get_revisions_fields(): string {
				$post_id = Helpers::get_current_post_id();

				$output = '';

				$messages = $this->get_commit_messages( $post_id );
				$output   .= '<blockquote id="cornell-governance-revisions-list-container">';
				$output   .= sprintf( '<h3>%s</h3>', __( 'Previous Content Changes', 'cornell/governance' ) );
				if ( count( $messages ) > 0 ) {
					$output .= '<ol class="commit-messages">';
					$output .= sprintf( '<li>%s</li>', implode( '</li><li>', $messages ) );
					$output .= '</ol>';
				} else {
					$output .= sprintf( '<p>%s</p>', __( 'There have not been any commit messages added to this page, yet.', 'cornell/governance' ) );
				}
				$output .= '</blockquote>';

				return sprintf( '<div id="%3$s" class="%1$s">%2$s</div><!--/ .cornell-governance-metabox-tab -->', 'cornell-governance-metabox-tab cornell-governance-metabox-tab-revisions', $output, 'cornell-governance-page-revisions' );
			}

			/**
			 * Build and return the tab panel that includes a list of Wayback snapshots
			 *
			 * @access protected
			 * @return string the tab panel HTML
			 * @since  0.6.2
			 */
			protected function get_archive_tab(): string {
				$tabindex = $this::$tab_index;

				$output = sprintf( '<div id="panel-%1$d" role="tabpanel" class="cornell-governance-tabpanel" tabindex="0" aria-labelledby="tab-%1$d" hidden>', $tabindex );

				$output .= __( '<p>Below you will find a list of the snapshots that have been captured by archive.org (the Wayback Machine) of this page.</p>', 'cornell/governance' );

				$output .= $this->get_snapshot_list();

				$output .= sprintf( '</div><!--/ #panel-%1$d -->', $tabindex );

				$this::$tab_index ++;

				return $output;
			}

			/**
			 * Build and return the tab panel that includes the "Mark for Deletion" button
			 *
			 * @access protected
			 * @return string the tab panel HTML
			 * @since  0.6.2
			 */
			protected function get_deletion_tab(): string {
				$tabindex = $this::$tab_index;

				$output = sprintf( '<div id="panel-%1$d" role="tabpanel" class="cornell-governance-tabpanel" tabindex="0" aria-labelledby="tab-%1$d" hidden>', $tabindex );

				$output .= $this->get_deletion_fields();

				$output .= sprintf( '</div><!--/ #panel-%1$d -->', $tabindex );

				$this::$tab_index ++;

				return $output;
			}

			/**
			 * Build the fields for the Deletion tab
			 *
			 * @access protected
			 * @return string the HTML fields for the Deletion tab
			 * @since  0.6.2
			 */
			protected function get_deletion_fields(): string {
				$output = $this->fieldset_open( 'cornell-governance-fieldset', __( 'Deletion Request', 'cornell/governance' ) );

				$output .= sprintf( '<input type="hidden" name="cornell-governance-deletion-post-id" value="%d"/>', get_the_ID() );
				$output .= sprintf( '<input type="hidden" name="cornell-governance-action" value="%s"/>', 'delete' );

				$namespace    = $this->get_field_namespace();
				$class        = $namespace . 'Mark_For_Deletion_Instructions';
				$confirmation = $class::instance()->get_input();

				$class        = $namespace . 'Mark_For_Deletion';
				$confirmation .= $class::instance()->get_input();

				$class        = $namespace . 'Deletion_Reason';
				$confirmation .= $class::instance()->get_input();

				$class        = $namespace . 'Deletion_Submit';
				$confirmation .= $class::instance()->get_input();

				$output .= $confirmation;

				$output .= sprintf( '<div class="field-note timestamp-container">%s</div>', Deletion_Timestamp::instance()->get_input() );

				$output .= $this->fieldset_close();

				return sprintf( '<div id="%3$s" class="%1$s">%2$s</div><!--/ .cornell-governance-metabox-tab -->', 'cornell-governance-metabox-tab cornell-governance-metabox-tab-deletion', $output, 'cornell-governance-page-deletion' );
			}

			/**
			 * Retrieve and return the list of Wayback snapshots as an HTML list
			 *
			 * @access protected
			 * @return string the HTML list of snapshots
			 * @since  0.6.2
			 */
			protected function get_snapshot_list(): string {
				$post_id = Helpers::get_current_post_id();
				$url     = get_permalink( $post_id );

				if ( 'production' !== Helpers::get_environment() ) {
					$search = Plugin::instance()->get_archive_settings( 'search' );
					Helpers::log( 'The URL being replaced for Archive queries is: ' . $search, 'info' );
					$replace = Plugin::instance()->get_archive_settings( 'replace' );
					Helpers::log( 'The URL being used to replace this URL for Archive queries is: ' . $replace, 'info' );

					if ( ! empty( $search ) && ! empty( $replace ) ) {
						$url = str_ireplace( $search, $replace, $url );
					}
				}

				Helpers::log( 'The URL being queried against the Wayback Machine is: ' . $url, 'info' );

				return Retrieve::instance()->get_unordered_list( $url );
			}

			/**
			 * Retrieve revision commit messages to display
			 *
			 * @param int $post the post ID for the current post
			 * @param int $limit the maximum number of messages to display
			 *
			 * @access public
			 * @return array an array of commit messages
			 * @since  0.1
			 */
			public function get_commit_messages( int $post, int $limit = 5 ): array {
				$messages = array();

				$all_commits = get_post_meta( $post, Plugin::REVISIONS_META_KEY . '/all', true );
				if ( ! is_array( $all_commits ) ) {
					$all_commits = array();
				}

				$tmp_commits = array();
				foreach ( $all_commits as $key => $commit ) {
					if ( empty( $commit['commit-message'] ) ) {
						continue;
					}

					$tmp_commits[ $key ] = $commit;
				}
				$all_commits = $tmp_commits;

				if ( count( $all_commits ) > $limit && $limit > 0 ) {
					$all_commits = array_slice( $all_commits, ( 0 - $limit ), $limit, true );
				}

				foreach ( $all_commits as $revision_id => $commit ) {
					$messages[ $revision_id ] = Revisions::format_commit_message( $commit );
				}

				return array_reverse( $messages, true );
			}

			/**
			 * Build and return the content of the Help Documentation tab
			 *
			 * @access private
			 * @since  1.0.1
			 * @return string the HTML for the tab
			 */
			private function get_help_documentation_tab(): string {
				$tabindex = $this::$tab_index;

				$output = sprintf( '<div id="panel-%1$d" role="tabpanel" class="cornell-governance-tabpanel" tabindex="0" aria-labelledby="tab-%1$d" hidden>', $tabindex );

				$output .= $this->get_help_documentation_fields();

				$output .= sprintf( '</div><!--/ #panel-%1$d -->', $tabindex );

				$this::$tab_index ++;

				return $output;
			}

			/**
			 * Build and return the Help Documentation content
			 *
			 * @access private
			 * @since  1.0.1
			 * @return string the content
			 */
			private function get_help_documentation_fields(): string {
				$output = $this->fieldset_open( array( 'cornell-governance-fieldset', 'help-documentation-fieldset' ), __( 'Helpful Information', 'cornell-governance' ) );

				$output .= '<div class="help-documentation-content cornell-governance">';
				$output .= apply_filters( 'the_content', apply_filters( 'cornell/governance/help-documentation', Admin::instance()->get_help_documentation() ) );
				$output .= '</div>';

				$output .= $this->fieldset_close();

				return sprintf( '<div id="%3$s" class="%1$s">%2$s</div><!--/ .cornell-governance-metabox-tab -->', 'cornell-governance-metabox-tab cornell-governance-metabox-tab-help-documentation', $output, 'cornell-governance-help-documentation' );
			}
		}
	}
}