<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Submenus {

	use Cornell\Governance\Emails;
	use Cornell\Governance\Helpers;
	use Cornell\Governance\Plugin;

	class Reports extends Base {
		/**
		 * @var Reports $instance holds the single instance of this class
		 * @access private
		 */
		private static Reports $instance;
		/**
		 * @var array $chartConfig holds all of the localized script data for charts on this page
		 * @access public
		 */
		public array $chartConfig;
		/**
		 * @var array $allData holds all of the postmeta data that might be used in chart reports
		 * @access protected
		 */
		protected array $allData;
		/**
		 * @var int $user if this info should be limited to a specific user, that will be stored here
		 * @access protected
		 */
		protected int $user;

		/**
		 * Creates the Reports object
		 *
		 * @access private
		 * @since  0.1
		 */
		function __construct() {
			if ( ! is_admin() ) {
				return;
			}

			$this->allData = array();

			parent::__construct( array(
				'title'       => __( 'Cornell Governance: Page Reports', 'cornell/governance' ),
				'menu_name'   => __( 'Page Reports', 'cornell/governance' ),
				'slug'        => 'cornell-governance-reports',
				'description' => __( 'A series of reports on various aspects of governance throughout the site.', 'cornell/governance' ),
			) );

			$this->chartConfig = array();
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @return  Reports
		 * @since   0.1
		 */
		public static function instance(): Reports {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Set the data array
		 *
		 * @access protected
		 * @return void
		 * @since  0.1
		 */
		protected function gather_data() {
			$this->user = apply_filters( 'cornell/governance/reports/current-user', 0 );

			global $wpdb;

			$ids = false;

			if ( ! empty( $this->user ) ) {
				$types = Plugin::instance()->get_post_types();
				$types = array_map( function ( $v ) {
					return "'" . esc_sql( $v ) . "'";
				}, $types );
				$types = implode( ',', $types );
				$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ({$types}) AND post_author=%d", $this->user );

				$ids = $wpdb->get_col( $query );

				if ( is_array( $ids ) ) {
					$ids = array_filter( $ids, 'is_numeric' );
				}
			}

			$q = $wpdb->prepare( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key=%s", 'cornell/governance/information' );
			if ( false !== $ids && ! is_wp_error( $ids ) ) {
				if ( empty( $ids ) ) {
					/* The current user has no content */
					$this->allData = array();

					return;
				}

				$q .= " AND post_id IN (" . implode( ',', $ids ) . ")";
			}

			$results = $wpdb->get_results( $q );
			if ( is_wp_error( $results ) ) {
				$this->allData = array();

				return;
			}

			foreach ( $results as $result ) {
				$result->meta_value = maybe_unserialize( $result->meta_value );

				foreach ( $result->meta_value as $key => $value ) {
					if ( ! array_key_exists( $key, $this->allData ) ) {
						$this->allData[ $key ] = array();
					}

					$this->allData[ $key ][ $result->post_id ] = $value;
				}
			}
		}

		/**
		 * Retrieve and return a specific item from the list of all data
		 *
		 * @param string $key the key to be retrieved
		 *
		 * @access public
		 * @return array the array of appropriate data
		 * @since  0.1
		 */
		public function get_var( string $key ): array {
			if ( empty( $this->allData ) ) {
				$this->gather_data();
			}

			if ( 'all' === $key ) {
				return $this->allData;
			}

			if ( ! array_key_exists( $key, $this->allData ) ) {
				return array();
			}

			return $this->allData[ $key ];
		}

		/**
		 * Outputs the content of the Page List
		 *
		 * @access protected
		 * @return void
		 * @since  0.1
		 */
		protected function display() {
			printf( '<div class="wrap"><h2>%s</h2><div class="cornell-governance-data-charts">', $this->title );
			$namespace = __NAMESPACE__;
			foreach ( array(
				'Review_Cycle',
				'Primary_Audience',
				'Non_Compliant',
				'Compliance_Status',
				'Due_For_Review',
				'Liaison',
				'Stewards',
			) as $o ) {
				$classname = $namespace . '\Reports\\' . $o;
				$classname::instance()->display();
			}

			/*print( '<div class="governance-chart">' );
			$emails_test = Emails::instance();
			$emails_test->send_messages();
			$emails_test->list_recipients();
			print( '</div>' );*/

			print( '</div></div>' );

			add_action( 'admin_footer', array( $this, 'localize_script' ) );
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

			$this->table = new Tables\Page_List_Table();
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

		/**
		 * Output the localized script data
		 *
		 * @access public
		 * @return void
		 * @since  0.1
		 */
		public function localize_script() {
			wp_localize_script( 'governance-charts', 'chartConfig', $this->chartConfig );
		}
	}
}