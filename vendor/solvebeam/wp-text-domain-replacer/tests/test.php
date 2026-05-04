<?php

__( 'Test 1', 'old' );
\__( 'Test 2', 'old' );

test__( 'Test 3', 'old' );

__( 'Test 4', 'older' );

load_plugin_textdomain( 'old', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
load_plugin_textdomain( 'old', false );
load_plugin_textdomain( 'old' );
