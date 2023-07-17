<?php

class Memcached_Testing {
	public Memcached $mc;

	function __construct() {
		$mc = new Memcached( 'persistent' );

		// Only add servers and set options if not already available (to keep persistency).
		if ( empty( $mc->getServerList() ) ) {
			$mc->addServers( [ [ 'memcached-1', 11211, 1 ], [ 'memcached-2', 11211, 1 ] ] );

			// NOTE: Make sure to change the persistent ID if changing these options while testing.
			$mc->setOptions( [
				// What we have in production
				// Memcached::OPT_CONNECT_TIMEOUT => 1000,
				// Memcached::OPT_TCP_NODELAY     => true,

				// This one appears required to replicate the bug:
				Memcached::OPT_POLL_TIMEOUT => 20, // milliseconds (default 1000)

				// These all seem to increase the likelihood, but are not technically required:
				// Memcached::OPT_RETRY_TIMEOUT   => 60, // seconds (default 0)
				// Memcached::OPT_CONNECT_TIMEOUT => 20, // milliseconds (default 1000)
				// Memcached::OPT_RECV_TIMEOUT    => 20*1000, // microseconds (default 0)
				// Memcached::OPT_SEND_TIMEOUT    => 20*1000, // microseconds (default 0)
				// Memcached::OPT_TCP_NODELAY     => true, // (default false)
			] );
		}

		$this->mc = $mc;
	}

	function get_and_validate_key( $key ) {
		$value = $this->mc->get( $key );

		if ( $value !== false && $key !== $value ) {
			$request_info = isset( $_GET['thread'], $_GET['iteration'] ) ? " Thread: #{$_GET['thread']}. Iteration: #{$_GET['iteration']}" : '';
			trigger_error( "Invalid value returned. Requested key: $key. Received value: $value.$request_info", E_USER_WARNING );
		}

		return $value;
	}

	function set( $key, $value ) {
		$this->mc->set( $key, $value, 0 );
	}
}

$memcached = new Memcached_Testing();

if ( isset( $_GET['set_keys'] ) ) {
	trigger_error( 'Setting up key caches', E_USER_NOTICE );
}

// Run the stuffs
$max = 1000;
for ( $i = 0; $i <= $max; $i++ ) {
	$loop_key = 'loop_' . $i;

	if ( isset( $_GET['set_keys'] ) ) {
		$memcached->set( $loop_key, $loop_key );
	}

	$memcached->get_and_validate_key( $loop_key );
}

echo "All Done";
