<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Dashboard_Widgets {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;
	use Cornell\Governance\Admin\Submenus\Reports\Non_Compliant;
	use Cornell\Governance\Helpers;

	if ( ! class_exists( 'Compliance' ) ) {
		class Compliance extends Base {
			/**
			 * @var Compliance $instance holds the single instance of this class
			 * @access private
			 */
			protected static Compliance $instance;

			function __construct() {
				parent::__construct( array(
					'id'       => 'cornell-governance-compliance-widget',
					'title'    => __( 'Compliance Status', 'cornell/governance' ),
					'context'  => 'normal',
					'priority' => 'high',
				) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Compliance
			 * @since   0.1
			 */
			public static function instance(): Compliance {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * @inheritDoc
			 */
			public function do_widget() {
				/**
				 * TODO: Add a list of compliant pages (to show a max of 5 with a link to the full list)
				 */
				add_filter( 'cornell/governance/reports/current-user', function() { return get_current_user_id(); } );
				Non_Compliant::instance()->display();
				Due_For_Review::instance()->display();
			}
		}
	}
}