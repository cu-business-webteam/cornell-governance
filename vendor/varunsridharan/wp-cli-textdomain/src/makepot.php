<?php

$file = ( isset( $argv[1] ) ) ? $argv[1] : false;
$file = json_decode( file_get_contents( $file ), true );

if ( is_array( $file ) ) {
	$src  = false;
	$dist = false;
	$cmd  = '';

	foreach ( $file as $key => $data ) {
		if ( in_array( $key, array( 'src', 'source', 'plugin' ), true ) ) {
			$src = $data;
		} elseif ( in_array( $key, array( 'dist', 'destination', 'pot_location' ), true ) ) {
			$dist = $data;
		} else {
			if ( is_array( $data ) ) {
				$data = addslashes( json_encode( $data ) );
			}
			$cmd .= ' --' . $key . '="' . $data . '" ';
		}
	}
	$cmd = 'wp-cli i18n make-pot ' . $src . ' ' . $dist . ' ' . $cmd;
	$out = '';
	exec( $cmd, $out );
	echo implode( PHP_EOL, $out );
	echo PHP_EOL;
	if ( isset( $argv[2] ) ) {
		echo PHP_EOL . PHP_EOL;
		echo 'CMD Used : ';
		echo $cmd;
	}
} else {
	echo 'Error : Sytax Error In JSON File Or Its Empty';
}
