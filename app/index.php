<?php

class Memcached_Testing {
	public Memcached $mc;

	const CANARY_KEY = 'canary';

	function __construct() {
		$mc = new Memcached( 'persistent-123' );

		// Only add servers and set options if not already available (to keep persistency).
		if ( empty( $mc->getServerList() ) ) {
			$mc->addServers( [ [ 'memcached-1', 11211, 1 ], [ 'memcached-2', 11211, 1 ] ] );

			$mc->setOptions( [
				Memcached::OPT_BINARY_PROTOCOL => false,
				Memcached::OPT_SERIALIZER      => Memcached::SERIALIZER_PHP, // TODO: igbinary
				Memcached::OPT_CONNECT_TIMEOUT => 1000,
				Memcached::OPT_COMPRESSION     => true,
				Memcached::OPT_TCP_NODELAY     => true,
			] );
		}

		$this->mc = $mc;
	}

	function get_and_validate_canary() {
		$value = $this->mc->get( self::CANARY_KEY );

		if ( empty( $value ) ) {
			if ( Memcached::RES_NOTFOUND !== $this->mc->getResultCode() ) {
				if ( false !== $value ) {
					error_log( 'GET canary result code: ' . $this->mc->getResultCode() . '. Value: ' . print_r( $value, true ) . '. Type: ' . gettype( $value ) );
				}
			}

			return [];
		}

		// This memcached value should only ever be an an array of keys that have values of only "true".
		if ( [ true ] !== array_unique( array_values( $value ) ) ) {
			trigger_error( 'ERROR!!!!!! An invalid value was found in the cache key: ' . print_r( $value, true ), E_USER_WARNING );
			die( 'Ruh roh' );
		}

		return $value;
	}

	function set( $key, $value ) {
		$result = $this->mc->set( $key, $value, 0 );

		if ( ! $result && $key === self::CANARY_KEY && $this->mc->getResultCode() !== 47 ) {
			trigger_error( 'Unable to set key: ' . $key . ' ' . $this->mc->getResultCode(), E_USER_WARNING );
		}
	}

	function get( $key ) {
		return $this->mc->get( $key );
	}

	function getMulti( $keys ) {
		return $this->mc->getMulti( $keys );
	}

	function delete( $key ) {
		return $this->mc->delete( $key );
	}
}

$memcached = new Memcached_Testing();

$multiGetKeys = array_map( fn( $n ) => 'random-' . $n, range( 1, 1000 ) );

// Run the stuffs
$max = 100;
for ( $i = 0; $i <= $max; $i++ ) {
	// The first thing a request sends for.
	$canary_value = $memcached->get_and_validate_canary();

	// A chunky getMulti to stress out MC a little maybe?
	$memcached->getMulti( $multiGetKeys );

	// Add some other random noise.
	$memcached->set( 'random-' . $i, [ 'something' => 'else #' . mt_rand( 0, 1000 ) ] );
	$memcached->get( 'random-' . $i );

	// Sometimes update the canary.
	if ( mt_rand( 0, 100 ) === 1 ) {
		$canary_value = $memcached->get_and_validate_canary();
		$canary_value[ 'string-' . mt_rand( 0, 1000 ) ] = true;
		$memcached->set( $memcached::CANARY_KEY, $canary_value );
	}

	// Less frequently delete the canary.
	if ( count( $canary_value ) > 1000 ) {
		$memcached->delete( $memcached::CANARY_KEY );
	}

	// The last thing a request sends for
	$memcached->get( 'random-' . $i );
}

echo "All Done";
