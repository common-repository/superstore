<?php

namespace Binarithm\Superstore\Shortcode;

/**
 * Superstore shortcodes controller
 */
class Controller {

	/**
	 * Class contructor
	 */
	public function __construct() {
		new SellerAccount();
		new Stores();
	}
}
