<?php


namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Import_Export {

	if ( ! class_exists( 'Base' ) ) {
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
				$this->headers = array(
					'page_id'            => __( 'Page ID', 'cornell/governance' ),
					'page_title'         => __( 'Page Title', 'cornell/governance' ),
					'goals'              => __( 'Goals', 'cornell/governance' ),
					'problem'            => __( 'Problem', 'cornell/governance' ),
					'primary-audience'   => __( 'Primary Audience', 'cornell/governance' ),
					'secondary-audience' => __( 'Secondary Audience', 'cornell/governance' ),
					'last-reviewed'      => __( 'Last Reviewed', 'cornell/governance' ),
					'review-cycle'       => __( 'Review Cycle', 'cornell/governance' ),
					'tasks'              => __( 'Tasks', 'cornell/governance' ),
					'steward'            => __( 'Steward', 'cornell/governance' ),
					'supervisor'         => __( 'Secondary Contact', 'cornell/governance' ),
					'liaison'            => __( 'Liaison', 'cornell/governance' ),
					'notes'              => __( 'Notes', 'cornell/governance' ),
				);
			}
		}
	}
}