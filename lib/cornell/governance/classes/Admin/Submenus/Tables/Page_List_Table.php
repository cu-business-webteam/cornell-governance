<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}

	// Loading table class
	if ( ! class_exists( '\Cornell\Governance\Admin\Submenus\Tables\WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}
}

namespace Cornell\Governance\Admin\Submenus\Tables {

	use Cornell\Governance\Admin\Fields\Post_Types;
	use Cornell\Governance\Admin\Meta_Boxes\Info;
	use Cornell\Governance\Admin\Meta_Boxes\Revisions;
	use Cornell\Governance\Admin\Settings;
	use Cornell\Governance\Helpers;

	class Page_List_Table extends \WP_List_Table {
		/**
		 * @var string $per_page_option the option name for our per_page setting
		 */
		protected string $per_page_option = 'per_page';

		/**
		 * Construct our object
		 *
		 * @param $args
		 */
		function __construct( $args = array() ) {
			parent::__construct( $args );

			$this->per_page_option = str_replace( '-', '_', $this->screen->id ) . '_per_page';

			$option = 'per_page';
			$args   = array(
				'default' => 50,
			);
			add_screen_option( $option, $args );

			add_filter( 'set_screen_option_' . $this->per_page_option, array( $this, 'save_per_page_option' ), 10, 3 );
			set_screen_options();
		}

		/**
		 * Define the table columns
		 */
		function get_columns() {
			return apply_filters(
				'cornell/governance/page-list-table/columns',
				array_merge(
					$this->get_visible_columns(),
					$this->get_hidden_columns()
				)
			);
		}

		/**
		 * Prepare an array of visible columns
		 *
		 * @access public
		 * @return array the list of visible columns
		 * @since  0.1
		 */
		function get_visible_columns(): array {
			return apply_filters( 'cornell/governance/page-list-table/columns/visible', array(
				//'cb'          => '<input type="checkbox"/>',
				'author'      => esc_html( __( 'Author', 'cornell-governance' ) ),
				'title'       => esc_html( __( 'Page Title', 'cornell-governance' ) ),
				'last-review' => esc_html( __( 'Last Reviewed', 'cornell-governance' ) ),
				'next-review' => esc_html( __( 'Next Review Due', 'cornell-governance' ) ),
				'status'      => esc_html( __( 'Status', 'cornell-governance' ) ),
				'modified'    => esc_html( __( 'Modified', 'cornell-governance' ) ),
				'type'        => esc_html( __( 'Post Type', 'cornell-governance' ) ),
			) );
		}

		/**
		 * Prepare an array of the hidden columns
		 *
		 * @access public
		 * @return array the list of hidden columns
		 * @since  0.1
		 */
		function get_hidden_columns(): array {
			return apply_filters( 'cornell/governance/page-list-table/columns/hidden', array(
				'ID'                 => esc_html( __( 'ID', 'cornell-governance' ) ),
				'latest-commit'      => esc_html( __( 'Commit Message', 'cornell-governance' ) ),
				'supervisor'         => esc_html( __( 'Secondary', 'cornell-governance' ) ),
				'liaison'            => esc_html( __( 'Liaison', 'cornell-governance' ) ),
				'primary-audience'   => esc_html( __( 'Primary Audience', 'cornell-governance' ) ),
				'secondary-audience' => esc_html( __( 'Secondary Audience', 'cornell-governance' ) ),
			) );
		}

		/**
		 * Prepare an array of sortable columns
		 *
		 * @access public
		 * @return array the array of sortable columns
		 * @since  0.1
		 */
		function get_sortable_columns(): array {
			return apply_filters( 'cornell/governance/page-list-table/columns/sortable', array(
				'ID'                 => array( 'ID', 'asc' ),
				'author'             => array( 'author', 'asc' ),
				'title'              => array( 'title', 'asc' ),
				'modified'           => array( 'modified', true ),
				'last-review'        => array( 'last-review', 'asc' ),
				'next-review'        => array( 'next-review', true ),
				'status'             => array( 'status', 'asc' ),
				'supervisor'         => array( 'supervisor', 'asc' ),
				'liaison'            => array( 'liaison', 'asc' ),
				'primary-audience'   => array( 'primary-audience', 'asc' ),
				'secondary-audience' => array( 'secondary-audience', 'asc' ),
				'type'               => array( 'type', true ),
			) );
		}

		/**
		 * Bind table with columns, data and all
		 */
		function prepare_items() {
			$columns               = $this->get_columns();
			$hidden                = array_keys( $this->get_hidden_columns() );
			$sortable              = $this->get_sortable_columns();
			/*$this->_column_headers = array( $columns, $hidden, $sortable );*/
			$this->_column_headers = array(
				$this->get_columns(),
				get_hidden_columns( $this->screen ),
				$this->get_sortable_columns(),
			);

			$this->items = $this->get_data();
		}

		/**
		 * Retrieve the appropriate query args for the orderby clause
		 *
		 * @param string $orderby the key on which the data are being sorted
		 *
		 * @access protected
		 * @return array the appropriate query arg
		 * @since  0.1
		 */
		protected function get_orderby( string $orderby ): array {
			switch ( $orderby ) {
				case 'last-review' :
				case 'next-review' :
				case 'latest-commit' :
				case 'supervisor' :
				case 'liaison' :
				case 'primary-audience' :
				case 'secondary-audience' :
					$arg = array(
						'orderby'    => 'meta_value',
						'meta_key'   => $orderby,
						'meta_query' => array(
							array(),
						),
					);
					break;
				default : /* title, author, ID, modified */
					return array( 'orderby' => $orderby );
					break;
			}

			return apply_filters( 'cornell/governance/page-list-table/default-orderby', array( 'orderby' => 'title' ) );
		}

		/**
		 * Return the search filter for this request, if any.
		 *
		 * @return string
		 */
		protected function get_request_search_query(): string {
			return ( ! empty( $_REQUEST['s'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Retrieve the data to be displayed in the table
		 *
		 * @access public
		 * @return array the list of data
		 * @since  0.1
		 */
		function get_data(): array {
			$data = array();

			$per_page     = $this->get_items_per_page( $this->per_page_option, 50 );
			$current_page = $this->get_pagenum();
			$orderby      = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'title';
			$order        = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : false;
			if ( false === $order ) {
				$sortable = $this->get_sortable_columns();
				if ( array_key_exists( $orderby, $sortable ) && is_array( $sortable[ $orderby ] ) && true === $sortable[ $orderby ][1] ) {
					$order = 'desc';
				} else {
					$order = 'asc';
				}
			}

			$args = array(
				'post_type'      => Post_Types::instance()->get_input_value(),
				'posts_per_page' => $per_page,
				'paged'          => $current_page,
				'order'          => strtoupper( $order ),
				'post_status'    => Helpers::get_page_status_list(),
			);

			$s = $this->get_request_search_query();
			if ( ! empty( $s ) ) {
				$args['s'] = $s;
			}

			$natural_sort = false;

			switch ( $orderby ) {
				case 'ID' :
				case 'title' :
				case 'modified' :
				case 'author' :
					$natural_sort = true;
					break;
				default :
					unset( $args['paged'] );
					unset( $args['posts_per_page'] );
					$args['numberposts'] = '-1';
					break;
			}

			$args['meta_query'] = array(
				array(
					'key' => \Cornell\Governance\Plugin::INFO_META_KEY,
					'compare' => 'EXISTS',
				),
			);

			$args = array_merge( $args, $this->get_orderby( $orderby ) );

			$args = apply_filters( 'cornell/governance/page-list-table/query-args', $args );

			$data = $this->do_query( $args );

			if ( false === $natural_sort ) {
				$this->usort( $data, $orderby );

				if ( 'desc' === strtolower( $order ) ) {
					$data = array_reverse( $data, true );
				}

				$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page, true );
			}

			return apply_filters(
				'cornell/governance/page-list-table/data',
				$data
			);
		}

		/**
		 * Run a query and process the information to be returned
		 *
		 * @param array $args the query arguments to be applied
		 *
		 * @return array false on error or an array of the retrieved post information
		 */
		public function do_query( array $args ): array {
			$per_page = $args['posts_per_page'];

			$q = new \WP_Query( $args );

			Helpers::log( print_r( $q, true ), 'info' );

			$data = array();

			global $post;
			if ( $q->have_posts() ) : while ( $q->have_posts() ) : $q->the_post();
				$data[] = $this->prepare_item( $post );
			endwhile; endif;

			Helpers::log( print_r( $data, true ), 'info' );

			$this->set_pagination_args( array(
				'total_items' => $q->found_posts,
				'per_page' => $per_page,
				'total_pages' => ceil( $q->found_posts / $per_page ),
			) );

			return $data;
		}

		/**
		 * Sort the data according to the appropriate key
		 *
		 * @param array &$data the data being sorted
		 * @param string $key the orderby key
		 *
		 * @access protected
		 * @return void
		 * @since  0.1
		 */
		protected function usort( array &$data, string $key ) {
			uasort( $data, function ( $a, $b ) use ( &$key ) {
				switch ( $key ) {
					case 'last-review':
					case 'next-review' :
						if ( $a[ $key ] === $b[ $key ] ) {
							return 0;
						}

						$rt = ( $a[ $key ] < $b[ $key ] ) ? - 1 : 1;
						return $rt;
						break;
					default :
						return strcmp( $a[ $key ], $b[ $key ] );
						break;
				}
			} );
		}

		/**
		 * Prepare the checkbox column
		 *
		 * @param array $item the item being processed
		 *
		 * @access public
		 * @return string the checkbox input
		 * @since  0.1
		 */
		function column_cb( $item ): string {
			return '';
			return sprintf( '<input type="checkbox" name="governance[]" value="%d"/>', $item['ID'] );
		}

		/**
		 * Prepare the individual column data
		 *
		 * @param array $item the item being processed
		 * @param string $column_name the column key
		 *
		 * @access public
		 * @return string the column data to be printed
		 * @since  0.1
		 */
		function column_default( $item, $column_name ): string {
			$actions = array();
			switch ( $column_name ) {
				case 'last-review' :
					if ( current_user_can( 'edit_post', $item['ID'] ) ) {
						$link                 = admin_url( 'admin.php' );
						$link                 = add_query_arg( array( 'page' => 'cornell-governance-page-meta', 'post' => $item['ID'] ), $link );
						$actions['view_meta'] = sprintf( '<a href="%1$s">%2$s</a>', $link, __( 'View Governance Info', 'cornell-governance' ) );
					}
				case 'next-review' :
					$date = Helpers::get_date_time( $item[ $column_name ] );
					if ( false === $date ) {
						return __( 'Not reviewed, yet', 'cornell-governance' ) . $this->row_actions( $actions );
					}

					return Helpers::format_date( $date->format('U') ) . $this->row_actions( $actions );
					break;
				case 'title' :
					$title = $item['title'];
					if ( empty( $title ) ) {
						$title = esc_html( __( '[No Specified Title]', 'cornell-governance' ) );
					}
					$link = get_edit_post_link( $item['ID'] );

					$actions['edit_page'] = sprintf( '<a href="%1$s">%2$s</a>', $link, __( 'Edit', 'cornell-governance' ) );
					$actions['view_page'] = sprintf( '<a href="%1$s">%2$s</a>', get_permalink( $item['ID'] ), __( 'View', 'cornell-governance' ) );

					return sprintf( '<a href="%1$s" target="editor">%2$s</a>%3$s', $link, $title, $this->row_actions( $actions ) );
					break;
				case 'type' :
					$post_type_object = get_post_type_object( $item[ $column_name ] );
					return $post_type_object->labels->singular_name;
					break;
				default :
					return is_null( $item[ $column_name ] ) ? '' : $item[ $column_name ];
					break;
			}
		}

		/**
		 * Prepare an individual item for inclusion in the table
		 *
		 * @param int|\WP_Post $post the post being prepared/queried
		 *
		 * @access protected
		 * @return array the item data
		 * @since  0.1
		 */
		protected function prepare_item( $post ): array {
			if ( is_numeric( $post ) ) {
				$id   = $post;
				$post = get_post( $id );
			}

			$item = array_merge( array_fill_keys( array_keys( $this->get_columns() ), null ), array(
				'ID'          => $post->ID,
				'title'       => $post->post_title,
				'author'      => get_the_author_meta( 'display_name', $post->post_author ),
				'modified'    => $post->post_modified,
				'type'        => $post->post_type,
			) );

			$meta = get_post_meta( $post->ID, Info::instance()->get_meta_key(), true );
			foreach ( $meta as $key => $value ) {
				switch ( $key ) {
					case 'tasks' :
						break;
					default :
						$item[ $key ] = $value;
						break;
				}
			}

			if ( ! array_key_exists( 'last-review', $meta ) ) {
				$meta['last-review'] = 0;
			}

			if ( ! array_key_exists( 'review-cycle', $meta ) ) {
				$status = array( 'next_review' => null, 'overdue' => false, 'due' => false );
			} else {
				$item['next-review'] = Helpers::calculate_next_review_date( $meta['last-review'], $meta['review-cycle'] );

				$status = Helpers::get_compliance_status( $meta );
			}

			$item['status'] = esc_html( __( 'Compliant', 'cornell-governance' ) );
			if ( array_key_exists( 'overdue', $status ) && $status['overdue'] ) {
				$item['status'] = esc_html( __( 'Overdue', 'cornell-governance' ) );
			} else if ( array_key_exists( 'due', $status ) && $status['due'] ) {
				$item['status'] = esc_html( __( 'Due', 'cornell-governance' ) );
			} else if ( ! array_key_exists( 'next_review', $status ) || null === $status['next_review'] ) {
				$tmp = array_merge( array( 'next_review' => null, 'last-review' => null, 'review-cycle' => null ), $meta, $status );
				Helpers::log( esc_html( __( 'It does not appear that this page has been reviewed.', 'cornell-governance' ) ), 'alert' );
				Helpers::log( sprintf( esc_html( __( 'The next review looks like: %s, the last review looks like: %s and the review cycle looks like: %s', 'cornell-governance' ) ), $tmp['next_review'], $tmp['last-review'], $tmp['review-cycle'] ), 'alert' );
				$item['status'] = esc_html( __( 'Never Reviewed', 'cornell-governance' ) );
			}

			$commit = Revisions::instance()->get_latest_commit( $post->ID );
			if ( array_key_exists( 'commit-message', $commit ) ) {
				$item['latest-commit'] = $commit['commit-message'];
			}

			return $item;
		}

		/**
		 * Output a custom phrase when no items are found
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		public function no_items() {
			if ( isset( $_REQUEST['s'] ) ) {
				_e( 'There are no pages that match the specified criteria', 'cornell-governance' );
			} else {
				_e( 'There are no pages to display', 'cornell-governance' );
			}
		}

		/**
		 * Filter the value of the Per Page option before saving
		 *
		 * @param mixed $ignore whether to ignore saving this value or not
		 * @param string $option the name of the option being filtered
		 * @param mixed $value the value being filtered
		 *
		 * @access public
		 * @since  0.6.5
		 * @return mixed the new value
		 */
		public function save_per_page_option( $ignore, string $option, $value ) {
			return intval( $value );
		}
	}
}