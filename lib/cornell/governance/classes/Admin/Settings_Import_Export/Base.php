<?php


namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Settings_Import_Export {

	if ( ! class_exists( 'Base' ) ) {
		abstract class Base {
			/**
			 * @var array $headers the CSV headers
			 * @access protected
			 */
			protected array $headers = array();

			/**
			 * @var array $classes the Settings Field classes
			 * @access protected
			 */
			protected array $classes = array();

			/**
			 * @var array $data the exported data
			 */
			protected array $data = [];


			/**
			 * Construct our Base object
			 */
			protected function __construct() {
				$this->classes = array(
					'Capability',
					'Managing_Office',
					'Post_Types',
					'Default_Tasks',
					/*'Message_Content',*/
					'Mark_For_Deletion',
					'Frontend_Compliance',
					'Archive_Active',
					'Archive_URL_Search',
					'Archive_URL_Replace',
					'Archive_Log_Limit',
					'Change_Form_Active',
					'Change_Form_Link_Text',
					'Change_Form_URL',
					'Change_Form_Props',
					'Email_Active',
					'Initial_Prompt',
					'Secondary_Prompt',
					'Tertiary_Prompt'
				);

				$this->set_headers();
			}

			/**
			 * Set the header array
			 *
			 * @access protected
			 * @since  0.6.2
			 * @return void
			 */
			protected function set_headers() {
				$this->headers = array();
				$namespace = '\Cornell\Governance\Admin\Fields';
				foreach ( $this->classes as $class ) {
					$name = $namespace . '\\' . $class;
					if ( class_exists( $name ) ) {
						$obj = $name::instance();
						$this->headers[$obj->get_field_id()] = $obj->get_field_title();
					}
				}
			}
		}
	}
}