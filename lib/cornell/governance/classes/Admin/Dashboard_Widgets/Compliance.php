<?php
declare( strict_types=1 );

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Dashboard_Widgets {

	use Cornell\Governance\Admin\Submenus\Reports\Due_For_Review;
	use Cornell\Governance\Admin\Submenus\Reports\Non_Compliant;
	use Cornell\Governance\Plugin;

	if ( ! class_exists( '\Cornell\Governance\Admin\Dashboard_Widgets\Compliance' ) ) {
		class Compliance extends Base {
			/**
			 * @var Compliance $instance holds the single instance of this class
			 * @access private
			 */
			protected static Compliance $instance;

			/**
			 * Construct our Compliance object
			 */
			public function __construct() {
				if ( ! current_user_can( 'edit_pages' ) ) {
					return;
				}

				parent::__construct( array(
					'id'       => 'cornell-governance-compliance-widget',
					'title'    => esc_html( __( 'Compliance Status', 'cornell-governance' ) ),
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
				add_filter( 'cornell/governance/reports/current-user', function () {
					return get_current_user_id();
				} );
				Non_Compliant::instance()->display();
				Due_For_Review::instance()->display();
				printf( '<p><a href="%s" class="large">%s</a></p>',
					esc_url( admin_url( 'admin.php?page=cornell-governance-steward-dashboard' ) ),
					esc_html( __( 'View your full Steward Page Report', 'cornell-governance' ) )
				);
				if ( current_user_can( Plugin::instance()->get_capability() ) ) {
					printf( '<p><a href="%s" class="large">%s</a></p>',
						esc_url( admin_url( 'admin.php?page=cornell-governance-liaison-dashboard' ) ),
						esc_html( __( 'View your full Liaison Page Report', 'cornell-governance' ) )
					);
				}
			}
		}
	}
}