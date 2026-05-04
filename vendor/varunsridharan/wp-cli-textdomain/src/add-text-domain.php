<?php
/**
 * Console application, which adds textdomain argument
 * to all i18n function calls
 *
 * @package wordpress-i18n
 */
error_reporting( E_ALL );

/**
 * Class AddTextdomain
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
class AddTextdomain {
	/**
	 * @var string
	 * @access
	 */
	var $modified_contents = '';

	/**
	 * @var array
	 * @access
	 */
	var $funcs;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->funcs   = array(
			'_',
			'__',
			'_e',
			'_c',
			'_n',
			'_n_noop',
			'_nc',
			'__ngettext',
			'__ngettext_noop',
			'_x',
			'_ex',
			'_nx',
			'_nx_noop',
			'_n_js',
			'_nx_js',
			'esc_attr__',
			'esc_html__',
			'esc_attr_e',
			'esc_html_e',
			'esc_attr_x',
			'esc_html_x',
			'comments_number_link',
		);
		$this->funcs[] = 'translate_nooped_plural';
	}

	/**
	 * Prints CLI usage.
	 */
	public function usage() {
		$usage = "Usage: php add-textdomain.php [-i] <domain> <file>\n\nAdds the string <domain> as a last argument to all i18n function calls in <file>\nand prints the modified php file on standard output.\n\nOptions:\n    -i    Modifies the PHP file in place, instead of printing it to standard output.\n";
		fwrite( STDERR, $usage );
		exit( 1 );
	}

	/**
	 * Adds textdomain to a single file.
	 *
	 * @param string $domain Text domain.
	 * @param string $source_filename Filename with optional path.
	 * @param bool   $inplace True to modifies the PHP file in place. False to print to standard output.
	 *
	 * @see AddTextdomain::process_string()
	 *
	 */
	public function process_file( $domain, $source_filename, $inplace ) {
		$new_source = $this->process_string( $domain, file_get_contents( $source_filename ) );

		if ( $inplace ) {
			$f = fopen( $source_filename, 'w' );
			fwrite( $f, $new_source );
			fclose( $f );
		} else {
			echo $new_source;
		}
	}

	/**
	 * Adds textdomain to a string of PHP.
	 *
	 * Functions calls should be wrapped in opening and closing PHP delimiters as usual.
	 *
	 * @param string $domain Text domain.
	 * @param string $string PHP code to parse.
	 *
	 * @return string Modified source.
	 * @see AddTextdomain::process_tokens()
	 *
	 */
	public function process_string( $domain, $string ) {
		$tokens = token_get_all( $string );
		return $this->process_tokens( $domain, $tokens );
	}

	/**
	 * Adds textdomain to a set of PHP tokens.
	 *
	 * @param string $domain Text domain.
	 * @param array  $tokens PHP tokens. An array of token identifiers. Each individual token identifier is either a
	 *                       single character (i.e.: ;, ., >, !, etc.), or a three element array containing the token
	 *                       index in element 0, the string content of the original token in element 1 and the line
	 *                       number in element 2.
	 *
	 * @return string Modified source.
	 */
	public function process_tokens( $domain, $tokens ) {
		$this->modified_contents = '';
		$domain                  = addslashes( $domain );

		$in_func        = false;
		$args_started   = false;
		$parens_balance = 0;
		$found_domain   = false;

		foreach ( $tokens as $index => $token ) {
			$string_success = false;
			if ( is_array( $token ) ) {
				list( $id, $text ) = $token;
				if ( T_STRING === $id && in_array( $text, $this->funcs, true ) ) {
					$in_func        = true;
					$parens_balance = 0;
					$args_started   = false;
					$found_domain   = false;
				} elseif ( T_CONSTANT_ENCAPSED_STRING === $id && ( "'$domain'" === $text || "\"$domain\"" === $text ) ) {
					if ( $in_func && $args_started ) {
						$found_domain = true;
					}
				}
				$token = $text;
			} elseif ( '(' === $token ) {
				$args_started = true;
				++$parens_balance;
			} elseif ( ')' === $token ) {
				--$parens_balance;
				if ( $in_func && 0 === $parens_balance ) {
					if ( ! $found_domain ) {
						$token = ", '$domain'";
						if ( T_WHITESPACE === $tokens[ $index - 1 ][0] ) {
							$token .= ' '; // Maintain code standards if previously present
							// Remove previous whitespace token to account for it.
							$this->modified_contents = trim( $this->modified_contents );
						}
						$token .= ')';
					}
					$in_func      = false;
					$args_started = false;
					$found_domain = false;
				}
			}
			$this->modified_contents .= $token;
		}

		return $this->modified_contents;
	}

	/**
	 * @param \SplFileInfo $file
	 * @param array        $matchers
	 * @param bool         $base_dir
	 *
	 * @static
	 * @return float|int
	 */
	public static function calculate_match_score( SplFileInfo $file, array $matchers = [], $base_dir = false ) {
		if ( empty( $matchers ) ) {
			return 0;
		}

		if ( in_array( $file->getBasename(), $matchers, true ) ) {
			return 10;
		}

		// Check for more complex paths, e.g. /some/sub/folder.
		$root_relative_path = str_replace( $base_dir, '', $file->getPathname() );

		foreach ( $matchers as $path_or_file ) {
			$pattern    = preg_quote( str_replace( '*', '__wildcard__', $path_or_file ), '/' );
			$pattern    = '(^|/)' . str_replace( '__wildcard__', '(.+)', $pattern );
			$base_score = count( array_filter( explode( '/', $path_or_file ), function ( $component ) {
				return '*' !== $component;
			} ) );            // Base score is the amount of nested directories, discounting wildcards.

			if ( 0 === $base_score ) {
				// If the matcher is simply * it gets a score above the implicit score but below 1.
				$base_score = 0.2;
			}

			// If the matcher contains no wildcards and matches the end of the path.
			if ( false === strpos( $path_or_file, '*' ) && false !== mb_ereg( $pattern . '$', $root_relative_path ) ) {
				return $base_score * 10;
			}

			// If the matcher matches the end of the path or a full directory contained.
			if ( false !== mb_ereg( $pattern . '(/|$)', $root_relative_path ) ) {
				return $base_score;
			}
		}
		return 0;
	}

	/**
	 * @param \SplFileInfo $dir
	 * @param array        $matchers
	 * @param bool         $base_dir
	 *
	 * @static
	 * @return bool
	 */
	public static function contains_matching_children( SplFileInfo $dir, array $matchers = [], $base_dir = false ) {
		if ( empty( $matchers ) ) {
			return false;
		}

		/** @var string $root_relative_path */
		$root_relative_path = str_replace( $base_dir, '', $dir->getPathname() );

		foreach ( $matchers as $path_or_file ) {
			// If the matcher contains no wildcards and the path matches the start of the matcher.
			if ( '' !== $root_relative_path && false === strpos( $path_or_file, '*' ) && 0 === strpos( $path_or_file . '/', $root_relative_path ) ) {
				return true;
			}

			$base = current( explode( '*', $path_or_file ) );

			// If start of the path matches the start of the matcher until the first wildcard.
			// Or the start of the matcher until the first wildcard matches the start of the path.
			if ( ( '' !== $root_relative_path && 0 === strpos( $base, $root_relative_path ) ) || 0 === strpos( $root_relative_path, $base ) ) {
				return true;
			}
		}

		return false;
	}
}

// Run the CLI only if the file wasn't included.
$included_files = get_included_files();
if ( __FILE__ === $included_files[0] ) {
	$adddomain = new AddTextdomain();

	if ( ! isset( $argv[1] ) || ! isset( $argv[2] ) ) {
		$adddomain->usage();
	}

	$inplace = false;
	if ( '-i' === $argv[1] ) {
		$inplace = true;
		if ( ! isset( $argv[3] ) ) {
			$adddomain->usage();
		}
		array_shift( $argv );
	}

	if ( is_dir( $argv[2] ) ) {
		$include  = [];
		$exclude  = [
			'node_modules',
			'.git',
			'.svn',
			'.CVS',
			'.hg',
			'vendor',
			'Gruntfile.js',
			'webpack.config.js',
			'*.min.js',
		];
		$dirs     = new RecursiveDirectoryIterator( $argv[2], RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS );
		$callback = new RecursiveCallbackFilterIterator( $dirs, function ( $file, $key, $iterator ) use ( $include, $exclude, $argv ) {
			/**
			 * @var RecursiveCallbackFilterIterator $iterator
			 * @var SplFileInfo                     $file
			 */

			// If no $include is passed everything gets the weakest possible matching score.
			$inclusion_score = empty( $include ) ? 0.1 : AddTextdomain::calculate_match_score( $file, $include, $argv[2] );
			$exclusion_score = AddTextdomain::calculate_match_score( $file, $exclude, $argv[2] );

			// Always include directories that aren't excluded.
			if ( 0 === $exclusion_score && $iterator->hasChildren() ) {
				return true;
			}

			if ( 0 === $inclusion_score || $exclusion_score > $inclusion_score ) {
				// Always include directories that may have matching children even if they are excluded.
				return $iterator->hasChildren() && AddTextDomain::contains_matching_children( $file, $include, $argv[2] );
			}

			return ( $file->isFile() && in_array( $file->getExtension(), [ 'php' ], true ) );
		} );
		$files    = new RecursiveIteratorIterator( $callback, RecursiveIteratorIterator::CHILD_FIRST );
		foreach ( $files as $file ) {
			if ( 'php' === $file->getExtension() ) {
				$adddomain->process_file( $argv[1], $file->getPathname(), $inplace );
			}
		}
	} else {
		$adddomain->process_file( $argv[1], $argv[2], $inplace );
	}
}
