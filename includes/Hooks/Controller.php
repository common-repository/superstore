<?php

namespace Binarithm\Superstore\Hooks;

/**
 * Superstore hooks controller
 */
class Controller {

	/**
	 * Class constructor
	 */
	public function __construct() {
		new Core();
		new WCGeneral();
		new Product();
		new Order();
	}
}
