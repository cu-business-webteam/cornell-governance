<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Taxonomies {
	if ( ! class_exists( 'Base' ) ) {
		abstract class Base {
			abstract protected function __construct();

			/**
			 * Returns the handle for the taxonomy
			 *
			 * @access protected
			 * @return string
			 * @since  0.1
			 */
			abstract protected function get_handle(): string;

			/**
			 * Returns the array of labels for this taxonomy
			 *
			 * @access protected
			 * @return array the array of labels
			 * @since  0.1
			 */
			abstract protected function get_labels(): array;

			/**
			 * Returns the array of arguments for the taxonomy
			 *
			 * @access protected
			 * @return array the array of arguments
			 * @since  0.1
			 */
			abstract protected function get_args(): array;

			/**
			 * Returns the array of post types to associate with this taxonomy
			 *
			 * @access protected
			 * @return array the array of post type handles
			 * @since  0.1
			 */

			abstract protected function get_post_types(): array;

			/**
			 * Populate other labels that aren't usually populated
			 *
			 * @param array $labels the existing list of labels
			 *
			 * @access protected
			 * @return array the populated list of labels
			 * @since  0.1
			 */
			protected function populate_labels( array $labels ): array {
				$extra = array(
					/* translators: The placeholder is the plural name of the taxonomy */
					'menu_name'                  => sprintf( esc_html__( '%s', 'cornell/governance' ), $labels['name'] ),
					/* translators: The placeholder is the plural name of the taxonomy */
					'all_items'                  => sprintf( esc_html__( 'All %s', 'cornell/governance' ), $labels['name'] ),
					/* translators: The placeholder is the singular name of the taxonomy */
					'edit_item'                  => sprintf( esc_html__( 'Edit %s', 'cornell/governance' ), $labels['singular_name'] ),
					/* translators: The placeholder is the singular name of the taxonomy */
					'view_item'                  => sprintf( esc_html__( 'View %s', 'cornell/governance' ), $labels['singular_name'] ),
					/* translators: The placeholder is the singular name of the taxonomy */
					'update_item'                => sprintf( esc_html__( 'Update %s name', 'cornell/governance' ), $labels['singular_name'] ),
					/* translators: The placeholder is the singular name of the taxonomy */
					'add_new_item'               => sprintf( esc_html__( 'Add new %s', 'cornell/governance' ), $labels['singular_name'] ),
					/* translators: The placeholder is the singular name of the taxonomy */
					'new_item_name'              => sprintf( esc_html__( 'New %s name', 'cornell/governance' ), $labels['singular_name'] ),
					/* translators: The placeholder is the singular name of the taxonomy */
					'parent_item'                => sprintf( esc_html__( 'Parent %s', 'cornell/governance' ), $labels['singular_name'] ),
					/* translators: The placeholder is the singular name of the taxonomy */
					'parent_item_colon'          => sprintf( esc_html__( 'Parent %s:', 'cornell/governance' ), $labels['singular_name'] ),
					/* translators: The placeholder is the plural name of the taxonomy */
					'search_items'               => sprintf( esc_html__( 'Search %s', 'cornell/governance' ), $labels['name'] ),
					/* translators: The placeholder is the plural name of the taxonomy */
					'popular_items'              => sprintf( esc_html__( 'Popular %s', 'cornell/governance' ), $labels['name'] ),
					/* translators: The placeholder is the plural name of the taxonomy */
					'separate_items_with_commas' => sprintf( esc_html__( 'Separate %s with commas', 'cornell/governance' ), $labels['name'] ),
					/* translators: The placeholder is the plural name of the taxonomy */
					'add_or_remove_items'        => sprintf( esc_html__( 'Add or remove %s', 'cornell/governance' ), $labels['name'] ),
					/* translators: The placeholder is the plural name of the taxonomy */
					'choose_from_most_used'      => sprintf( esc_html__( 'Choose from the most used %s', 'cornell/governance' ), $labels['name'] ),
					/* translators: The placeholder is the plural name of the taxonomy */
					'not_found'                  => sprintf( esc_html__( 'No %s found', 'cornell/governance' ), $labels['name'] ),
					/* translators: The placeholder is the plural name of the taxonomy */
					'no_terms'                   => sprintf( esc_html__( 'No %s', 'cornell/governance' ), $labels['name'] ),
					/* translators: The placeholder is the plural name of the taxonomy */
					'items_list_navigation'      => sprintf( esc_html__( '%s list navigation', 'cornell/governance' ), $labels['name'] ),
					/* translators: The placeholder is the plural name of the taxonomy */
					'items_list'                 => sprintf( esc_html__( '%s list', 'cornell/governance' ), $labels['name'] ),
					/* translators: The placeholder is the plural name of the taxonomy */
					'back_to_items'              => sprintf( esc_html__( 'Back to %s', 'cornell/governance' ), $labels['name'] ),
				);

				return array_merge( $extra, $labels );
			}

			/**
			 * Register the new taxonomy
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function register_taxonomy(): void {
				register_taxonomy( $this->get_handle(), $this->get_post_types(), $this->get_args() );
			}
		}
	}
}