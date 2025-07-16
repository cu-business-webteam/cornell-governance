<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance {

	use Cornell\Governance\Admin\Fields\Initial_Prompt;

	if ( ! class_exists( 'Helpers' ) ) {
		final class Helpers {
			/**
			 * Custom logging function that can be short-circuited
			 *
			 * @param string $message the text to output to the log
			 * @param string $level one of "debug", "warning" or "error"
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public static function log( string $message, string $level = 'debug' ): void {
				if ( empty( Config::instance()->get_var( 'CORNELL_DEBUG' ) ) || false === Config::instance()->get_var( 'CORNELL_DEBUG' ) ) {
					return;
				}

				$intro = '[Cornell Governance ' . ucfirst( $level ) . ']: ';

				if ( class_exists( '\QM' ) ) {
					do_action( 'qm/' . $level, $intro . $message );
				} else {
					error_log( $intro . $message );
				}
			}

			/**
			 * Retrieve a URL relative to the root of this plugin
			 *
			 * @param string $path the path to append to the root plugin path
			 *
			 * @access public
			 * @return string the full URL to the provided path
			 * @since  0.1
			 */
			public static function plugins_url( string $path ): string {
				return plugins_url( $path, dirname( __FILE__, 4 ) );
			}

			/**
			 * Retrieve a path relative to the root of this plugin
			 *
			 * @param string $path the path to append to the root plugin path
			 *
			 * @access public
			 * @return string the full path to the provided path
			 * @since  0.1
			 */
			public static function plugins_path( string $path ): string {
				$plugin_path = self::plugin_dir_path();

				if ( str_starts_with( $path, '/' ) ) {
					$plugin_path = untrailingslashit( $plugin_path );
				} else {
					$plugin_path = trailingslashit( $plugin_path );
				}

				return $plugin_path . $path;
			}

			/**
			 * Retrieve and return the root path of this plugin
			 *
			 * @access public
			 * @return string the absolute path to the root of this plugin
			 * @since  0.1
			 */
			public static function plugin_dir_path(): string {
				return plugin_dir_path( dirname( __FILE__, 4 ) );
			}

			/**
			 * Retrieve and return the root URL of this plugin
			 *
			 * @access public
			 * @return string the absolute URL
			 * @since  0.1
			 */
			public static function plugin_dir_url(): string {
				return plugin_dir_url( dirname( __FILE__, 4 ) );
			}

			/**
			 * Attempt to determine whether the Block Editor is being used
			 *
			 * @access public
			 * @return bool whether the block editor is being used
			 * @since  0.1
			 */
			public static function is_block_editor_active(): bool {
				// Gutenberg plugin is installed and activated.
				$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

				// Block editor since 5.0.
				$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

				if ( ! $gutenberg && ! $block_editor ) {
					return false;
				}

				if ( self::is_classic_editor_plugin_active() ) {
					$editor_option       = get_option( 'classic-editor-replace' );
					$block_editor_active = array( 'no-replace', 'block' );

					return in_array( $editor_option, $block_editor_active, true );
				}

				return true;
			}

			/**
			 * Determine whether the Classic Editor plugin is active
			 *
			 * @access protected
			 * @return bool whether the plugin is active
			 * @since  0.1
			 */
			protected static function is_classic_editor_plugin_active(): bool {
				return self::is_plugin_active( 'classic-editor/classic-editor.php' );
			}

			/**
			 * Determine whether a plugin is active on a site or network
			 *
			 * @param string $plugin the plugin slug to check
			 *
			 * @access public
			 * @return bool whether the plugin is active or not
			 * @since  0.1
			 */
			public static function is_plugin_active( string $plugin ): bool {
				if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
					include_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				if ( is_plugin_active( $plugin ) ) {
					return true;
				}

				if ( is_plugin_active_for_network( $plugin ) ) {
					return true;
				}

				return false;
			}

			/**
			 * Sets up a DateTime object based on a specific timestamp
			 *
			 * @param numeric $timestamp the timestamp being formatted
			 *
			 * @access public
			 * @return \DateTime|boolean the formatted date
			 * @since  2023.04
			 */
			public static function get_date_time( $timestamp ) {
				$gmt      = new \DateTimeZone( 'UTC' );
				$timezone = wp_timezone();

				$date = \DateTime::createFromFormat( 'U', $timestamp, $gmt );
				if ( false === $date || is_null( $date ) ) {
					return false;
				}

				$date->setTimezone( $timezone );

				return $date;
			}

			/**
			 * Formats a DateTime object into the WordPress date/time format
			 *
			 * @param \DateTime|boolean|null $date the object being formatted
			 *
			 * @access public
			 * @return string the formatted date/time
			 * @since  2023.04
			 */
			public static function format_date_time( $date = false ): string {
				if ( false === $date || is_null( $date ) ) {
					return '';
				}

				$time_format = get_option( 'date_format' ) . ' \a\t ' . get_option( 'time_format' );

				return $date->format( $time_format );
			}

			/**
			 * Format a timestamp into the WordPress date format
			 *
			 * @param numeric $time the timestamp being formatted
			 *
			 * @access public
			 * @return string the formatted date
			 * @since 2023.04
			 */
			public static function format_date( $time ): string {
				if ( false === $time ) {
					return '';
				}

				return date( get_option( 'date_format' ), $time );
			}

			/**
			 * Calculate the next review date based on the last review date & review cycle
			 *
			 * @param int|null $last_reviewed the timestamp when the page was last reviewed
			 * @param mixed $cycle the value of the review cycle setting
			 *
			 * @access public
			 * @return int the timestamp when the next review is due
			 * @since  2023.04
			 */
			public static function calculate_next_review_date( ?int $last_reviewed, $cycle ): int {
				if ( empty( $last_reviewed ) ) {
					$last_reviewed = strtotime( '-1 year' );
				}

				$timezone = wp_timezone();

				$cycle = intval( $cycle );

				$reviewed        = \DateTime::createFromFormat( 'U', $last_reviewed );
				$compliance_time = get_option( 'cornell-governance-initial-prompt-time', 60 );
				$interval        = new \DateInterval( 'P' . $compliance_time . 'D' );

				$next = $reviewed->add( $interval );

				Helpers::log( 'Last reviewed date appears to be: ' . print_r( $reviewed, true ) );
				Helpers::log( 'Next test date is: ' . print_r( $next, true ) );

				switch ( $cycle ) {
					case 3 :
						/* End of January - we test based on Feb. 1 */
						$test = \DateTime::createFromFormat( 'U', strtotime( 'February 1', $last_reviewed ), $timezone );

						Helpers::log( 'First test in 3 month cycle is: ' . print_r( $test, true ) );

						$cycle_interval = new \DateInterval( 'P3M' );

						while ( $test < $next ) {
							$test->add( $cycle_interval );
							Helpers::log( 'Next test in 3 month cycle is: ' . print_r( $test, true ) );
						}

						$m1d = new \DateInterval( 'PT1S' );
						$test->sub( $m1d );

						Helpers::log( 'Final result looks like: ' . print_r( $test, true ) );

						$next_review = $test->getTimestamp();

						Helpers::log( 'Returning ' . $next_review . ' as the next review timestamp' );

						break;
					case 6 :
						/* End of May - we use June 1 for calculation */
						$test = \DateTime::createFromFormat( 'U', strtotime( 'June 1', $last_reviewed ), $timezone );

						Helpers::log( 'First test in 6 month cycle is: ' . print_r( $test, true ) );

						$cycle_interval = new \DateInterval( 'P6M' );

						while ( $test < $next ) {
							$test->add( $cycle_interval );
							Helpers::log( 'Next test in 6 month cycle is: ' . print_r( $test, true ) );
						}

						$m1d = new \DateInterval( 'PT1S' );
						$test->sub( $m1d );

						Helpers::log( 'Final result looks like: ' . print_r( $test, true ) );

						$next_review = $test->getTimestamp();

						Helpers::log( 'Returning ' . $next_review . ' as the next review timestamp' );

						break;
					default :
						/* June 30 - we use July 1 for testing purposes */
						$test = \DateTime::createFromFormat( 'U', strtotime( 'July 1', $last_reviewed ), $timezone );

						Helpers::log( 'First test in 12 month cycle is: ' . print_r( $test, true ) );

						$cycle_interval = new \DateInterval( 'P1Y' );

						while ( $test < $next ) {
							$test->add( $cycle_interval );
							Helpers::log( 'Next test in 12 month cycle is: ' . print_r( $test, true ) );
						}

						$m1d = new \DateInterval( 'PT1S' );
						$test->sub( $m1d );

						Helpers::log( 'Final result looks like: ' . print_r( $test, true ) );

						$next_review = $test->getTimestamp();

						Helpers::log( 'Returning ' . $next_review . ' as the next review timestamp' );

						break;
				}

				return $next_review;
			}

			/**
			 * Identify and return the compliance status for a specific post
			 *
			 * @param array $meta the metadata for the post
			 *
			 * @access public static
			 * @return array{
			 *      legend: string,
			 *      overdue: bool,
			 *      due: bool,
			 *      next_review: int
			 * } the compliance status, overdue status, due status
			 * @since  0.6.2
			 */
			public static function get_compliance_status( array $meta ): array {
				$legend  = '';
				$overdue = $due = false;

				$last_review = array_key_exists( 'last-review', $meta ) ? $meta['last-review'] : null;
				if ( array_key_exists( 'review-cycle', $meta ) ) {
					$next_review     = Helpers::calculate_next_review_date( $last_review, $meta['review-cycle'] );
					$compliance_time = get_option( sprintf( 'cornell-governance-%s', 'initial-prompt-time' ), 60 );

					$due_date = \DateTime::createFromFormat( 'U', $next_review );
					$now_date = new \DateTime();
					$compare  = new \DateInterval( 'P' . $compliance_time . 'D' );

					$overdue = ( $now_date >= $due_date );
					$due     = ( $now_date->add( $compare ) >= $due_date );

					$legend = $due ? __( 'This page is due for review', 'cornell/governance' ) : __( 'This page is in compliance', 'cornell/governance' );
					if ( $overdue ) {
						$legend = __( 'This page is out of compliance', 'cornell/governance' );
					}
				} else {
					Helpers::log( sprintf( __( 'The page does not appear to have been reviewed. %s', 'cornell/governance' ), print_r( $meta, true ) ), 'alert' );
					$next_review = null;
					$legend      = __( 'Never Reviewed', 'cornell/governance' );
				}

				return array(
					'legend' => $legend,
					'overdue' => $overdue,
					'due' => $due,
					'next_review' => $next_review
				);
			}

			/**
			 * Converts an HSL color value to RGB. Conversion formula
			 * adapted from http://en.wikipedia.org/wiki/HSL_color_space.
			 * Assumes h, s, and l are contained in the set [0, 1] and
			 * returns r, g, and b in the set [0, 255].
			 *
			 * @param   {number}  h       The hue
			 * @param   {number}  s       The saturation
			 * @param   {number}  l       The lightness
			 *
			 * @return  {Array}           The RGB representation
			 */
			public static function hue2rgb( $p, $q, $t ) {
				if ( $t < 0 ) {
					$t += 1;
				}
				if ( $t > 1 ) {
					$t -= 1;
				}
				if ( $t < 1 / 6 ) {
					return $p + ( $q - $p ) * 6 * $t;
				}
				if ( $t < 1 / 2 ) {
					return $q;
				}
				if ( $t < 2 / 3 ) {
					return $p + ( $q - $p ) * ( 2 / 3 - $t ) * 6;
				}

				return $p;
			}

			public static function hslToRgb( $h, $s, $l ) {
				if ( $s == 0 ) {
					$r = $l;
					$g = $l;
					$b = $l; // achromatic
				} else {
					$q = $l < 0.5 ? $l * ( 1 + $s ) : $l + $s - $l * $s;
					$p = 2 * $l - $q;
					$r = self::hue2rgb( $p, $q, $h + 1 / 3 );
					$g = self::hue2rgb( $p, $q, $h );
					$b = self::hue2rgb( $p, $q, $h - 1 / 3 );
				}

				return array( round( $r * 255 ), round( $g * 255 ), round( $b * 255 ) );
			}

			public static function randomColor() {
				return self::hslToRgb( ( rand( 0, 359 ) / 360 ), ( rand( 60, 100 ) / 100 ), ( rand( 30, 70 ) / 100 ) );
			}

			public static function get_class_name( $classname ) {
				if ( $pos = strrpos( $classname, '\\' ) ) {
					return substr( $classname, $pos + 1 );
				}

				return $pos;
			}

			/**
			 * Remove all empty elements from an array recursively
			 *
			 * @param mixed $input the item being evaluated
			 *
			 * @access public
			 * @return mixed the filtered content
			 * @since  0.1
			 */
			public static function ArrayCleaner( $input ) {
				foreach ( $input as &$value ) {
					if ( is_array( $value ) ) {
						$value = self::ArrayCleaner( $value );
					}
				}

				return array_filter( $input );
			}

			/**
			 * Determine which environment we are currently in
			 *
			 * @access public
			 * @return string environment handle
			 * @since  0.4.1
			 */
			public static function get_environment(): string {
				if ( getenv( 'WP_ENVIRONMENT_TYPE' ) !== false ) {
					return getenv( 'WP_ENVIRONMENT_TYPE' );
				} else {
					return wp_get_environment_type();
				}
			}

			/**
			 * Retrieve and return the Edit Post link for logged out users
			 *
			 * @param int|\WP_Post $post
			 * @param string $context
			 *
			 * @access public
			 * @return string the URL to edit a post
			 * @since  0.4.7
			 */
			public static function get_edit_post_link( $post = 0, $context = 'display' ) {
				$post = get_post( $post );

				if ( ! $post ) {
					return '';
				}

				if ( 'revision' === $post->post_type ) {
					$action = '';
				} elseif ( 'display' === $context ) {
					$action = '&amp;action=edit';
				} else {
					$action = '&action=edit';
				}

				$post_type_object = get_post_type_object( $post->post_type );

				if ( ! $post_type_object ) {
					return '';
				}

				/*if ( ! current_user_can( 'edit_post', $post->ID ) ) {
					return;
				}*/

				$link = '';

				if ( 'wp_template' === $post->post_type || 'wp_template_part' === $post->post_type ) {
					$slug = urlencode( get_stylesheet() . '//' . $post->post_name );
					$link = admin_url( sprintf( $post_type_object->_edit_link, $post->post_type, $slug ) );
				} elseif ( 'wp_navigation' === $post->post_type ) {
					$link = admin_url( sprintf( $post_type_object->_edit_link, (string) $post->ID ) );
				} elseif ( $post_type_object->_edit_link ) {
					$link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
				}

				/**
				 * Filters the post edit link.
				 *
				 * @param string $link The edit link.
				 * @param int $post_id Post ID.
				 * @param string $context The link context. If set to 'display' then ampersands
				 *                        are encoded.
				 *
				 * @since 2.3.0
				 *
				 */
				return apply_filters( 'get_edit_post_link', $link, $post->ID, $context );
			}

			/**
			 * Determines whether a specific user has a specific capability
			 *
			 * @param int|\WP_User $user - the user being checked
			 * @param string $cap - the capability being tested
			 *
			 * @access public
			 * @return bool whether or not the user has the specified capability
			 * @since 1.0.26
			 */
			public static function user_can( $user = 0, string $cap = '' ): bool {
				if ( empty( $user ) ) {
					return current_user_can( $cap );
				}

				return user_can( $user, $cap );
			}

			/**
			 * Retrieve a sample user that does not have the required capability set by the plugin
			 *
			 * @access public
			 * @return null|\WP_User the sample user
			 * @since  1.0.26
			 */
			public static function get_sample_author(): ?\WP_User {
				$users = get_users( array(
					'capability__in'     => array(
						'edit_posts',
					),
					'capability__not_in' => Plugin::instance()->get_capability(),
					'number'             => 1
				) );

				if ( is_array( $users ) && count( $users ) >= 1 ) {
					return array_shift( $users );
				}

				return null;
			}

			/**
			 * Determine whether the current user can edit governance info
			 *
			 * @param int|\WP_Post $post the post being checked
			 *
			 * @access public
			 * @return bool
			 * @since  0.4.9
			 */
			public static function can_edit_governance( $post = 0 ): bool {
				return current_user_can( Plugin::instance()->get_capability() );
			}

			/**
			 * Determine whether the current user is allowed to review the current page
			 *
			 * @param int|\WP_Post $post the post being checked
			 *
			 * @access protected
			 * @return bool
			 * @since  0.4.9
			 */
			public static function can_review_page( $post = 0 ): bool {
				if ( empty( $post ) ) {
					$post = $GLOBALS['post'];
				}

				if ( empty( $post ) && isset( $_POST['cornell-governance-info-post-id'] ) ) {
					$post = intval( $_POST['cornell-governance-info-post-id'] );
				}

				if ( is_numeric( $post ) ) {
					$post_id = $post;
					$post    = get_post( $post_id );
				}

				$author  = $post->post_author;
				$current = get_current_user_id();

				return ( intval( $author ) === intval( $current ) ) || current_user_can( 'edit_post', $post->ID );
			}

			/**
			 * Determine whether the current user is allowed to edit the current page
			 *
			 * @param int|\WP_Post $post the page being checked
			 *
			 * @access protected
			 * @return bool
			 * @since  0.4.9
			 */
			public static function can_edit_page( $post = 0 ): bool {
				if ( empty( $post ) ) {
					$post = $GLOBALS['post'];
				}

				return current_user_can( 'edit_page', $post );
			}

			/**
			 * Attempt to gracefully retrieve and return the current post ID
			 *
			 * @access public
			 * @since  0.6.2
			 * @return int the post ID
			 */
			public static function get_current_post_id(): int {
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

				return $post_id;
			}

			public static function get_page_status_list(): array {
				return array( 'publish', 'pending', 'future', 'private' );
			}
		}
	}
}
