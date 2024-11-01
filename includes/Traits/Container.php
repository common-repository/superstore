<?php

namespace Binarithm\Superstore\Traits;

trait Container {

	/**
	 * Contains chainable class instance
	 *
	 * @var array
	 */
	protected $container = array();

	/**
	 * Magic getter to get chainable container instance
	 *
	 * @param string $prop Access.
	 * @return mixed
	 */
	public function __get( $prop ) {
		if ( array_key_exists( $prop, $this->container ) ) {
			return $this->container[ $prop ];
		}
	}
}
