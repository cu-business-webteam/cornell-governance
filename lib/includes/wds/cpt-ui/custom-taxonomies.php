<?php
/* This file is just for reference, and is not actually called anywhere */

function cptui_register_my_taxes() {

	/**
	 * Taxonomy: Audiences.
	 */

	$labels = [
		"name" => esc_html__( "Audiences", "twentytwentythree" ),
		"singular_name" => esc_html__( "Audience", "twentytwentythree" ),
		"menu_name" => esc_html__( "Audiences", "twentytwentythree" ),
		"all_items" => esc_html__( "All Audiences", "twentytwentythree" ),
		"edit_item" => esc_html__( "Edit Audience", "twentytwentythree" ),
		"view_item" => esc_html__( "View Audience", "twentytwentythree" ),
		"update_item" => esc_html__( "Update Audience name", "twentytwentythree" ),
		"add_new_item" => esc_html__( "Add new Audience", "twentytwentythree" ),
		"new_item_name" => esc_html__( "New Audience name", "twentytwentythree" ),
		"parent_item" => esc_html__( "Parent Audience", "twentytwentythree" ),
		"parent_item_colon" => esc_html__( "Parent Audience:", "twentytwentythree" ),
		"search_items" => esc_html__( "Search Audiences", "twentytwentythree" ),
		"popular_items" => esc_html__( "Popular Audiences", "twentytwentythree" ),
		"separate_items_with_commas" => esc_html__( "Separate Audiences with commas", "twentytwentythree" ),
		"add_or_remove_items" => esc_html__( "Add or remove Audiences", "twentytwentythree" ),
		"choose_from_most_used" => esc_html__( "Choose from the most used Audiences", "twentytwentythree" ),
		"not_found" => esc_html__( "No Audiences found", "twentytwentythree" ),
		"no_terms" => esc_html__( "No Audiences", "twentytwentythree" ),
		"items_list_navigation" => esc_html__( "Audiences list navigation", "twentytwentythree" ),
		"items_list" => esc_html__( "Audiences list", "twentytwentythree" ),
		"back_to_items" => esc_html__( "Back to Audiences", "twentytwentythree" ),
		"name_field_description" => esc_html__( "The name is how it appears on your site.", "twentytwentythree" ),
		"parent_field_description" => esc_html__( "Assign a parent term to create a hierarchy. The term Jazz, for example, would be the parent of Bebop and Big Band.", "twentytwentythree" ),
		"slug_field_description" => esc_html__( "The slug is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.", "twentytwentythree" ),
		"desc_field_description" => esc_html__( "The description is not prominent by default; however, some themes may show it.", "twentytwentythree" ),
	];


	$args = [
		"label" => esc_html__( "Audiences", "twentytwentythree" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => false,
		"query_var" => true,
		"rewrite" => false,
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "audience",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "audience", [ "page" ], $args );
}
add_action( 'init', 'cptui_register_my_taxes' );