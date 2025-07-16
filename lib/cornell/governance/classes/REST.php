<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance {

	use WP_REST_Request;

	if ( ! class_exists( 'REST' ) ) {
		class REST {
			/**
			 * @var REST $instance holds the single instance of this class
			 * @access private
			 */
			private static REST $instance;

			/**
			 * Construct our REST object
			 */
			private function __construct() {
				add_action( 'rest_api_init', array( $this, 'register_rest_fields' ) );
				add_action( 'rest_api_init', array( $this, 'register_rest_endpoint' ) );
				/*$types = Plugin::instance()->get_post_types();
				foreach ( $types as $type ) {
					add_filter( "rest_{$type}_query", array( $this, 'register_rest_query' ), 10, 2 );
				}*/
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  REST
			 * @since   0.1
			 */
			public static function instance(): REST {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Allow-lists the fields that should be shown in a REST response
			 *
			 * @access public
			 * @return void
			 * @since  0.6.2
			 */
			public function register_rest_fields() {
				$types = Plugin::instance()->get_post_types();
				if ( count( $types ) <= 0 ) {
					return;
				}

				register_rest_field(
					$types,
					'cornell/governance/information',
					array(
						'get_callback'    => array( $this, 'get_data' ),
						'update_callback' => null,
						'schema'          => array(
							'type'        => 'array',
							'description' => __( 'Displays governance information about the content', 'cornell/governance' ),
							'context'     => array( 'view' ),
							'items'       => array(
								'goal'              => 'string',
								'purpose'           => 'string',
								'primaryAudience'   => 'string',
								'secondaryAudience' => 'string',
								'secondaryContact'  => 'string',
								'liaison'           => 'string',
								'cycle'             => 'string',
								'complianceStatus'  => 'string',
								'updateMessage'     => 'string',
							)
						),
					)
				);
			}

			/**
			 * Retrieve and organize the REST data
			 *
			 * @param array $post_arr the post information
			 *
			 * @access public
			 * @return array the appropriate data
			 * @since  0.6.2
			 */
			public function get_data( array $post_arr ): array {
				$ID   = $post_arr['id'];
				$meta = get_post_meta( $ID, 'cornell/governance/information', true );
				if ( is_wp_error( $meta ) || ! is_array( $meta ) || count( $meta ) <= 0 ) {
					return array();
				}

				list(
					'legend' => $legend,
					'overdue' => $overdue,
					'due' => $due,
					'next_review' => $next_review
					) = Helpers::get_compliance_status( $meta );

				$latest_update = get_post_meta( $ID, 'cornell/governance/revisions', true );

				$data = array(
					'goal'              => $meta['goals'],
					'purpose'           => $meta['problem'],
					'primaryAudience'   => $meta['primary-audience'],
					'secondaryAudience' => $meta['secondary-audience'],
					'secondaryContact'  => $meta['supervisor'],
					'liaison'           => $meta['liaison'],
					'cycle'             => sprintf( __( 'Every %d months', 'cornell/governance' ), $meta['review-cycle'] ),
					'complianceStatus'  => $legend,
					'updateMessage'     => $latest_update,
				);

				return $data;
			}

			/**
			 * Add appropriate sort and filter information
			 *
			 * @param array $params
			 * @param WP_REST_Request $request
			 *
			 * @access public
			 * @return array the updated parameters
			 * @since  0.6.2
			 */
			public function register_rest_query( array $params, WP_REST_Request $request ): array {
				$orderby = $request->get_param( 'orderby' );
				if ( ! isset( $orderby ) || empty( $orderby ) ) {
					return $params;
				}

				$key = null;

				switch ( $orderby ) {
					case 'primaryAudience' :
						$key = 'primary-audience';
						break;
					case 'secondaryAudience' :
						$key = 'secondary-audience';
						break;
					case 'liaison' :
						$key = 'liaison';
						break;
					case 'cycle' :
						$key = 'review-cycle';
						break;
					case 'complianceStatus' :
						$key = 'compliance-status';
						break;
				}

				return $params;
			}

			/**
			 * Register a custom REST endpoint specifically for Governance info
			 *
			 * @access public
			 * @return void
			 * @since  0.6.2
			 */
			public function register_rest_endpoint() {
				register_rest_route(
					'cornell/governance/v1',
					'/information',
					array(
						'methods'             => \WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_rest_endpoint' ),
						'permission_callback' => '__return_true',
					)
				);

				register_rest_route(
					'cornell/governance/v1',
					'/information/(?P<id>[\d]+)',
					array(
						'methods'             => \WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_rest_endpoint' ),
						'permission_callback' => '__return_true',
					)
				);
			}

			/**
			 * Build and return the data for the custom information endpoint
			 *
			 * @param \WP_REST_Request $request the REST request being made
			 *
			 * @access public
			 * @return \WP_REST_Response|\WP_Error
			 * @since  0.6.2
			 */
			public function get_rest_endpoint( \WP_REST_Request $request ) {
				$types = Plugin::instance()->get_post_types();
				if ( count( $types ) <= 0 ) {
					return new \WP_Error(
						__( 'No Post Types', 'cornell/governance' ),
						__( 'The Governance plugin is not configured for any post types' )
					);
				}

				if ( isset( $request['id'] ) ) {
					return $this->get_single_rest_post( $request['id'] );
				}

				$per_page = isset( $request['params']['per_page'] ) ? $request['params']['per_page'] : 10;
				$page     = isset( $request['params']['page'] ) ? $request['params']['page'] : 1;

				$posts = get_posts(
					array(
						'posts_per_page' => $per_page,
						'paged'          => $page,
						'post_type'      => $types,
						'meta_query'     => array(
							'relation' => 'OR',
							array(
								'key'     => Plugin::INFO_META_KEY,
								'compare' => 'EXISTS',
							),
							array(
								'key'     => Plugin::NOTES_META_KEY,
								'compare' => 'EXISTS',
							),
							array(
								'key'     => Plugin::REVISIONS_META_KEY,
								'compare' => 'EXISTS',
							)
						)
					)
				);

				if ( empty( $posts ) || is_wp_error( $posts ) ) {
					return new \WP_Error(
						__( 'No posts were found', 'cornell/governance' ),
						__( 'No posts with Governance Information could be located', 'cornell/governance' )
					);
				}

				$data = array();

				foreach ( $posts as $post ) {
					$meta = $this->get_post_rest_data( $post );
					if ( empty( $meta ) ) {
						continue;
					}

					$data[ $post->ID ] = $meta;
				}

				if ( empty( $data ) ) {
					return new \WP_Error( __( 'No posts found', 'cornell/governance' ), __( 'We could not locate any posts with governance information', 'cornell/governance' ) );
				}

				$response = new \WP_REST_Response( $data );
				$response->set_status( 200 );

				return $response;
			}

			/**
			 * Build and return a REST response for a specific post
			 *
			 * @param int $id the ID of the post being queried
			 *
			 * @access protected
			 * @return \WP_REST_Response|\WP_Error
			 * @since  0.6.2
			 */
			protected function get_single_rest_post( int $id ) {
				$post = get_post( $id );
				if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) ) {
					return new \WP_Error(
						__( 'No post', 'cornell/governance' ),
						sprintf( __( 'No post could be found matching an ID of %d', 'cornell/governance' ), $id )
					);
				}

				$meta = $this->get_post_rest_data( $post );

				if ( empty( $meta ) ) {
					return new \WP_Error(
						__( 'No meta found', 'cornell/governance' ),
						sprintf( __( 'No governance metadata could be found for %s', 'cornell/governance' ), $post->post_title )
					);
				}

				$response = new \WP_REST_Response( $meta );
				$response->set_status( 200 );

				return $response;
			}

			/**
			 * Retrieve and return an array of Governance data for a specific post
			 *
			 * @param \WP_Post $post the post being queried
			 *
			 * @access protected
			 * @return array the array of metadata
			 * @since  0.6.2
			 */
			protected function get_post_rest_data( \WP_Post $post ): array {
				$meta = get_post_meta( $post->ID, Plugin::INFO_META_KEY, true );
				if ( is_wp_error( $meta ) || ! is_array( $meta ) ) {
					$meta = array();
				}

				list( 'legend' => $meta['compliance-status'] ) = Helpers::get_compliance_status( $meta );
				if ( array_key_exists( 'review-cycle', $meta ) ) {
					$meta['review-cycle'] = array(
						'cycle' => (int) $meta['review-cycle'],
						'text' => sprintf( __( 'Every %d months', 'cornell/governance' ), $meta['review-cycle'] ),
					);
				}

				if ( array_key_exists( 'last-review', $meta ) && is_numeric( $meta['last-review'] ) ) {
					$tmp                 = array(
						'timestamp' => $meta['last-review'],
						'date'      => date( 'c', $meta['last-review'] ),
					);
					$meta['last-review'] = $tmp;
				}

				if ( array_key_exists( 'initial-setup', $meta ) && is_array( $meta['initial-setup'] ) ) {
					$tmp = array(
						'timestamp' => $meta['initial-setup']['time'],
						'date'      => date( 'c', $meta['initial-setup']['time'] ),
						'user'      => array(
							'id'    => $meta['initial-setup']['user'],
							'email' => get_user_by( 'id', $meta['initial-setup']['user'] )->user_email,
						),
					);

					$meta['initial-setup'] = $tmp;
				}

				if ( array_key_exists( 'supervisor', $meta ) ) {
					$meta['secondary-contact'] = $meta['supervisor'];
					unset( $meta['supervisor'] );
				}

				if ( array_key_exists( 'primary-audience', $meta ) ) {
					$meta['primary-audience'] = $this->get_audience_term( $meta['primary-audience'] );
				}
				if ( array_key_exists( 'secondary-audience', $meta ) ) {
					$meta['secondary-audience'] = $this->get_audience_term( $meta['secondary-audience'] );
				}

				$notes     = get_post_meta( $post->ID, 'cornell/governance/notes', true );
				$revisions = get_post_meta( $post->ID, 'cornell/governance/revisions/all', true );

				if ( is_array( $notes ) && count( $notes ) > 0 ) {
					$meta['notes'] = $notes;

					$Parsedown = new \ParsedownExtra();
					$value = $Parsedown->text($meta['notes']['notes']);

					$meta['notes']['rendered'] = $value;
				}

				if ( is_array( $revisions ) && count( $revisions ) > 0 ) {
					$rev = array();
					foreach ( $revisions as $key => $revision ) {
						$rev[ $key ] = array(
							'revision-id' => $key,
							'message'     => $revision['commit-message'],
							'author'      => array(
								'id' => $revision['editor'],
								'email' => get_user_by( 'id', (int) $revision['editor'] )->user_email,
							),
							'timestamp'   => $revision['timestamp'],
							'date'        => date( 'Y-m-d H:i:s', $revision['timestamp'] ),
						);
					}

					$meta['revisions'] = $rev;
				}

				if ( ! empty( $meta ) ) {
					$temp = array( 'post-title' => $post->post_title );
					$meta = array_merge( $temp, $meta );
				}

				return $meta;
			}

			/**
			 * Retrieve information about the Audience taxonomy term being included
			 *
			 * @param string $slug the term slug being queried
			 *
			 * @access protected
			 * @return \WP_Term|\WP_Error the term object or an error if term failed
			 * @since  0.6.2
			 */
			protected function get_audience_term( string $slug ) {
				$term = get_term_by( 'slug', $slug, Audience::HANDLE );

				if ( false === $term ) {
					return new \WP_Error(
						__( 'No term found', 'cornell/governance' ),
						sprintf( __( 'The term with a slug of %s could not be found.', 'cornell/governance' ), $slug )
					);
				}

				return $term;
			}
		}
	}
}