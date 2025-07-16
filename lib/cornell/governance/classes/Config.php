<?php
/**
 * This file processes the various private configuration variables available to the plugin
 */
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance {
	if ( ! class_exists( 'Config' ) ) {
		class Config {
			/**
			 * @var Config $instance holds the single instance of this class
			 * @access private
			 */
			private static Config $instance;

			/**
			 * @var array $config holds the array of available private config variables
			 * @access private
			 */
			private array $config=array();

			private function __construct() {
				$this->process_config();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Config
			 * @since   0.1
			 */
			public static function instance(): Config {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Checks for the various private configuration variables available to the
			 *      plugin and processes them
			 *
			 * @return void
			 */
			private function process_config(): void {
				$vars = array(
					'CORNELL_GOVERNANCE_REPO_URL'              => 'https://github.com/cu-business-webteam/cornell-governance',
					'CORNELL_GOVERNANCE_REPO_SLUG'             => 'cornell-governance',
					'CORNELL_GOVERNANCE_REPO_BRANCH'           => 'main',
					'CORNELL_GOVERNANCE_REPO_CONSUMER_KEY'     => null,
					'CORNELL_GOVERNANCE_REPO_CONSUMER_SECRET'  => null,
					'CORNELL_GOVERNANCE_REPO_AUTH_TOKEN'       => null,
					'CORNELL_GOVERNANCE_REPO_IS_CUSTOM_GITLAB' => false,
					'CORNELL_DEBUG'                            => false,
					'CORNELL_GOVERNANCE_EMAIL_TO'              => null,
					'CORNELL_GOVERNANCE_EMAIL_CC'              => null,
					'CORNELL_GOVERNANCE_EMAIL_BCC'             => null,
				);

				foreach ( $vars as $var => $default ) {
					if ( array_key_exists( $var, $_ENV ) ) {
						$this->config[ $var ] = $_ENV[ $var ];
					} else if ( defined( $var ) ) {
						$this->config[ $var ] = constant( $var );
					} else {
						$this->config[ $var ] = $default;
					}
				}
			}

			/**
			 * Retrieve a single config var
			 *
			 * @param string $key the key to be retrieved
			 * @param mixed $default the default value to return
			 *
			 * @access public
			 * @since  0.6.2
			 * @return mixed the value of the variable
			 */
			public function get_var( string $key, $default = null ) {
				if ( array_key_exists( $key, $this->config ) ) {
					return $this->config[ $key ];
				} else {
					return $default;
				}
			}
		}
	}
}