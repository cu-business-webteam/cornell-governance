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
			public static function log( string $message, string $level='debug' ): void {
				if ( ! defined( 'CORNELL_DEBUG' ) || false === CORNELL_DEBUG ) {
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
				$gmt = new \DateTimeZone('UTC');
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

				$reviewed        = \DateTime::createFromFormat( 'U', $last_reviewed );
				$compliance_time = get_option( 'cornell-governance-initial-prompt-time', 60 );
				$interval        = new \DateInterval( 'P' . $compliance_time . 'D' );

				$next = $reviewed->add( $interval );

				switch ( $cycle ) {
					case 3 :
						/* End of January - we test based on Feb. 1 */
						$test           = \DateTime::createFromFormat( 'U', strtotime( 'February 1', $last_reviewed ), $timezone );

						$cycle_interval = new \DateInterval( 'P3M' );

						while ( $test < $next ) {
							$test->add( $cycle_interval );
						}

						$m1d = new \DateInterval( 'P1D' );
						$test->sub( $m1d );

						$next_review = $test->getTimestamp();

						break;
					case 6 :
						/* End of May - we use June 1 for calculation */
						$test           = \DateTime::createFromFormat( 'U', strtotime( 'June 1', $last_reviewed ), $timezone );

						$cycle_interval = new \DateInterval( 'P6M' );

						$m1d = new \DateInterval( 'P1D' );
						$test->sub( $m1d );

						$next_review = $test->getTimestamp();

						while ( $test < $next ) {
							$test->add( $cycle_interval );
						}

						$next_review = $test->getTimestamp();

						break;
					default :
						/* June 30 - we use July 1 for testing purposes */
						$test           = \DateTime::createFromFormat( 'U', strtotime( 'July 1', $last_reviewed ), $timezone );

						$cycle_interval = new \DateInterval( 'P1Y' );

						while ( $test < $next ) {
							$test->add( $cycle_interval );
						}

						$m1d = new \DateInterval( 'P1D' );
						$test->sub( $m1d );

						$next_review = $test->getTimestamp();

						break;
				}

				return $next_review;
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
			 * @since  0.1
			 * @return mixed the filtered content
			 */
			public static function ArrayCleaner($input) {
				foreach ($input as &$value) {
					if (is_array($value)) {
						$value = self::ArrayCleaner($value);
					}
				}
				return array_filter($input);
			}

			/**
			 * Determine which environment we are currently in
			 *
			 * @access public
			 * @since  0.4.1
			 * @return string environment handle
			 */
			public static function get_environment(): string {
				if ( getenv('WP_ENVIRONMENT_TYPE') !== false ) {
					return getenv( 'WP_ENVIRONMENT_TYPE' );
				} else {
					return 'unknown';
				}
			}

			/**
			 * Retrieve and return the Edit Post link for logged out users
			 *
			 * @param int|\WP_Post $post
			 * @param string $context
			 *
			 * @access public
			 * @since  0.4.7
			 * @return string the URL to edit a post
			 */
			public static function get_edit_post_link( $post = 0, $context = 'display' ) {
				$post = get_post( $post );

				if ( ! $post ) {
					return;
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
					return;
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
				 * @since 2.3.0
				 *
				 * @param string $link    The edit link.
				 * @param int    $post_id Post ID.
				 * @param string $context The link context. If set to 'display' then ampersands
				 *                        are encoded.
				 */
				return apply_filters( 'get_edit_post_link', $link, $post->ID, $context );
			}
		}
	}
}
