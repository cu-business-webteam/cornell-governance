<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Cornell\Governance {

	use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
	use YahnisElsts\PluginUpdateChecker\v5p5\Vcs\PluginUpdateChecker;
	use YahnisElsts\PluginUpdateChecker\v5p5\Vcs\GitLabApi;

	if ( ! class_exists( '\Cornell\Governance\Updates' ) ) {
		class Updates {
			/**
			 * @var Updates $instance holds the single instance of this class
			 * @access private
			 */
			private static Updates $instance;
			/**
			 * @var array $repo holds the details for the Git update repo
			 * @access private
			 */
			private array $repo = array();

			private string $plugin_slug = 'cornell-governance';
			private array $files_to_preserve = array();

			/**
			 * Creates the Plugin object
			 *
			 * @access private
			 * @since  0.1
			 */
			private function __construct() {
				$this->set_initial_variables();
				$this->init_update_check();

				add_filter( 'upgrader_pre_install', array( $this, 'preserveFileBeforeUpdate' ), 10, 2 );
				add_filter( 'upgrader_post_install', array( $this, 'restoreFileAfterUpdate' ), 10, 3 );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Updates
			 * @since   0.1
			 */
			public static function instance(): Updates {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Set initial variable values
			 *
			 * @access private
			 * @return void
			 * @since  0.1
			 */
			private function set_initial_variables() {
				$this->repo = array_fill_keys( array(
					'url',
					'slug',
					'branch',
					'consumer-key',
					'secret',
					'token',
					'custom-gitlab'
				), false );

				if ( ! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_URL' ) ) ) {
					$this->repo['url'] = Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_URL' );
				}

				if ( ! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_SLUG' ) ) ) {
					$this->repo['slug'] = Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_SLUG' );
				}

				if ( ! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_BRANCH' ) ) ) {
					$this->repo['branch'] = Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_BRANCH' );
				}

				if ( ! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_CONSUMER_KEY' ) ) ) {
					$this->repo['consumer-key'] = Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_CONSUMER_KEY' );
				}

				if ( ! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_CONSUMER_SECRET' ) ) ) {
					$this->repo['secret'] = Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_CONSUMER_SECRET' );
				}

				if ( ! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_AUTH_TOKEN' ) ) ) {
					$this->repo['token'] = Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_AUTH_TOKEN' );
				}

				if ( ! empty( Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_IS_CUSTOM_GITLAB' ) ) ) {
					$this->repo['custom-gitlab'] = Config::instance()->get_var( 'CORNELL_GOVERNANCE_REPO_IS_CUSTOM_GITLAB' );
				}

				Helpers::log( 'Our environment variables look like: ' . print_r( $this->repo, true ) );

				$this->plugin_slug      = 'cornell-governance';
				$this->files_to_preserve = array(
					Helpers::plugins_path( '/.env' ) => WP_CONTENT_DIR . '/cornell-governance.env',
					Helpers::plugins_path( '/cornell-governance-config.php' ) => WP_CONTENT_DIR . '/cornell-governance.config.php',
				);
			}

			/**
			 * Instantiate the update checker
			 *
			 * @access private
			 * @return void
			 * @since  0.5.3
			 */
			private function init_update_check() {
				if ( true === filter_var( $this->repo['custom-gitlab'], FILTER_VALIDATE_BOOLEAN ) ) {
					Helpers::log( 'We appear to be using a custom GitLab repo' );
					$myUpdateChecker = new PluginUpdateChecker(
						new GitLabApi( $this->repo['url'] ),
						Helpers::plugins_path( '/cornell-governance.php' ),
						$this->repo['slug']
					);
				} else if ( ! empty( $this->repo['url'] ) ) {
					Helpers::log( 'Setting up a default git repo' );
					$myUpdateChecker = PucFactory::buildUpdateChecker(
						$this->repo['url'],
						Helpers::plugins_path( '/cornell-governance.php' ),
						$this->repo['slug']
					);
				} else {
					return;
				}

				if ( false !== $this->repo['consumer-key'] && false !== $this->repo['secret'] ) {
					//Optional: If you're using a private repository, create an OAuth consumer
					//and set the authentication credentials like this:
					//Note: For now you need to check "This is a private consumer" when
					//creating the consumer to work around #134:
					// https://github.com/YahnisElsts/plugin-update-checker/issues/134
					$myUpdateChecker->setAuthentication( array(
						'consumer_key'    => $this->repo['consumer-key'],
						'consumer_secret' => $this->repo['secret'],
					) );
				} else if ( false !== $this->repo['token'] ) {
					$myUpdateChecker->setAuthentication( $this->repo['token'] );
				}

				//Optional: Set the branch that contains the stable release.
				$myUpdateChecker->setBranch( $this->repo['branch'] );
			}

			public function preserveFileBeforeUpdate( $response, $hook_extra ) {
				if ( isset( $hook_extra['plugin'] ) && $hook_extra['plugin'] === $this->plugin_slug ) {
					foreach ( $this->files_to_preserve as $preserve => $tmp ) {
						if ( file_exists( $preserve ) ) {
							if ( is_dir( $preserve ) ) {
								return $this->preserveFolderBeforeUpdate( $response, $hook_extra, $preserve, $tmp );
							}

							copy( $preserve , $tmp );
						}
					}
				}

				return $response;
			}

			public function restoreFileAfterUpdate( $response, $hook_extra, $result ) {
				if ( isset( $hook_extra['plugin'] ) && $hook_extra['plugin'] === $this->plugin_slug ) {
					foreach ( $this->files_to_preserve as $preserve => $tmp ) {
						if ( file_exists( $tmp ) ) {
							if ( is_dir( $tmp ) ) {
								return $this->restoreFolderAfterUpdate( $response, $hook_extra, $result, $preserve, $tmp );
							}

							copy( $tmp, $preserve );
							unlink( $tmp );
						}
					}
				}

				return $response;
			}

			public function preserveFolderBeforeUpdate( $response, $hook_extra, $file_to_preserve, $temp_location ) {
				if ( isset( $hook_extra['plugin'] ) && $hook_extra['plugin'] === $this->plugin_slug ) {
					if ( file_exists( $file_to_preserve ) ) {
						if ( ! file_exists( $temp_location ) ) {
							mkdir( $temp_location, 0755, true );
						}
						$this->recurseCopy( $file_to_preserve, $temp_location );
					}
				}

				return $response;
			}

			public function restoreFolderAfterUpdate( $response, $hook_extra, $result, $file_to_preserve, $temp_location ) {
				if ( isset( $hook_extra['plugin'] ) && $hook_extra['plugin'] === $this->plugin_slug ) {
					if ( file_exists( $temp_location ) ) {
						$this->recurseCopy( $temp_location, $file_to_preserve );
						$this->deleteFolder( $temp_location );
					}
				}

				return $response;
			}

			private function recurseCopy( $src, $dst ) {
				$dir = opendir( $src );
				@mkdir( $dst );
				while ( ( $file = readdir( $dir ) ) !== false ) {
					if ( $file !== '.' && $file !== '..' ) {
						if ( is_dir( $src . '/' . $file ) ) {
							$this->recurseCopy( $src . '/' . $file, $dst . '/' . $file );
						} else {
							copy( $src . '/' . $file, $dst . '/' . $file );
						}
					}
				}
				closedir( $dir );
			}

			private function deleteFolder( $folder ) {
				if ( ! is_dir( $folder ) ) {
					return;
				}
				$files = array_diff( scandir( $folder ), [ '.', '..' ] );
				foreach ( $files as $file ) {
					$path = $folder . '/' . $file;
					if ( is_dir( $path ) ) {
						$this->deleteFolder( $path );
					} else {
						unlink( $path );
					}
				}
				rmdir( $folder );
			}
		}
	}
}