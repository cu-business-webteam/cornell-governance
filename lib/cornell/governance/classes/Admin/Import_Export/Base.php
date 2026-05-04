<?php


namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Import_Export {

	if ( ! class_exists( '\Cornell\Governance\Admin\Import_Export\Base' ) ) {
		abstract class Base {
			/**
			 * @var array $headers the CSV headers
			 * @access protected
			 */
			protected array $headers = array();

			/**
			 * @var array $data the exported data
			 */
			protected array $data = [];


			/**
			 * Construct our Base object
			 */
			protected function __construct() {
				$this->headers = apply_filters( 'cornell/governance/import-export/headers', array(
					'page_id'            => esc_html( __( 'Page ID', 'cornell-governance' ) ),
					'page_title'         => esc_html( __( 'Page Title', 'cornell-governance' ) ),
					'page_url'           => esc_html( __( 'Page URL', 'cornell-governance' ) ),
					'goals'              => esc_html( __( 'Goals', 'cornell-governance' ) ),
					'problem'            => esc_html( __( 'Problem', 'cornell-governance' ) ),
					'primary-audience'   => esc_html( __( 'Primary Audience', 'cornell-governance' ) ),
					'secondary-audience' => esc_html( __( 'Secondary Audience', 'cornell-governance' ) ),
					'last-reviewed'      => esc_html( __( 'Last Reviewed', 'cornell-governance' ) ),
					'review-cycle'       => esc_html( __( 'Review Cycle', 'cornell-governance' ) ),
					'tasks'              => esc_html( __( 'Tasks', 'cornell-governance' ) ),
					'steward'            => esc_html( __( 'Steward', 'cornell-governance' ) ),
					'steward_email'      => esc_html( __( 'Steward Email', 'cornell-governance' ) ),
					'steward_username'   => esc_html( __( 'Steward Username', 'cornell-governance' ) ),
					'supervisor'         => esc_html( __( 'Secondary Contact', 'cornell-governance' ) ),
					'liaison'            => esc_html( __( 'Liaison', 'cornell-governance' ) ),
					'notes'              => esc_html( __( 'Notes', 'cornell-governance' ) ),
				) );
			}
		}
	}
}