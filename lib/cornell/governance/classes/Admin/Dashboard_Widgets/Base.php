<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance\Admin\Dashboard_Widgets {
	if ( ! class_exists( 'Base' ) ) {
		abstract class Base {
			/**
			 * @var string $id the HTML ID for this widget
			 */
			protected string $id;
			/**
			 * @var string $title the plain-language heading for the widget
			 */
			protected string $title;
			/**
			 * @var string $context The context within the screen where the box should display.
			 *      Accepts 'normal', 'side', 'column3', or 'column4'. Default 'normal'.
			 *      Default: 'normal'
			 */
			protected string $context;
			/**
			 * @var string $priority The priority within the context where the box should show.
			 *      Accepts 'high', 'core', 'default', or 'low'. Default 'core'.
			 *      Default: 'core'
			 */
			protected string $priority;

			/**
			 * Construct our Dashboard_Widget object
			 *
			 * @param array $atts the input attributes
			 */
			public function __construct( array $atts = array() ) {
				$this->context  = 'normal';
				$this->priority = 'core';

				foreach ( array( 'id', 'title', 'context', 'priority' ) as $k ) {
					if ( array_key_exists( $k, $atts ) ) {
						$this->{$k} = $atts[ $k ];
					}
				}

				wp_add_dashboard_widget(
					$this->id,
					$this->title,
					array( $this, 'do_widget' ),
					null,
					null,
					$this->context,
					$this->priority
				);
			}

			/**
			 * Output the content of the dashboard widget
			 *
			 * @access public
			 * @return void
			 * @since  2023.05
			 */
			abstract public function do_widget();
		}
	}
}